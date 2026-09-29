<?php

namespace Tests\Feature;

use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        Family::create([
            'name' => 'Família dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
