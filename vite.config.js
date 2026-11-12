import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    css: {
        postcss: {
            plugins: [{
                // Keep Tailwind's theme and internal properties from leaking into the host page's Tailwind.
                postcssPlugin: 'scope-toolbar-css',
                Once(root) {
                    root.walkRules(rule => {
                        if (rule.selectors.join() === ':root,:host') {
                            rule.selector = '.super-admin-toolbar';
                        }
                    });
                    root.walkAtRules('property', rule => {
                        rule.params = rule.params.replace(/^--tw-/, '--sat-');
                    });
                    root.walkDecls(declaration => {
                        declaration.prop = declaration.prop.replace(/^--tw-/, '--sat-');
                        declaration.value = declaration.value.replaceAll('--tw-', '--sat-');
                    });
                },
            }],
        },
    },
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
