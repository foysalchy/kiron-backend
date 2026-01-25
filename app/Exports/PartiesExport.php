<?php

namespace App\Exports;

use App\Models\Party;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PartiesExport implements FromQuery, WithHeadings, WithMapping
{
    protected string $partyType;

    public function __construct(string $partyType = 'all')
    {
        $this->partyType = $partyType;
    }

    public function query()
    {
        $query = Party::query();

        if ($this->partyType !== 'all') {
            $query->where('type', $this->partyType);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Alternative Phone',
            'Address',
            'Type',
            'Balance',
            'Status',
        ];
    }

    public function map($party): array
    {
        return [
            $party->name,
            $party->email,
            $party->phone,
            $party->alternative_phone,
            $party->address,
            $party->type == 1 ? 'Supplier' : 'Customer',
            $party->balance,
            $party->status == 1 ? 'Active' : 'Inactive',
        ];
    }
}