<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\CategoryGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCategoryGroupOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_listing_includes_product_with_only_parent_category(): void
    {
        $group = CategoryGroup::create(['name' => 'Mates', 'slug' => 'mates']);
        $product = Product::factory()->create([
            'category_group_id' => $group->id,
            'category_id' => null,
        ]);

        $this->get("/productos/{$group->slug}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Products/Index')
                ->has('products.data', 1)
                ->where('products.data.0.id', $product->id)
        );
    }

    public function test_detail_exposes_parent_group_without_subcategory(): void
    {
        $group = CategoryGroup::create(['name' => 'Mates', 'slug' => 'mates']);
        $product = Product::factory()->create([
            'category_group_id' => $group->id,
            'category_id' => null,
        ]);

        $this->get("/producto/{$product->slug}")->assertInertia(
            fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('product.category_group.slug', $group->slug)
                ->where('product.category', null)
        );
    }

    public function test_home_featured_shows_parent_group_without_subcategory(): void
    {
        $group = CategoryGroup::create(['name' => 'Mates', 'slug' => 'mates']);
        Product::factory()->create([
            'category_group_id' => $group->id,
            'category_id' => null,
        ]);

        $this->get('/')->assertInertia(
            fn (Assert $page) => $page
                ->component('Home')
                ->where('featuredProducts.0.category', $group->name)
                ->where('featuredProducts.0.group_slug', $group->slug)
        );
    }

    public function test_search_shows_parent_group_without_subcategory(): void
    {
        $group = CategoryGroup::create(['name' => 'Mates', 'slug' => 'mates']);
        $product = Product::factory()->create([
            'name' => 'Mate Imperial Premium',
            'category_group_id' => $group->id,
            'category_id' => null,
        ]);

        $this->get('/buscar?q=Imperial')->assertInertia(
            fn (Assert $page) => $page
                ->component('Search/Index')
                ->where('products.data.0.id', $product->id)
                ->where('products.data.0.category', $group->name)
        );
    }

    public function test_admin_form_requires_parent_group(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateProduct::class)
            ->fillForm(['name' => 'Producto sin grupo'])
            ->call('create')
            ->assertHasFormErrors(['category_group_id' => 'required']);
    }

    public function test_admin_can_save_product_with_only_parent_group(): void
    {
        $this->actingAs($this->admin());
        $group = CategoryGroup::create(['name' => 'Mates', 'slug' => 'mates']);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Mate solo padre',
                'category_group_id' => $group->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Mate solo padre',
            'category_group_id' => $group->id,
            'category_id' => null,
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
