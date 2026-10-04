<?php

namespace App\Http\Controllers;

use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\Family;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $assets = Asset::where('family_id', $family->id)
            ->latest('current_value')
            ->get();

        $totalCurrentValue = (int) $assets->sum('current_value');
        $totalPurchaseValue = (int) $assets->sum('purchase_value');
        $totalAppreciation = $totalCurrentValue - $totalPurchaseValue;

        return view('assets.index', [
            'family' => $family,
            'assets' => $assets,
            'totalCurrentValue' => $totalCurrentValue,
            'totalPurchaseValue' => $totalPurchaseValue,
            'totalAppreciation' => $totalAppreciation,
            'formattedTotalCurrentValue' => formatMoney($totalCurrentValue, $family->currency),
            'formattedTotalPurchaseValue' => formatMoney($totalPurchaseValue, $family->currency),
            'formattedTotalAppreciation' => formatMoney($totalAppreciation, $family->currency),
            'assetTypes' => AssetType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:property,vehicle,investment,electronics,other',
            'current_value' => 'required',
            'purchase_value' => 'nullable',
            'purchase_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $family = Family::first();
        $currentCentavos = parseMoney($validated['current_value']);
        $purchaseCentavos = !empty($validated['purchase_value']) ? parseMoney($validated['purchase_value']) : 0;

        Asset::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'current_value' => $currentCentavos,
            'purchase_value' => $purchaseCentavos,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('assets.index')->with('success', 'Bem patrimonial registado com sucesso!');
    }

    public function edit(Asset $asset): View
    {
        return view('assets.edit', [
            'asset' => $asset,
            'assetTypes' => AssetType::cases(),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:property,vehicle,investment,electronics,other',
            'current_value' => 'required',
            'purchase_value' => 'nullable',
            'purchase_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $currentCentavos = parseMoney($validated['current_value']);
        $purchaseCentavos = !empty($validated['purchase_value']) ? parseMoney($validated['purchase_value']) : 0;

        $asset->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'current_value' => $currentCentavos,
            'purchase_value' => $purchaseCentavos,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('assets.index')->with('success', 'Bem patrimonial atualizado com sucesso!');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Bem patrimonial eliminado com sucesso!');
    }
}
