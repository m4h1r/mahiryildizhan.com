import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return;
                    }

                    if (id.includes('apexcharts')) {
                        return 'vendor-apexcharts';
                    }

                    if (id.includes('3d-force-graph') || id.includes('three') || id.includes('three-spritetext')) {
                        return 'vendor-3d-graph';
                    }

                    if (id.includes('chart.js')) {
                        return 'vendor-chartjs';
                    }

                    if (id.includes('vis-network') || id.includes('vis-data')) {
                        return 'vendor-vis';
                    }
                },
            },
        },
    },
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
