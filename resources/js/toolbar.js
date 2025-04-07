class SuperAdminToolbar {
    constructor() {
        this.cookieName = 'super-admin-toolbar-opened';
        this.container = document.getElementById('super-admin-toolbar');
        this.toggleButton = document.getElementById('super-admin-toolbar-toggle');
        this.activeDropdown = null;
    }

    init() {
        if (!this.container || !this.toggleButton) return;

        this.syncToolbarState();
        this.toggleButton.addEventListener('click', () => this.toggleToolbar());
        document.addEventListener('click', (e) => this.handleClick(e));
    }

    syncToolbarState() {
        this.container.toggleAttribute('si-group-toggled', this.getCookie(this.cookieName) === 'true');
    }

    toggleToolbar() {
        const toggled = this.container.hasAttribute('si-group-toggled');
        this.container.toggleAttribute('si-group-toggled', !toggled);
        this.setCookie(this.cookieName, !toggled);
        if (toggled) this.closeDropdown();
    }

    handleClick(e) {
        const toggle = e.target.closest('[data-dropdown-toggle]');
        if (toggle) {
            const menu = document.getElementById(toggle.dataset.dropdownToggle);
            if (menu) this.toggleDropdown(menu, toggle);
            return;
        }

        if (this.activeDropdown && !e.target.closest('.si-toolbar-dropdown-menu') && !e.target.closest('[data-dropdown-toggle]')) {
            this.closeDropdown();
        }
    }

    toggleDropdown(menu, toggle) {
        if (this.activeDropdown === menu) {
            this.closeDropdown();
            return;
        }

        this.closeDropdown();
        menu.style.display = 'block';
        this.activeDropdown = menu;
        this.activeToggle = toggle;
    }

    closeDropdown() {
        if (!this.activeDropdown) return;
        this.activeDropdown.style.display = 'none';
        this.activeDropdown = null;
        this.activeToggle = null;
    }

    getCookie(name) {
        const match = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return match ? match.pop() : '';
    }

    setCookie(name, value) {
        document.cookie = `${name}=${value}; path=/; SameSite=Lax; max-age=${60 * 60 * 24 * 365}`;
    }
}

window.SuperAdminToolbar = SuperAdminToolbar;
