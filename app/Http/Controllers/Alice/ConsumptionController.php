<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreConsumptionRequest;
use App\Http\Requests\Alice\UpdateConsumptionRequest;
use App\Http\Resources\Alice\ConsumptionResource;
use App\Models\Consumption;
use App\Services\AliceLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsumptionController extends AliceController
{
    public function __construct(private AliceLookupService $lookup) {}

    public function index(Request $request): JsonResponse
    {
        $query = Consumption::with('food')
            ->when($request->query('food_id'), fn ($q, $id) => $q->where('food_id', $id))
            ->when($request->query('with_trashed'), fn ($q) => $q->withTrashed());

        $this->applyDateRange($query, $request, 'consumed_on');
        $this->applySort($query, $request, ['consumed_on', 'quantity', 'created_at']);

        return $this->paginate($query, $request, ConsumptionResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $consumption = Consumption::with('food')->find($id);

        return $consumption ? $this->success(new ConsumptionResource($consumption)) : $this->notFound();
    }

    public function store(StoreConsumptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['food']) && ! is_numeric($data['food'])) {
            $resolved = $this->lookup->resolveFood($data['food']);

            if (! $resolved) {
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' => "Besin bulunamadı: \"{$data['food']}\". Önce POST /foods ile oluşturun.",
                    ],
                ], 404);
            }

            $data['food_id'] = $resolved->id;
        }
        unset($data['food']);

        $consumption = Consumption::create($data);
        $consumption->load('food');

        return $this->success(new ConsumptionResource($consumption), 201);
    }

    public function update(UpdateConsumptionRequest $request, int $id): JsonResponse
    {
        $consumption = Consumption::find($id);
        if (! $consumption) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $consumption);
        $data = $request->validated();

        if (isset($data['food']) && ! is_numeric($data['food'])) {
            $resolved = $this->lookup->resolveFood($data['food']);

            if (! $resolved) {
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' => "Besin bulunamadı: \"{$data['food']}\". Önce POST /foods ile oluşturun.",
                    ],
                ], 404);
            }

            $data['food_id'] = $resolved->id;
        }
        unset($data['food']);

        $consumption->update($data);
        $consumption->load('food');

        return $this->success(new ConsumptionResource($consumption));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $consumption = Consumption::find($id);
        if (! $consumption) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $consumption);
        $consumption->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
