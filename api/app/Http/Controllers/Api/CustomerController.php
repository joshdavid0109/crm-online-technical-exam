<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\ElasticsearchService;


class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('search')) {
            return $this->elasticsearch->search($request->search);
        }

        return Customer::all();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:customers,email',
            'contact_number' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        $this->elasticsearch->indexCustomer(
            $customer->toArray(),
            $customer->id
        );

        return response()->json($customer, 201);
    }




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'contact_number' => 'nullable|string',
        ]);

        $customer->update($validated);

        $this->elasticsearch->indexCustomer(
            $customer->toArray(),
            $customer->id
        );

        return response()->json($customer);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $this->elasticsearch->deleteCustomer($customer->id);
        $customer->delete();

        return response()->json(['message' => 'Deleted']);
    }


    protected ElasticsearchService $elasticsearch;

    public function __construct(ElasticsearchService $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

}
