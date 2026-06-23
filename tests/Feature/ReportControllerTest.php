<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    public function test_can_get_financial_report(): void
    {
        $response = $this->getJson('/api/reports/financial');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'revenue_by_date',
                    'revenue_by_method',
                ],
            ]);
    }

    public function test_can_get_demographics_report(): void
    {
        $response = $this->getJson('/api/reports/demographics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'visits_by_species',
                    'visits_by_breed',
                ],
            ]);
    }

    public function test_can_get_stock_mutation_report(): void
    {
        $response = $this->getJson('/api/reports/stock-mutation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'mutations',
                    'raw_mutations',
                ],
            ]);
    }
}
