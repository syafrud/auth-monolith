<?php

namespace Modules\Library\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Library\Exceptions\AuthorHasBooksException;
use Modules\Library\Models\Author;
use Modules\Library\Requests\StoreAuthorRequest;
use Modules\Library\Requests\UpdateAuthorRequest;

class AuthorController extends Controller
{
    public function index(): View
    {
        $authors = Author::withCount('books')->latest('id')->paginate(10);

        return view('authors.index', compact('authors'));
    }

    public function create(): View
    {
        return view('authors.create');
    }

    public function store(StoreAuthorRequest $request): RedirectResponse
    {
        Author::create($request->validated());

        return redirect()
            ->route('authors.index')
            ->with('success', 'Penulis berhasil ditambahkan.');
    }

    public function edit(Author $author): View
    {
        return view('authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, Author $author): RedirectResponse
    {
        $author->update($request->validated());

        return redirect()
            ->route('authors.index')
            ->with('success', 'Penulis berhasil diperbarui.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        $booksCount = $author->books()->count();

        if ($booksCount > 0) {
            throw new AuthorHasBooksException($author->name, $booksCount);
        }

        $author->delete();

        return redirect()
            ->route('authors.index')
            ->with('success', 'Penulis berhasil dihapus.');
    }
}
