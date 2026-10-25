<?php

use App\Models\User;

test('new users are registered with the user role', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::where('email', 'test@example.com')->first()->role)->toBe('user');
});

test('guests are redirected to login from protected routes', function () {
    $this->get('/admin/dashboard')->assertRedirect(route('login', absolute: false));
    $this->get('/user/dashboard')->assertRedirect(route('login', absolute: false));
});

test('regular users can access the user dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/user/dashboard')->assertOk();
});

test('regular users are forbidden from admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    $this->actingAs($user)->get('/admin/users')->assertForbidden();
});

test('admins can access the admin dashboard', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();

    $this->actingAs($admin)->get('/admin/dashboard')
        ->assertOk()
        ->assertViewHas('totalUsers', 3)
        ->assertViewHas('totalAdmins', 1)
        ->assertViewHas('totalRegularUsers', 2);
});

test('admins can see the list of users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->get('/admin/users')
        ->assertOk()
        ->assertSee($user->email);
});

test('admins can change the role of another user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/role", ['role' => 'admin'])
        ->assertSessionHas('status');

    expect($user->fresh()->role)->toBe('admin');
});

test('role must be admin or user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/role", ['role' => 'superadmin'])
        ->assertSessionHasErrors('role');

    expect($user->fresh()->role)->toBe('user');
});

test('admins can delete another user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->delete("/admin/users/{$user->id}")
        ->assertSessionHas('status');

    expect(User::find($user->id))->toBeNull();
});

test('admins can not change their own role or delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch("/admin/users/{$admin->id}/role", ['role' => 'user'])
        ->assertSessionHas('error');
    $this->actingAs($admin)->delete("/admin/users/{$admin->id}")
        ->assertSessionHas('error');

    expect($admin->fresh())->not->toBeNull()
        ->and($admin->fresh()->role)->toBe('admin');
});
