<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = $request->user()
            ->categories()
            ->orderByDesc('is_active')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(10);

        return view('categories.index', [
            'categories' => $categories,
            'activeIncomeCategoriesCount' => $this->activeTypeCount($request, TransactionType::Income),
            'activeExpenseCategoriesCount' => $this->activeTypeCount($request, TransactionType::Expense),
            'categoryTypes' => $this->categoryTypes(),
            'categoryIcons' => $this->categoryIcons(),
        ]);
    }

    public function create(): View
    {
        return view('categories.create', [
            'categoryTypes' => $this->categoryTypes(),
            'categoryIcons' => $this->categoryIcons(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $request->user()->categories()->create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        return to_route('categories.index')->with('status', 'Categoria cadastrada com sucesso.');
    }

    public function edit(Request $request, Category $category): View
    {
        $category = $this->categoryFromAuthenticatedUser($request, $category);

        return view('categories.edit', [
            'category' => $category,
            'categoryTypes' => $this->categoryTypes(),
            'categoryIcons' => $this->categoryIcons(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category = $this->categoryFromAuthenticatedUser($request, $category);

        $category->update($request->validated());

        return to_route('categories.index')->with('status', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $category = $this->categoryFromAuthenticatedUser($request, $category);

        $category->update([
            'is_active' => false,
        ]);

        return to_route('categories.index')->with('status', 'Categoria desativada com sucesso.');
    }

    private function categoryFromAuthenticatedUser(Request $request, Category $category): Category
    {
        abort_unless($category->user_id === $request->user()->id, 404);

        return $category;
    }

    private function activeTypeCount(Request $request, TransactionType $type): int
    {
        return $request->user()
            ->categories()
            ->where('is_active', true)
            ->where('type', $type->value)
            ->count();
    }

    /**
     * @return array<string, string>
     */
    private function categoryTypes(): array
    {
        return [
            TransactionType::Income->value => 'Receita',
            TransactionType::Expense->value => 'Despesa',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function categoryIcons(): array
    {
        return [
            'tag' => 'Etiqueta',
            'wallet' => 'Carteira',
            'home' => 'Casa',
            'cart' => 'Compras',
            'briefcase' => 'Trabalho',
            'receipt' => 'Recibo',
        ];
    }
}
