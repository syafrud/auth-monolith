<?php

use App\Models\User;
use Modules\Library\Catalog\Models\Book;
use Modules\Library\Circulation\Models\Loan;
use Modules\Library\Circulation\Services\LoanService;
use Modules\Library\Membership\Models\Member;

dataset('soft deletable', [
    'book' => [fn () => Book::factory()->create(), 'library.books', 'books'],
    'member' => [fn () => Member::factory()->create(), 'library.members', 'members'],
    'loan' => [fn () => app(LoanService::class)->borrow(Member::factory()->create()->id, [Book::factory()->create()->id]), 'library.loans', 'loans'],
]);

test('admin soft deletes and restores library records', function (Closure $makeRecord, string $route, string $table) {
    $record = $makeRecord();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->delete(route("{$route}.destroy", $record))
        ->assertRedirect(route("{$route}.index"));

    $this->assertSoftDeleted($table, ['id' => $record->id]);
    expect($record::query()->find($record->id))->toBeNull()
        ->and($record::withTrashed()->find($record->id))->not->toBeNull();

    $this->actingAs($admin)
        ->get(route("{$route}.index", ['trashed' => 1]))
        ->assertOk()
        ->assertSee('Pulihkan');

    $this->actingAs($admin)
        ->patch(route("{$route}.restore", $record))
        ->assertRedirect(route("{$route}.index"))
        ->assertSessionHas('success');

    $this->assertNotSoftDeleted($table, ['id' => $record->id]);
})->with('soft deletable');

test('regular users cannot delete or restore library records', function (Closure $makeRecord, string $route) {
    $record = $makeRecord();
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->delete(route("{$route}.destroy", $record))->assertForbidden();

    $record->delete();

    $this->actingAs($user)->patch(route("{$route}.restore", $record))->assertForbidden();
    expect($record::withTrashed()->find($record->id)->trashed())->toBeTrue();
})->with('soft deletable');

test('soft deleted loans keep their items for the audit trail', function () {
    $loan = app(LoanService::class)->borrow(Member::factory()->create()->id, [Book::factory()->create()->id]);

    $loan->delete();

    expect(Loan::withTrashed()->with('items')->find($loan->id)->items)->toHaveCount(1);
});
