<?php

namespace App\Services;

use App\Models\SmsPackage;
use Illuminate\Support\Collection;

class SmsPackageService
{
    public function getAll(): Collection
    {
        return SmsPackage::latest()->get();
    }

    public function getActive(): Collection
    {
        return SmsPackage::where('status', 1)->latest()->get();
    }

    public function store(array $data): SmsPackage
    {
        return SmsPackage::create($data);
    }

    public function update(int $id, array $data): SmsPackage
    {
        $package = SmsPackage::findOrFail($id);
        $package->update($data);
        return $package;
    }

    public function delete(int $id): void
    {
        SmsPackage::findOrFail($id)->delete();
    }
}
