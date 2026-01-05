<?php

namespace App\Filament\Pharmacy\Pages;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use UnitEnum;

class KnowledgeBase extends Page
{
    use WithPagination; // 👈 Enable pagination support

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected string $view = 'filament.pharmacy.pages.knowledge-base';

    protected static ?string $navigationLabel = 'Message Playbook';

    protected static string|UnitEnum|null $navigationGroup = 'Patient Care';

    protected static ?int $navigationSort = 5; // Places it at the bottom of the care tools

    public string $search = '';

    public ?int $selectedCategory = null;

    public $categories;

    /**
     * Reset pagination when search or category changes
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->categories = KnowledgeBaseCategory::orderBy('sort_order')->get();
    }

    /**
     * Use pagination instead of returning all articles
     */
    public function getArticlesProperty(): LengthAwarePaginator
    {
        return KnowledgeBaseArticle::query()
            ->when($this->selectedCategory, fn ($q) => $q->where('knowledge_base_category_id', $this->selectedCategory)
            )
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('content', 'like', "%{$this->search}%")
            )
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate(8); // 👈 You can adjust the per-page number here
    }
}
