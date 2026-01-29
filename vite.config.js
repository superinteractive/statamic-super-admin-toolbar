import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/js/load-toolbar.js',
                'resources/js/toolbar.js',
                'resources/css/toolbar.css',
            ],
            refresh: true,
            publicDirectory: 'resources/dist',
            hotFile: 'resources/dist/hot',
        }),
    ],
});
