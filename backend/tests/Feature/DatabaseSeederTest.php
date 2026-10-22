<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_seeder_adds_repeatable_shared_master_data_without_accounts(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('item_categories', 5);
        $this->assertDatabaseCount('brands', 5);
        $this->assertDatabaseCount('uoms', 5);
        $this->assertDatabaseCount('users', 0);

        $this->assertDatabaseHas('item_categories', ['name' => 'Electronics']);
        $this->assertDatabaseHas('brands', ['name' => 'Samsung']);
        $this->assertDatabaseHas('uoms', ['name' => 'Piece']);
    }
}
