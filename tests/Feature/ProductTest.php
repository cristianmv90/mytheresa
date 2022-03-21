<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{

    public function test_store()
    {
        $data = [
            "sku" => "00021",
            "name" => "BV Lean leather ankle boots",
            "category" => "boots",
            "price" => "4500",
        ];

        $this->json('POST', 'api/products/store', $data, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "message" => "Product created successfully"
            ]);
    }  

    public function test_store_error()
    {
        $data = [
            "sku" => "00022",
        ];

        $this->json('POST', 'api/products/store', $data, ['Accept' => 'application/json'])
            ->assertStatus(404);
    }  

    public function test_show()
    {
        $this->json('GET', 'api/products/show/00021', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "product" => [
                    "name" => "BV Lean leather ankle boots"
                ]
            ]);
    }    

    public function test_update()
    {
        $data = [
            "name" => "Test Name",
        ];

        $this->json('PUT', 'api/products/update/00021', $data, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "message" => "Product updated successfully"
            ]);
    }

    public function test_update_error()
    {
        $data = [
            "name" => "Test Name",
        ];

        $this->json('PUT', 'api/products/update/00022', $data, ['Accept' => 'application/json'])
            ->assertStatus(404);
    }

    public function test_show_after_update()
    {
        $this->json('GET', 'api/products/show/00021', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "product" => [
                    "name" => "Test Name"
                ]
            ]);
    }  

    public function test_show_error()
    {
        $this->json('GET', 'api/products/show/00022', [], ['Accept' => 'application/json'])
            ->assertStatus(404);
    }  

    public function test_index()
    {
        $response = $this->json('GET', 'api/products/list', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonCount(1, 'products');
    }

    public function test_index_with_filter_category()
    {
        $response = $this->json('GET', 'api/products/list?category=boo', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonCount(1, 'products');
    }

    public function test_index_with_filter_price()
    {
        $response = $this->json('GET', 'api/products/list?priceLessThan=90000', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonCount(1, 'products');
    }

    public function test_index_with_filters()
    {
        $response = $this->json('GET', 'api/products/list?category=boo&priceLessThan=90000', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonCount(1, 'products');
    }

    public function test_index_with_filters_no_results()
    {
        $response = $this->json('GET', 'api/products/list?category=boo&priceLessThan=4000', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonCount(0, 'products');
    }

    public function test_destroy()
    {
        $this->json('DELETE', 'api/products/destroy/00021', [], ['Accept' => 'application/json'])
            ->assertStatus(200);
    }

    public function test_destroy_error()
    {
        $this->json('DELETE', 'api/products/destroy/00022', [], ['Accept' => 'application/json'])
            ->assertStatus(404);
    }

    public function test_index_after_delete()
    {
        $response = $this->json('GET', 'api/products/list', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonCount(0, 'products');
    }
}
