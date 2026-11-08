<?php

use App\Models\User;
use Modules\Products\Models\Category;
use Modules\Products\Models\Product;

test('product detail page shows the product and its categories', function () {
    $product = Product::factory()->create(['name' => 'Laptop Kantor', 'stock' => 3, 'low_stock_threshold' => 5]);
    $category = Category::factory()->create(['name' => 'Elektronik']);
    $product->categories()->attach($category, ['added_at' => now()]);

    $this->actingAs(User::factory()->create())
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Laptop Kantor')
        ->assertSee('Elektronik')
        ->assertSee('Stok menipis')
        ->assertSee(route('products.edit', $product));
});

test('product edit page is prefilled with the product data', function () {
    $product = Product::factory()->create(['name' => 'Meja Lipat', 'slug' => 'meja-lipat']);

    $this->actingAs(User::factory()->create())
        ->get(route('products.edit', $product))
        ->assertOk()
        ->assertSee('value="Meja Lipat"', false)
        ->assertSee('value="meja-lipat"', false)
        ->assertSee(route('products.update', $product));
});

test('product can be updated from the edit page', function () {
    $product = Product::factory()->create(['name' => 'Nama Lama', 'stock' => 5]);

    $this->actingAs(User::factory()->create())
        ->put(route('products.update', $product), [
            'name' => 'Nama Baru',
            'slug' => $product->slug,
            'price' => 15000,
            'stock' => 8,
        ])
        ->assertRedirect(route('products.index'))
        ->assertSessionHasNoErrors();

    expect($product->fresh())
        ->name->toBe('Nama Baru')
        ->stock->toBe(8);
});

test('product list links to the detail and edit pages', function () {
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee(route('products.show', $product))
        ->assertSee(route('products.edit', $product));
});

test('guests cannot open product pages', function () {
    $product = Product::factory()->create();

    $this->get(route('products.show', $product))->assertRedirect(route('login'));
    $this->get(route('products.edit', $product))->assertRedirect(route('login'));
});
