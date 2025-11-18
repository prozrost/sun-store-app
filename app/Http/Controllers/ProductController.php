<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Http\Requests\ProductDataRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductStrategyFactory;
use App\Http\DTOs\ProductQueryDTO;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(): View
    {
        // Render only the HTML shell. The client will request data from `data()` via AJAX

        return view('products.index');
    }

    /**
     * Return JSON data for AJAX requests.
     */
    public function data(ProductDataRequest $request): JsonResponse
    {
        $dto = new ProductQueryDTO($request->validated());

        $strategy = ProductStrategyFactory::make($dto->type);
        $repository = $strategy->repository();

        $manufacturers = $repository->manufacturers();

        $paginator = $repository->paginate($dto);

        // Use API Resource to serialize models consistently
        $data = ProductResource::collection($paginator->getCollection())->toArray(request());

        $extra = [];
        
        // provide type-specific helper data for the frontend
        if (method_exists($repository, 'capacityRange')) {
            $extra['capacityRange'] = $repository->capacityRange();
        }
        if (method_exists($repository, 'powerRange')) {
            $extra['powerRange'] = $repository->powerRange();
        }
        if (method_exists($repository, 'connectorTypes')) {
            $extra['connectorTypes'] = $repository->connectorTypes();
        }

        return response()->json([
            'items' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'manufacturers' => $manufacturers,
            'type' => $dto->type->value,
            'q' => $dto->search,
            'search' => $dto->search,
            'selectedManufacturer' => $dto->manufacturer,
            'extra' => $extra,
        ]);
    }
}
