<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingPackage;
use App\Http\Requests\PricingPackageRequest;
use App\Services\PricingPackageService;

class PricingPackageController extends Controller
{
    public function index()
    {
        $packages = PricingPackage::with('tiers')->latest()->paginate(15);
        return response()->json(['data' => $packages]);
    }

    public function store(PricingPackageRequest $request, PricingPackageService $service)
    {
        $package = $service->store($request->validated());
        return response()->json(['message' => 'Package created successfully', 'data' => $package]);
    }

    public function show(PricingPackage $pricingPackage)
    {
        return response()->json(['data' => $pricingPackage->load('tiers')]);
    }

    public function update(PricingPackageRequest $request, PricingPackage $pricingPackage, PricingPackageService $service)
    {
        $package = $service->update($pricingPackage, $request->validated());
        return response()->json(['message' => 'Package updated successfully', 'data' => $package]);
    }

    public function destroy(PricingPackage $pricingPackage)
    {
        $pricingPackage->delete();
        return response()->json(['message' => 'Package deleted successfully']);
    }
}
