<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NodeConnectionController extends Controller
{
    public function index(): View
    {
        return view('admin.node-connections.index', [
            'title' => 'Node Connections',
            'heading' => 'Node Connections',
            'connections' => NodeConnection::query()->with(['fromNode', 'toNode'])->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.node-connections.create', [
            'title' => 'New Node Connection',
            'heading' => 'New Node Connection',
            'nodes' => Node::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        NodeConnection::query()->create($this->validatedPayload($request));

        return to_route('admin.node-connections.index')->with('success', 'Node connection created.');
    }

    public function edit(NodeConnection $nodeConnection): View
    {
        return view('admin.node-connections.edit', [
            'title' => 'Edit Node Connection',
            'heading' => 'Edit Node Connection',
            'connection' => $nodeConnection,
            'nodes' => Node::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, NodeConnection $nodeConnection): RedirectResponse
    {
        $nodeConnection->update($this->validatedPayload($request, $nodeConnection->id));

        return to_route('admin.node-connections.index')->with('success', 'Node connection updated.');
    }

    public function destroy(NodeConnection $nodeConnection): RedirectResponse
    {
        $nodeConnection->delete();

        return to_route('admin.node-connections.index')->with('success', 'Node connection deleted.');
    }

    private function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'node_from_id' => [
                'required',
                'integer',
                'exists:nodes,id',
                Rule::unique('node_connections')
                    ->ignore($ignoreId)
                    ->where(fn ($query) => $query
                        ->where('node_from_id', $request->integer('node_from_id'))
                        ->where('node_to_id', $request->integer('node_to_id'))),
            ],
            'node_to_id' => ['required', 'integer', 'exists:nodes,id', 'different:node_from_id'],
        ]);
    }
}