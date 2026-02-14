<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\ElasticsearchService;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        if ($request->filled('search')) {
            try {
                $results = $this->elasticsearch->search($request->search);

                $customers = collect($results)->map(function ($item) {
                    return new Customer($item);
                });

                return CustomerResource::collection($customers);
            } catch (\Exception $e) {
                \Log::error('Elasticsearch search failed: ' . $e->getMessage());
                return response()->json(['error' => 'Search failed'], 500);
            }
        }

        return CustomerResource::collection(Customer::all());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        $customer = Customer::create($validated);

        try {
            $this->elasticsearch->indexCustomer(
                $customer->toArray(),
                $customer->id
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Log::error('Failed to index customer in Elasticsearch: ' . $e->getMessage());
        }
    
        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $validated = $request->validated();
        

        $customer->update($validated);

        try{
            $this->elasticsearch->indexCustomer(
                $customer->toArray(),
                $customer->id
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Log::error('Failed to update customer in Elasticsearch: ' . $e->getMessage());
        }

        return new CustomerResource($customer);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->elasticsearch->deleteCustomer($customer->id);
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Log::error('Failed to delete customer from Elasticsearch: ' . $e->getMessage());
        }
        $customer->delete();

        return response()->json(null, 204);
    }


    protected ElasticsearchService $elasticsearch;

    public function __construct(ElasticsearchService $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

}
