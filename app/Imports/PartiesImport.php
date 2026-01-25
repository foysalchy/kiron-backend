<?php

namespace App\Imports;

use App\Models\Party;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PartiesImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    SkipsOnError,
    SkipsEmptyRows  
{
    use SkipsErrors;

    private int $rowCount = 0;
    private array $failedRows = [];

    public function model(array $row)
    {
        
        if ($this->isEmptyRow($row)) {
            return null;
        }

        try {
            $this->rowCount++;

            $type = $this->extractType($row);
            $status = $this->extractStatus($row);

            return new Party([
                'name' => $row['name'] ?? null,
                'email' => $row['email'] ?? null,
                'phone' => $row['phone'] ?? null,
                'alternative_phone' => $row['alternative_phone'] ?? null,
                'address' => $row['address'] ?? null,
                'type' => $type,
                'balance' => $row['balance'] ?? 0,
                'status' => $status,
                'password' => Hash::make('12345678'),
            ]);
        } catch (\Exception $e) {
            $this->failedRows[] = [
                'row' => $this->rowCount + 1, // +1 because header is row 1
                'error' => $e->getMessage()
            ];
            return null;
        }
    }

    /**
     *  Check if row is empty
     */
    private function isEmptyRow(array $row): bool
    {
        // Remove null values and check if anything remains
        $filtered = array_filter($row, function($value) {
            return $value !== null && $value !== '';
        });
        
        return empty($filtered);
    }

    /**
     * Extract type from row
     */
    private function extractType(array $row): int
    {
        $typeValue = $row['type_1supplier_2customer'] 
                  ?? $row['type'] 
                  ?? 2;

        if (is_string($typeValue)) {
            $typeValue = strtolower(trim($typeValue));
            return ($typeValue === 'supplier' || $typeValue === '1') ? 1 : 2;
        }

        return (int) $typeValue;
    }

    /**
     * Extract status from row
     */
    private function extractStatus(array $row): int
    {
        $statusValue = $row['status_0inactive_1active'] 
                    ?? $row['status'] 
                    ?? 0;

        if (is_string($statusValue)) {
            $statusValue = strtolower(trim($statusValue));
            return ($statusValue === 'active' || $statusValue === '1') ? 1 : 0;
        }

        return (int) $statusValue;
    }

    /**
     * Validation rules 
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:parties,email',
            'phone' => 'required|max:20',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be valid',
            'email.unique' => 'Email already exists in database',
            'phone.required' => 'Phone number is required',
        ];
    }

    /**
     *  Prepare rows for validation 
     */
    public function prepareForValidation($data, $index)
    {
        // If row is empty, skip validation
        if ($this->isEmptyRow($data)) {
            return null;
        }
        
        return $data;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getFailedRows(): array
    {
        return $this->failedRows;
    }
}