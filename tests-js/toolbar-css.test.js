import { build } from 'vite';
import { expect, it } from 'vitest';

it('scopes the toolbar stylesheet so it cannot override the host page', async () => {
    const { output } = await build({ logLevel: 'silent', build: { write: false } });
    const css = output.find(file => file.fileName.endsWith('.css')).source;

    expect(css).toMatch(/\.super-admin-toolbar\{[^}]*--spacing:/);
    expect(css).not.toMatch(/:root|:host|--tw-/);
});
