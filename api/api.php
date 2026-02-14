<? php

use Illuminate\Support\Facades\Router;
use App\Http\Controllers\Api\CustomerController;

Route:apiResource('customers', CustomerController::class);


