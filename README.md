<!-- statamic:hide -->

![Statamic 5.0+](https://img.shields.io/badge/Statamic-5.0+-FF269E?style=flat-square&link=https://statamic.com)

![Super Admin Toolbar](./docs/superadmintoolbar.jpg)

<!-- /statamic:hide -->

# Super Admin Toolbar

A powerful admin toolbar for Statamic 5 that enhances content management workflow for editors and copywriters. It provides quick access to editing tools, SEO settings, and site management directly from your frontend. Perfect for those coming from WordPress and missing the admin bar!

## Features

- **Admin Toolbar**: Displays a toolbar for quick administrative actions when a user with `access cp` is signed in.
- **Quick Edit Links**: Easily edit the current entry or quickly create a new entry in the same collection.
- **Site Switcher**: Seamlessly switch between multi-sites right from the toolbar.
- **Static Cache Compatible**: Works seamlessly with both full and half measure static caching approaches.
- **SEO Pro Integration**: Convenient shortcuts to SEO Pro settings for optimizing your content.

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

Once installed, the Super Admin Toolbar automatically loads for users with the `access cp` permission. The toolbar is dynamically injected using JavaScript, ensuring compatibility with Statamic's full and half measure static caching.

## Supported Addons

The Super Admin Toolbar integrates with the following Statamic addons:

- **SEO Pro**: Quick access to SEO settings directly from the toolbar when viewing content on your site.

## Support

For issues or feature requests, please visit the [GitHub issues page](https://github.com/superinteractive/statamic-super-admin-toolbar).

## License

The MIT License (MIT). Please see the [License File](https://github.com/superinteractive/statamic-super-admin-toolbar/blob/main/LICENSE.md) for more information.
