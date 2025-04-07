import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/load-toolbar.js',
                'resources/js/toolbar.js',
                'resources/css/toolbar.css',
            ],
            publicDirectory: 'dist',
        }),
    ],
});
