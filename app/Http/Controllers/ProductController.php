<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Http\Requests\ProductDataRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductStrategyFactory;
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
        $validated = $request->validated();

        $type = ProductType::tryFrom($validated['type'] ?? ProductType::BATTERIES->value) ?? ProductType::BATTERIES;
        $search = $validated['q'] ?? null;
        $manufacturer = $validated['manufacturer'] ?? null;
        $perPage = 10;

        $strategy = ProductStrategyFactory::make($type);
        $repository = $strategy->repository();

        $manufacturers = $repository->manufacturers();

        $priceFrom = isset($validated['price_from']) && $validated['price_from'] !== '' ? (float) $validated['price_from'] : null;
        $priceTo = isset($validated['price_to']) && $validated['price_to'] !== '' ? (float) $validated['price_to'] : null;

        $capacityFrom = isset($validated['capacity_from']) && $validated['capacity_from'] !== '' ? (float) $validated['capacity_from'] : null;
        $capacityTo = isset($validated['capacity_to']) && $validated['capacity_to'] !== '' ? (float) $validated['capacity_to'] : null;

        $powerFrom = isset($validated['power_from']) && $validated['power_from'] !== '' ? (float) $validated['power_from'] : null;
        $powerTo = isset($validated['power_to']) && $validated['power_to'] !== '' ? (float) $validated['power_to'] : null;

        $connectorType = $validated['connector_type'] ?? null;

        $paginator = $repository->paginate(
            $perPage,
            $search,
            $manufacturer,
            $priceFrom,
            $priceTo,
            $capacityFrom,
            $capacityTo,
            $powerFrom,
            $powerTo,
            $connectorType
        );

        // Use API Resource to serialize models consistently
        $data = ProductResource::collection($paginator->getCollection())->toArray(request());

        $extra = [];
        // provide type-specific helper data for the frontend
        $extra['capacityRange'] = $repository->capacityRange();
        $extra['powerRange'] = $repository->powerRange();
        $extra['connectorTypes'] = $repository->connectorTypes();

        return response()->json([
            'items' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'manufacturers' => $manufacturers,
            'type' => $type->value,
            'q' => $search,
            'search' => $search,
            'selectedManufacturer' => $manufacturer,
            'extra' => $extra,
        ]);
    }
}
