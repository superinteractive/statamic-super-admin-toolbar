import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        tailwindcss(),

        // Default TailwindCSS uses :root and :host to scope the theme, but we need to scope it to .super-admin-toolbar instead.
        {
            name: 'scope-tailwind-theme',
            generateBundle(opts, bundle) {
                for (const chunk of Object.values(bundle)) {
                    if (chunk.fileName?.endsWith('.css') && 'source' in chunk) {
                        chunk.source = chunk.source.replace(/:root,:host(?=\{)/g, '.super-admin-toolbar');
                    }
                }
            },
        },
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
