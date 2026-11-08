<?php

namespace Modules\Library\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Library\Catalog\Models\Author;
use Modules\Library\Catalog\Models\Book;
use Modules\Library\Catalog\Models\BookCategory;
use Modules\Library\Catalog\Models\Publisher;
use Modules\Library\Catalog\Requests\StoreBookRequest;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $showTrashed = $request->boolean('trashed') && $request->user()->role === 'admin';

        $books = Book::query()
            ->with(['author', 'publisher', 'categories'])
            ->when($showTrashed, fn ($query) => $query->onlyTrashed())
            ->when($request->filled('q'), fn ($query) => $query->search($request->string('q')->toString()))
            ->when($request->filled('category'), fn ($query) => $query->inCategory($request->integer('category')))
            ->when($request->boolean('available'), fn ($query) => $query->available())
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        $categories = BookCategory::with('children')->whereNull('parent_id')->orderBy('name')->get();

        return view('library.books.index', compact('books', 'categories', 'showTrashed'));
    }

    public function create(): View
    {
        return view('library.books.create', [
            'authors' => Author::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'categories' => BookCategory::with('children')->whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = DB::transaction(function () use ($request) {
            $book = Book::create($request->safe()->except('category_ids'));
            $book->categories()->attach($request->validated('category_ids', []), ['added_at' => now()]);

            return $book;
        });

        return redirect()
            ->route('library.books.show', $book)
            ->with('success', "Buku '{$book->title}' berhasil ditambahkan.");
    }

    public function show(Book $book): View
    {
        $book->load(['author', 'publisher', 'categories.parent']);

        return view('library.books.show', compact('book'));
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()
            ->route('library.books.index')
            ->with('success', "Buku '{$book->title}' dipindahkan ke tempat sampah.");
    }

    public function restore(Book $book): RedirectResponse
    {
        $book->restore();

        return redirect()
            ->route('library.books.index')
            ->with('success', "Buku '{$book->title}' berhasil dipulihkan.");
    }
}
