<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NodeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Node::query()->withCount(['linksFrom', 'linksTo'])->latest('id');

        if ($search = trim((string) $request->string('q'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        return view('admin.nodes.index', [
            'title' => 'Nodes',
            'heading' => 'Nodes',
            'nodes' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        return view('admin.nodes.create', [
            'title' => 'New Node',
            'heading' => 'New Node',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Node::query()->create($this->validatedPayload($request));

        return to_route('admin.nodes.index')->with('success', 'Node created.');
    }

    public function edit(Node $node): View
    {
        return view('admin.nodes.edit', [
            'title' => 'Edit Node',
            'heading' => 'Edit Node',
            'node' => $node,
        ]);
    }

    public function update(Request $request, Node $node): RedirectResponse
    {
        $node->update($this->validatedPayload($request, $node->id));

        return to_route('admin.nodes.index')->with('success', 'Node updated.');
    }

    public function destroy(Node $node): RedirectResponse
    {
        $node->delete();

        return to_route('admin.nodes.index')->with('success', 'Node deleted.');
    }

    public function graph(): View
    {
        $nodes = Node::query()->orderBy('name')->get();
        $graphNodes = $nodes->map(fn (Node $node) => [
            'id' => $node->id,
            'label' => $node->name,
            'font' => [
                'color' => $node->text_color ?: '#111827',
                'size' => (int) ($node->text_size ?: 18),
            ],
            'shape' => $node->image ? 'circularImage' : 'dot',
            'image' => $node->image ? asset('storage/'.$node->image) : null,
            'size' => $node->image ? 30 : 18,
        ])->values();

        $graphEdges = \App\Models\NodeConnection::query()
            ->orderBy('id')
            ->get()
            ->map(fn ($connection) => [
                'from' => $connection->node_from_id,
                'to' => $connection->node_to_id,
                'arrows' => 'to',
            ])
            ->values();

        return view('admin.nodes.graph', [
            'title' => 'Node Graph',
            'heading' => 'Node Graph',
            'graphData' => [
                'nodes' => $graphNodes,
                'edges' => $graphEdges,
            ],
        ]);
    }

    private function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:nodes,name,'.($ignoreId ?? 'NULL').',id'],
            'text_color' => ['nullable', 'string', 'max:32'],
            'text_size' => ['nullable', 'string', 'max:32'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);
    }
}