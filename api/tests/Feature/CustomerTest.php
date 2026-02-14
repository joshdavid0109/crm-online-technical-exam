<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use App\Services\ElasticsearchService;
use Mockery;


class CustomerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_search_customers(): void
    {
        $mock = Mockery::mock(ElasticsearchService::class);

        $mock->shouldReceive('search')
            ->once()
            ->with('john')
            ->andReturn([
                [
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com',
                    'contact_number' => '1234567890',
                ]
            ]);

        $this->app->instance(ElasticsearchService::class, $mock);

        $response = $this->getJson('/api/customers?search=john');

        $response->assertStatus(200)
                ->assertJsonPath('data.0.first_name', 'John');
    }

    #[Test]
    public function email_must_be_unique(): void
    {
        Customer::factory()->create([
            'email' => 'duplicate@example.com'
        ]);

        $response = $this->postJson('/api/customers', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'duplicate@example.com',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_can_view_a_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $customer->id);
    }

    #[Test]
    public function it_can_update_a_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->putJson("/api/customers/{$customer->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $customer->email,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.first_name', 'Updated');
    }

    #[Test]
    public function it_can_delete_a_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id
        ]);
    }

    #[Test]
    public function it_returns_search_results(): void
    {
        $mock = Mockery::mock(ElasticsearchService::class);

        $mock->shouldReceive('search')
            ->once()
            ->with('john')
            ->andReturn([
                [
                    'id' => 10,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com',
                    'contact_number' => '1234567890',
                ]
            ]);

        $this->app->instance(ElasticsearchService::class, $mock);

        $response = $this->getJson('/api/customers?search=john');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.first_name', 'John');
    }

    #[Test]
    public function search_returns_empty_array_when_no_results(): void
    {
        $mock = Mockery::mock(ElasticsearchService::class);

        $mock->shouldReceive('search')
            ->once()
            ->with('unknown')
            ->andReturn([]);

        $this->app->instance(ElasticsearchService::class, $mock);

        $response = $this->getJson('/api/customers?search=unknown');

        $response->assertStatus(200)
                ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function search_returns_500_if_elasticsearch_fails(): void
    {
        $mock = Mockery::mock(ElasticsearchService::class);

        $mock->shouldReceive('search')
            ->once()
            ->with('error')
            ->andThrow(new \Exception('ES failure'));

        $this->app->instance(ElasticsearchService::class, $mock);

        $response = $this->getJson('/api/customers?search=error');

        $response->assertStatus(500)
                ->assertJsonPath('error', 'Search failed');
    }

}
