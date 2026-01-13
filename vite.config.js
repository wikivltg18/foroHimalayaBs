import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css', 'resources/css/custom.css', 'resources/js/custom.js', 'resources/js/create-task-calendar.js', 'resources/js/team_calendar.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '$': 'jquery'
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['jquery', 'bootstrap', 'sweetalert2', 'sortablejs']
                }
            }
        }
    }
});
