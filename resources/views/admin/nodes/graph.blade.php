@extends('admin.layout', ['title' => 'Node Graph', 'heading' => 'Node Graph'])

@section('content')
    <section class="card-admin p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Force-directed graph of nodes and directed connections.') }}</p>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.nodes.index') }}" class="admin-btn admin-btn-ghost">{{ __('Node List') }}</a>
                <a href="{{ route('admin.node-connections.index') }}" class="admin-btn admin-btn-ghost">{{ __('Connection List') }}</a>
                <a href="{{ route('admin.nodes.create') }}" class="admin-btn admin-btn-primary">{{ __('Add Node') }}</a>
                <a href="{{ route('admin.node-connections.create') }}" class="admin-btn admin-btn-primary">{{ __('Add Connection') }}</a>
            </div>
        </div>

        <div id="node-graph" class="h-[75vh] min-h-[32rem] w-full rounded-xl border border-gray-200 bg-black dark:border-gray-800"></div>
    </section>

    <script nonce="{{ request()->attributes->get('csp_nonce', '') }}">
        document.addEventListener('DOMContentLoaded', async function () {
            const container = document.getElementById('node-graph');

            if (container && window.loadGraph3DLibs) {
                try {
                    await window.loadGraph3DLibs();
                } catch (error) {
                    return;
                }
            }

            if (!container || !window.ForceGraph3D || !window.THREE || !window.SpriteText) {
                return;
            }

            const graphData = @json($graphData);
            const forceGraphData = {
                nodes: (graphData.nodes || []).map((node) => ({
                    id: node.id,
                    label: node.label || String(node.id),
                    color: node.font?.color || '#ffffff',
                    size: node.font?.size || 4,
                    image: node.image || null,
                })),
                links: (graphData.edges || []).map((edge) => ({
                    source: edge.from,
                    target: edge.to,
                })),
            };

            const Graph = window.ForceGraph3D()(container)
                .graphData(forceGraphData)
                .nodeThreeObject((node) => {
                    const group = new window.THREE.Group();

                    const text = new window.SpriteText(node.label);
                    text.color = node.color || '#ffffff';
                    text.textHeight = Number(node.size) > 0 ? Number(node.size) / 4 : 4;
                    group.add(text);

                    if (node.image) {
                        const texture = new window.THREE.TextureLoader().load(node.image);
                        const icon = new window.THREE.Sprite(new window.THREE.SpriteMaterial({ map: texture }));
                        icon.scale.set(9, 12, 1);
                        icon.position.set(0, 10, 0);
                        group.add(icon);
                    }

                    return group;
                })
                .nodeLabel('label')
                .linkColor(() => '#f3f4f6')
                .backgroundColor('#000000')
                .linkDirectionalArrowLength(3)
                .linkDirectionalArrowRelPos(1)
                .linkOpacity(0.8)
                .d3VelocityDecay(0.25);

            const fitGraph = function (duration) {
                const width = container.clientWidth || container.offsetWidth;
                const height = container.clientHeight || container.offsetHeight;

                if (width > 0 && height > 0) {
                    Graph.width(width);
                    Graph.height(height);
                }

                Graph.zoomToFit(duration ?? 700, 60);
            };

            Graph.onEngineStop(function () {
                fitGraph(700);
            });

            setTimeout(function () {
                fitGraph(700);
            }, 500);

            window.addEventListener('resize', function () {
                fitGraph(0);
            });
        });
    </script>
@endsection