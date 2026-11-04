<?php

use App\Models\User;
use Modules\Products\Models\Product;

function createProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Laptop',
        'slug' => 'laptop',
        'price' => 7500000,
        'stock' => 5,
    ], $attributes));
}

test('guests are redirected to login from product pages', function () {
    $this->get('/products')->assertRedirect(route('login', absolute: false));
    $this->get('/products/create')->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view the product list and create form', function () {
    $user = User::factory()->create();
    createProduct();

    $this->actingAs($user)->get('/products')->assertOk()->assertSee('Laptop');
    $this->actingAs($user)->get('/products/create')->assertOk();
});

test('empty product form fails validation with indonesian messages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/products/create')
        ->post('/products', [])
        ->assertRedirect('/products/create')
        ->assertSessionHasErrors([
            'name' => 'Nama produk wajib diisi.',
            'price' => 'Harga produk wajib diisi.',
            'stock' => 'Stok wajib diisi.',
        ])
        ->assertSessionHasErrors('slug');

    expect(Product::count())->toBe(0);
});

test('negative price and stock are rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/products', [
            'name' => 'Mouse',
            'slug' => 'mouse',
            'price' => -1,
            'stock' => -5,
        ])
        ->assertSessionHasErrors([
            'price' => 'Harga tidak boleh negatif.',
            'stock' => 'Stok tidak boleh negatif.',
        ]);
});

test('duplicate slug is rejected', function () {
    $user = User::factory()->create();
    createProduct();

    $this->actingAs($user)
        ->post('/products', [
            'name' => 'Laptop Baru',
            'slug' => 'laptop',
            'price' => 1000,
            'stock' => 1,
        ])
        ->assertSessionHasErrors(['slug' => 'Slug produk sudah digunakan.']);
});

test('valid product is stored and redirects with a success message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/products', [
            'name' => 'Keyboard',
            'slug' => 'keyboard',
            'price' => 250000.50,
            'stock' => 10,
            'description' => 'Keyboard mekanik',
        ])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success', 'Produk berhasil ditambahkan.');

    expect(Product::where('slug', 'keyboard')->exists())->toBeTrue();
});

test('reducing stock beyond availability throws insufficient stock error', function () {
    $user = User::factory()->create();
    $product = createProduct(['stock' => 3]);

    $this->actingAs($user)
        ->from('/products')
        ->get(route('products.reduce-stock', [$product, 10]))
        ->assertRedirect('/products')
        ->assertSessionHasErrors([
            'stock' => "Stok produk 'Laptop' tidak mencukupi. Tersedia: 3, diminta: 10.",
        ]);

    expect($product->fresh()->stock)->toBe(3);
});

test('insufficient stock error returns json for api requests', function () {
    $user = User::factory()->create();
    $product = createProduct(['stock' => 2]);

    $this->actingAs($user)
        ->getJson(route('products.reduce-stock', [$product, 5]))
        ->assertStatus(422)
        ->assertJson([
            'error' => 'insufficient_stock',
            'details' => [
                'product' => 'Laptop',
                'available' => 2,
                'requested' => 5,
            ],
        ]);
});

test('reducing stock within availability decrements stock', function () {
    $user = User::factory()->create();
    $product = createProduct(['stock' => 5]);

    $this->actingAs($user)
        ->get(route('products.reduce-stock', [$product, 2]))
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success', 'Stok berhasil dikurangi.');

    expect($product->fresh()->stock)->toBe(3);
});

test('invalid quantity does not match the reduce stock route', function (string $quantity) {
    $user = User::factory()->create();
    $product = createProduct();

    $this->actingAs($user)
        ->get("/products/{$product->id}/reduce-stock/{$quantity}")
        ->assertNotFound();
})->with(['-2', '0', '99999999999999999999']);

test('empty slug uses an indonesian message and unknown fields are ignored', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/products', ['category_ids' => [1]])
        ->assertSessionHasErrors(['slug' => 'Slug produk wajib diisi.']);
});

test('product detail and edit pages can be rendered', function () {
    $user = User::factory()->create();
    $product = createProduct();

    $this->actingAs($user)->get("/products/{$product->id}")->assertOk()->assertSee('Laptop');
    $this->actingAs($user)->get("/products/{$product->id}/edit")->assertOk()->assertSee('laptop');
});

test('product can be updated while keeping its own slug', function () {
    $user = User::factory()->create();
    $product = createProduct();

    $this->actingAs($user)
        ->put("/products/{$product->id}", [
            'name' => 'Laptop Baru',
            'slug' => 'laptop',
            'price' => 8000000,
            'stock' => 2,
        ])
        ->assertRedirect(route('products.index'))
        ->assertSessionHasNoErrors();

    expect($product->fresh()->name)->toBe('Laptop Baru');
});

test('updating a product with an empty name is rejected', function () {
    $user = User::factory()->create();
    $product = createProduct();

    $this->actingAs($user)
        ->put("/products/{$product->id}", ['name' => ''])
        ->assertSessionHasErrors(['name' => 'Nama produk wajib diisi.']);
});
