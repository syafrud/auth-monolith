<?php

namespace Modules\Library\Membership\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Library\Membership\Models\Member;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $showTrashed = $request->boolean('trashed') && $request->user()->role === 'admin';

        $members = Member::query()
            ->with('role')
            ->when($showTrashed, fn ($query) => $query->onlyTrashed())
            ->when($request->filled('q'), fn ($query) => $query->search($request->string('q')->toString()))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('library.members.index', compact('members', 'showTrashed'));
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()
            ->route('library.members.index')
            ->with('success', "Anggota '{$member->name}' dipindahkan ke tempat sampah.");
    }

    public function restore(Member $member): RedirectResponse
    {
        $member->restore();

        return redirect()
            ->route('library.members.index')
            ->with('success', "Anggota '{$member->name}' berhasil dipulihkan.");
    }
}
