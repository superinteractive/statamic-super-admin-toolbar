document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (document.getElementById('super-admin-toolbar')) {
        return;
    }

    fetch('/super-admin-toolbar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({url: window.location.href}),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.json();
        })
        .then(data => {
            if (!data || data.authenticated === false) {
                return; // User not authenticated, silently exit
            }

            if (!data.html) {
                return;
            }

            document.body.insertAdjacentHTML('beforeend', data.html);

            if (data.css) {
                const linkEl = document.createElement('link');
                linkEl.setAttribute('rel', 'stylesheet');
                linkEl.setAttribute('href', data.css);
                document.head.appendChild(linkEl);
            }

            if (data.js) {
                const scriptEl = document.createElement('script');
                scriptEl.setAttribute('src', data.js);
                scriptEl.setAttribute('type', 'module');

                scriptEl.onload = () => {
                    if (typeof SuperAdminToolbar !== 'undefined') {
                        const superAdminToolbar = new SuperAdminToolbar();
                        superAdminToolbar.init();
                    } else {
                        console.error('SuperAdminToolbar class not found after loading script.');
                    }
                };
                scriptEl.onerror = () => {
                    console.error('Failed to load toolbar script:', data.js);
                };
                document.body.appendChild(scriptEl);
            }
        })
        .catch(err => {
            if (err === 'unauthorized') return; // Silent fail

            console.error('Error loading Super Admin Toolbar:', err);
        });
});
