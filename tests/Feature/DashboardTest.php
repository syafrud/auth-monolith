<?php

use App\Models\User;
use Modules\Orders\Services\OrderService;
use Modules\Products\Models\Product;
use Modules\Shared\Contracts\SalesStatisticsInterface;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('user dashboard shows only the orders of the logged in user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 25000]);
    $orderService = app(OrderService::class);

    $ownOrder = $orderService->createOrder($user->id, [['product_id' => $product->id, 'quantity' => 2]]);
    $otherOrder = $orderService->createOrder($otherUser->id, [['product_id' => $product->id, 'quantity' => 1]]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($ownOrder->order_number)
        ->assertDontSee($otherOrder->order_number)
        ->assertSee('Rp 50.000')
        ->assertDontSee('Panel Admin');
});

test('user dashboard shows an empty state when the user has no orders', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada pesanan');
});

test('admin dashboard shows sales, catalog and user statistics', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['name' => 'Budi Pelanggan']);
    $product = Product::factory()->create(['stock' => 10, 'price' => 100000]);
    $lowStockProduct = Product::factory()->create(['name' => 'Buku Hampir Habis', 'stock' => 2, 'low_stock_threshold' => 5]);

    $order = app(OrderService::class)->createOrder($customer->id, [['product_id' => $product->id, 'quantity' => 3]]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Budi Pelanggan')
        ->assertSee('Buku Hampir Habis')
        ->assertSee(route('products.show', $lowStockProduct))
        ->assertSee('Rp 300.000');
});

test('regular users cannot open the admin dashboard', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('sales statistics exclude cancelled orders from revenue', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 10000]);
    $orderService = app(OrderService::class);

    $orderService->createOrder($user->id, [['product_id' => $product->id, 'quantity' => 1]]);
    $cancelledOrder = $orderService->createOrder($user->id, [['product_id' => $product->id, 'quantity' => 2]]);
    $orderService->updateStatus($cancelledOrder, 'cancelled', $user->id);

    expect(app(SalesStatisticsInterface::class)->getSummary($user->id))->toBe([
        'total_orders' => 2,
        'pending_orders' => 1,
        'total_revenue' => 10000.0,
    ]);
});
