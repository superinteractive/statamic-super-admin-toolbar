<!-- statamic:hide -->

![Statamic 5.0+](https://img.shields.io/badge/Statamic-5.0+-FF269E?style=flat-square&link=https://statamic.com)

![Super Admin Toolbar](./docs/superadmintoolbar.jpg)

<!-- /statamic:hide -->

# Super Admin Toolbar

An admin toolbar for Statamic 5 that streamlines content management for editors and copywriters.

## Features

- **Admin Toolbar**: Displays a toolbar for quick administrative actions when a user with `access cp` is signed in.
- **Quick Edit Links**: Easily edit the current entry or quickly create a new entry in the same collection.
- **Site Switcher**: Seamlessly switch between multi-sites right from the toolbar.
- **Lazy Loaded**: Injected via JavaScript, ensuring minimal impact on initial page load.
- **Static Cache Compatible**: Works smoothly with Statamic's static caching approach.
- **SEO Pro Integration**: Convenient shortcuts to SEO Pro settings (when installed).

## Installation

Require the addon via Composer:

```bash
composer require superinteractive/statamic-super-admin-toolbar
```

Ensure your layout contains the CSRF token [meta tag](https://laravel.com/docs/12.x/csrf#csrf-x-csrf-token):

### Antlers:
```html
<meta name="csrf-token" content="{{ csrf_token }}">
```

### Blade:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Finally, include the toolbar in your layout file — ideally **before** the closing `</head>` tag:

#### Antlers:

```html
{{ super_admin_toolbar }}
```

#### Blade:

```blade
@superAdminToolbar
```

## Usage

Once installed, the Super Admin Toolbar automatically loads for users with the `access cp` permission. The toolbar is lazy-loaded using JavaScript, ensuring compatibility with Statamic's full and half measure static caching.

## Support

For issues or feature requests, please visit the [GitHub issues page](https://github.com/superinteractive/statamic-super-admin-toolbar).

## License

The MIT License (MIT). Please see the [License File](https://github.com/superinteractive/statamic-super-admin-toolbar/blob/main/LICENSE.md) for more information.
