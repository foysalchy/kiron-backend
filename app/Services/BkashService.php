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
        // DB::beginTransaction();
        // try {
            $token = Cache::get('bkash_id_token') ?? $this->grantToken()['id_token'];

            $response = Http::withHeaders($this->getHeaders($token))->post("{$this->baseUrl}/tokenized/checkout/create", [
                'mode'                  => '0011',
                'payerReference'        => $data['payerReference'],
                'callbackURL'           => env('BKASH_CALLBACK_URL'),
                'amount'                => $data['amount'],
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => 'INV-' . time(), 
            ]);
            // dd($response);

            $result = $response->json();

            if ($response->successful() && isset($result['paymentID'])) {
                // Example: Log the creation attempt in your system logs
                LogHelper::created('bkash_payment', $result['paymentID'], $data['company_id'] ?? null, "Payment initiated for amount: " . $data['amount']);
                
                DB::commit();
                return $result;
            }

            Log::warning('bKash Payment Creation Denied', ['result' => $result]);
            throw ApiException::serverError($result['statusMessage'] ?? 'Failed to create payment');

        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     Log::error('bKash Create Payment Exception: ' . $e->getMessage());
        //     throw ApiException::serverError('Payment creation failed due to server error');
        // }
    }

    /**
     * Execute Payment 
     */
    public function execute(string $paymentID,int $companyId): array
    {
        DB::beginTransaction();
        try {
            $token = Cache::get('bkash_id_token') ?? $this->grantToken()['id_token'];

            $response = Http::withHeaders($this->getHeaders($token))->post("{$this->baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $paymentID
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['transactionStatus']) && $result['transactionStatus'] === 'Completed') {
                
                // Use LogHelper to record the successful update/completion of the payment
                LogHelper::updated('bkash_payment', $paymentID, $companyId, "Transaction Completed: " . $result['trxID']);

                DB::commit();
                return $result;
            }

            Log::error('bKash Execution Failed', ['paymentID' => $paymentID, 'result' => $result]);
            throw ApiException::serverError($result['statusMessage'] ?? 'Payment execution failed');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('bKash Execute Exception: ' . $e->getMessage());
            throw ApiException::serverError('Could not complete the transaction');
        }
    }


}
