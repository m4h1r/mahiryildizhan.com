@extends('admin.layout', ['title' => 'Family Tree', 'heading' => 'Family Tree'])

@section('content')
    <section class="card-admin p-4 md:p-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hierarchical View</p>
                <h2 class="text-xl font-semibold">{{ $person->name }} {{ $person->surname }}</h2>
            </div>
            <a href="{{ route('admin.people.index') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700">Back</a>
        </div>

        <div id="tree-network" class="h-[640px] w-full rounded-lg border border-gray-200 dark:border-gray-700"></div>
    </section>

    <script src="https://unpkg.com/vis-network@10.0.2/standalone/umd/vis-network.min.js"></script>
    <script>
        const treeData = @json($graphData);
        const treeContainer = document.getElementById('tree-network');
        const isDark = document.documentElement.classList.contains('dark');

        const groups = {
            male:    { color: { background: '#2563eb', border: '#1d4ed8', highlight: { background: '#3b82f6', border: '#1d4ed8' } }, font: { color: '#ffffff' }, shape: 'box' },
            female:  { color: { background: '#ec4899', border: '#db2777', highlight: { background: '#f472b6', border: '#db2777' } }, font: { color: '#ffffff' }, shape: 'box' },
            other:   { color: { background: '#6b7280', border: '#4b5563', highlight: { background: '#9ca3af', border: '#4b5563' } }, font: { color: '#ffffff' }, shape: 'box' },
            partner: { color: { background: '#f43f5e', border: '#e11d48', highlight: { background: '#fb7185', border: '#e11d48' } }, font: { color: '#ffffff' }, shape: 'box' },
            sibling: { color: { background: '#6366f1', border: '#4f46e5', highlight: { background: '#818cf8', border: '#4f46e5' } }, font: { color: '#ffffff' }, shape: 'box' },
        };

        const treeNetwork = new vis.Network(
            treeContainer,
            {
                nodes: new vis.DataSet(treeData.nodes),
                edges: new vis.DataSet(treeData.edges),
            },
            {
                groups,
                layout: {
                    hierarchical: {
                        enabled: true,
                        direction: 'UD',
                        sortMethod: 'directed',
                        nodeSpacing: 200,
                        levelSeparation: 180,
                        parentCentralization: true,
                        blockShifting: true,
                        edgeMinimization: true,
                    },
                },
                physics: false,
                interaction: {
                    hover: true,
                    tooltipDelay: 150,
                },
                nodes: {
                    margin: { top: 6, bottom: 6, left: 10, right: 10 },
                    font: { size: 13 },
                },
                edges: {
                    smooth: {
                        type: 'cubicBezier',
                        forceDirection: 'vertical',
                        roundness: 0.35,
                    },
                    color: {
                        color: isDark ? '#94a3b8' : '#64748b',
                        inherit: false,
                    },
                    font: {
                        size: 10,
                        color: isDark ? '#94a3b8' : '#64748b',
                        strokeWidth: 0,
                        align: 'middle',
                    },
                    width: 1.5,
                },
                configure: false,
            }
        );

        treeNetwork.fit({
            animation: {
                duration: 400,
                easingFunction: 'easeInOutQuad',
            },
        });
    </script>
@endsection
