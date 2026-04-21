<?php

namespace App\Exceptions;

use Exception;

class PackageLimitExceededException extends Exception
{
    public function __construct(
        private string $resource,
        private int $limit,
        private bool $isMonthly = false
    ) {
        $monthly = $isMonthly ? ' this month' : '';
        parent::__construct(
            "You have reached your {$resource} limit of {$limit}{$monthly}. Please upgrade your package."
        );
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'PACKAGE_LIMIT_EXCEEDED',
            'data' => [
                'resource' => $this->resource,
                'limit' => $this->limit,
                'is_monthly' => $this->isMonthly,
            ]
        ], 403);
    }
}