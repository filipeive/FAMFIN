<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $incomeCategories = Category::where('family_id', $family->id)
            ->where('type', CategoryType::Income)
            ->withCount(['transactions', 'children'])
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->withCount('transactions')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $expenseCategories = Category::where('family_id', $family->id)
            ->where('type', CategoryType::Expense)
            ->withCount(['transactions', 'children'])
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->withCount('transactions')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $parentCategories = Category::where('family_id', $family->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('categories.index', [
            'family' => $family,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'parentCategories' => $parentCategories,
            'categoryTypes' => CategoryType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'parent_id' => 'nullable|exists:categories,id',
            'color' => 'nullable|string|max:20',
        ]);

        $family = Family::first();

        Category::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'color' => $validated['color'] ?? '#10B981',
            'icon' => $validated['type'] === 'income' ? 'income' : 'expense',
            'sort_order' => Category::where('family_id', $family->id)->count() + 1,
            'is_active' => true,
        ]);

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category): View
    {
        $family = Family::first();
        $parentCategories = Category::where('family_id', $family->id)
            ->where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('categories.edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
            'categoryTypes' => CategoryType::cases(),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'parent_id' => 'nullable|exists:categories,id',
            'color' => 'nullable|string|max:20',
        ]);

        // Prevent setting self as parent
        if (!empty($validated['parent_id']) && $validated['parent_id'] === $category->id) {
            return back()->withErrors(['parent_id' => 'Uma categoria não pode ser subcategoria de si mesma.']);
        }

        $category->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'color' => $validated['color'] ?? $category->color,
        ]);

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Unlink a subcategory from its parent ("Tirar da lista").
     */
    public function unlink(Category $category): RedirectResponse
    {
        $category->update(['parent_id' => null]);

        return redirect()->route('categories.index')->with('success', "Item '{$category->name}' desvinculado da lista com sucesso!");
    }

    /**
     * Unlink or clear all child subcategories for a parent category.
     */
    public function destroyChildren(Category $category): RedirectResponse
    {
        $count = $category->children()->count();
        $category->children()->update(['parent_id' => null]);

        return redirect()->route('categories.index')->with('success', "Todas as {$count} subcategorias foram desvinculadas com sucesso!");
    }

    /**
     * Delete a category or subcategory safely.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Reassign any existing transactions to parent category or null
        Transaction::where('category_id', $category->id)->update([
            'category_id' => $category->parent_id,
        ]);

        // Unlink children if parent
        $category->children()->update(['parent_id' => null]);

        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')->with('success', "Categoria '{$name}' eliminada com sucesso!");
    }
}
