<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\{DB,Log};

class BkashService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('BKASH_BASE_URL');
    }
    /**
     * Get Common Headers
     */
    private function getHeaders(?string $token = null): array
    {
        $headers = [
            'username'     => env('BKASH_USERNAME'),
            'password'     => env('BKASH_PASSWORD'),
            'accept'       => 'application/json',
            'content-type' => 'application/json'
        ];

        if ($token) {
            $headers['Authorization'] = "Bearer $token";
            $headers['X-App-Key']     = env('BKASH_APP_KEY');
        }

        return $headers;
    }

    /**
     * Step 1: Grant Token (id_token & refresh_token)
     */
    public function grantToken(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())->post("{$this->baseUrl}/tokenized/checkout/token/grant", [
                'app_key'    => env('BKASH_APP_KEY'),
                'app_secret' => env('BKASH_APP_SECRET'),
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['id_token'])) {
                Cache::put('bkash_id_token', $result['id_token'], 3500);
                return $result;
            }

            LogHelper::error("bKash Token Failed: " . ($result['statusMessage'] ?? 'Unknown Error'));
            throw ApiException::serverError($result['statusMessage'] ?? 'Failed to get bKash token');

        } catch (\Exception $e) {
            Log::error('bKash Grant Token Error: ' . $e->getMessage());
            throw ApiException::serverError('Something went wrong with bKash Connectivity');
        }
    }
    /**
     * Create Payment
     */
    public function createPayment(array $data): array
    {
        DB::beginTransaction();
        try {
            $token = Cache::get('bkash_id_token') ?? $this->grantToken()['id_token'];

            // $companyId = auth()->user()->isSuperAdmin() ? ($data['company_id'] ?? null) : auth()->user()->company_id;

            // if (!$companyId) {
            //     throw ApiException::serverError('Company context is missing.');
            // }
            $response = Http::withHeaders($this->getHeaders($token))->post("{$this->baseUrl}/tokenized/checkout/create", [
                'mode'                  => '0011',
                'payerReference'        => $data['payerReference'],
                'callbackURL'           => env('BKASH_CALLBACK_URL'),
                'amount'                => $data['amount'],
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => 'INV-' . time(),
            ]);
            //  dd($response);

            $result = $response->json();

            if ($response->successful() && isset($result['paymentID'])) {
                // LogHelper::created('bkash_payment',(int) $result['paymentID'],(int) $companyId, "Payment initiated for amount: " . $data['amount']);

                $paymentID = $result['paymentID'];
                $customUrl = 'http://127.0.0.1:8000/api/v1/bkash';

                $result['successCallbackURL'] = "{$customUrl}/success?paymentID={$paymentID}&status=success";
                $result['failureCallbackURL'] = "{$customUrl}/failure?paymentID={$paymentID}&status=failure";
                // $result['cancelledCallbackURL'] = "{$baseUrl}/failure?paymentID={$paymentID}&status=cancel";
                DB::commit();
                return $result;
            }

            Log::warning('bKash Payment Creation Denied', ['result' => $result]);
            throw ApiException::serverError($result['statusMessage'] ?? 'Failed to create payment');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('bKash Create Payment Exception: ' . $e->getMessage());
            throw ApiException::serverError('Payment creation failed due to server error');
        }
    }

    /**
     * Execute Payment
     */
    public function execute(string $paymentID): array
    {
        DB::beginTransaction();
        try {
            $token = Cache::get('bkash_id_token') ?? $this->grantToken()['id_token'];

            $response = Http::withHeaders($this->getHeaders($token))->post("{$this->baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $paymentID
            ]);

            $result = $response->json();

            if (
                ($response->successful() && isset($result['transactionStatus']) && $result['transactionStatus'] === 'Completed') ||
                (isset($result['statusCode']) && in_array($result['statusCode'], ['2117', '2062']))
            ) {

                DB::commit();
                return $result;
            }

            Log::error('bKash Execution Failed', ['paymentID' => $paymentID, 'result' => $result]);
            throw ApiException::serverError($result['statusMessage'] ?? 'Payment execution failed');

        } catch (\Exception $e) {
            DB::rollBack();

            if (str_contains($e->getMessage(), '2117')) {
                return ['statusCode' => '0000', 'statusMessage' => 'Success', 'transactionStatus' => 'Completed'];
            }

            Log::error('bKash Execute Exception: ' . $e->getMessage());
            throw ApiException::serverError('Could not complete the transaction');
        }
    }
    public function successStatus($data): array
    {
        return [
            'transaction_id' => $data['trxID'] ?? null,
            'payment_id' => $data['paymentID'],
            'amount' => $data['amount'],
            'date' => now()->toDateTimeString(),
        ];
    }
    public function failureStatus(string $status, ?string $paymentID): array
    {
        return [
            'error_status' => $status,
            'payment_id'   => $paymentID,
            'message'      => 'User could not complete the payment.',
            'status'       => 'Failed'
        ];
    }


}
