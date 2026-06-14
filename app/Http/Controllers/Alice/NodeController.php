<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\StoreNodeRequest;
use App\Http\Requests\Alice\UpdateNodeRequest;
use App\Http\Resources\Alice\NodeResource;
use App\Models\Node;
use App\Models\NodeConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NodeController extends AliceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Node::with(['linksFrom'])
            ->when($request->query('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"));

        $this->applySort($query, $request, ['name', 'created_at']);

        return $this->paginate($query, $request, NodeResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $node = Node::with(['linksFrom', 'linksTo'])->find($id);

        return $node ? $this->success(new NodeResource($node)) : $this->notFound();
    }

    public function store(StoreNodeRequest $request): JsonResponse
    {
        $exists = Node::where('name', $request->name)->exists();
        if ($exists) {
            return $this->conflict('Bu isimde bir düğüm zaten mevcut');
        }

        $node = Node::create($request->validated());

        // Create connections if provided
        if ($request->has('connect_to')) {
            foreach ((array) $request->connect_to as $toId) {
                NodeConnection::firstOrCreate([
                    'node_from_id' => $node->id,
                    'node_to_id' => $toId,
                ]);
            }
        }

        $node->load(['linksFrom']);

        return $this->success(new NodeResource($node), 201);
    }

    public function update(UpdateNodeRequest $request, int $id): JsonResponse
    {
        $node = Node::find($id);
        if (! $node) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $node);
        $node->update($request->validated());
        $node->load(['linksFrom']);

        return $this->success(new NodeResource($node));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $node = Node::find($id);
        if (! $node) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $node);
        $node->delete();

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
