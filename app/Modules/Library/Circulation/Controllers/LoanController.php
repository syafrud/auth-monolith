<?php

namespace Modules\Library\Circulation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Library\Circulation\Models\Loan;
use Modules\Library\Circulation\Requests\StoreLoanRequest;
use Modules\Library\Circulation\Services\LoanService;
use Modules\Shared\Contracts\BookCatalogInterface;
use Modules\Shared\Contracts\MembershipServiceInterface;

class LoanController extends Controller
{
    public function __construct(
        private LoanService $loanService,
    ) {}

    public function index(Request $request): View
    {
        $showTrashed = $request->boolean('trashed') && $request->user()->role === 'admin';

        $loans = Loan::query()
            ->with('items')
            ->when($showTrashed, fn ($query) => $query->onlyTrashed())
            ->when($request->input('status') === 'active', fn ($query) => $query->active())
            ->when($request->input('status') === 'overdue', fn ($query) => $query->overdue())
            ->latest('borrowed_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('library.loans.index', compact('loans', 'showTrashed'));
    }

    public function create(MembershipServiceInterface $membership, BookCatalogInterface $bookCatalog): View
    {
        return view('library.loans.create', [
            'members' => $membership->getActiveMembers(),
            'books' => $bookCatalog->getAvailableBooks(),
        ]);
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $loan = $this->loanService->borrow(
            $request->integer('member_id'),
            array_map('intval', $request->validated('book_ids')),
        );

        return redirect()
            ->route('library.loans.index')
            ->with('success', "Peminjaman {$loan->loan_code} berhasil dibuat. Jatuh tempo {$loan->due_at->format('d-m-Y')}.");
    }

    public function returnBooks(Loan $loan): RedirectResponse
    {
        if ($loan->isReturned()) {
            return back()->with('error', "Peminjaman {$loan->loan_code} sudah dikembalikan.");
        }

        $loan = $this->loanService->returnLoan($loan);
        $fine = 'Rp '.number_format((float) $loan->fine_amount, 0, ',', '.');

        return redirect()
            ->route('library.loans.index')
            ->with('success', "Peminjaman {$loan->loan_code} dikembalikan. Denda: {$fine}.");
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        $loan->delete();

        return redirect()
            ->route('library.loans.index')
            ->with('success', "Peminjaman {$loan->loan_code} dipindahkan ke tempat sampah.");
    }

    public function restore(Loan $loan): RedirectResponse
    {
        $loan->restore();

        return redirect()
            ->route('library.loans.index')
            ->with('success', "Peminjaman {$loan->loan_code} berhasil dipulihkan.");
    }
}
