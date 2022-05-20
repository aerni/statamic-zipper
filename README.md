![Statamic](https://flat.badgen.net/badge/Statamic/3.3.12+/FF269E) ![Packagist version](https://flat.badgen.net/packagist/v/aerni/statamic-zipper/latest) ![Packagist Total Downloads](https://flat.badgen.net/packagist/dt/aerni/statamic-zipper)

# Zipper
This addon provides a simple way to zip your Statamic assets on the fly.

## Installation
Install the addon using Composer:

```bash
composer require aerni/zipper
```

Publish the config of the package (optional):

```bash
php please vendor:publish --tag=zipper-config
```

The following config will be published to `config/zipper.php`:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Save To Disk
    |--------------------------------------------------------------------------
    |
    | Set this to 'true' to save the zips to disk.
    | The saved file will be used the next time a user requests a zip with the same payload.
    |
    */

    'save' => false,

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Choose the disk you want to use when saving a zip.
    |
    */

    'disk' => 'public',

];
```

## Basic Usage

To create a zip of your assets, you have to call the `zip` tag followed by the `variable` containing your assets. The tag returns the URL to the route that handles creating the zip. The zip will be streamed without being saved to disk. You may opt in to save the file to disk to be used on subsequent requests.

Somewhere in your content files:

```yaml
images:
  - sega-genesis.jpg
  - snes.jpg
```

Somehwere in your views:

```html
{{ zip:images }}
```

You may optionally pass a filename using the `filename` parameter. The example below binds the name of the zip to the title of the current page. The filename defaults to the current timestamp.

```html
{{ zip:images :filename='title' }}
```

## Advanced Usage

This addon also exposes two methods that let you get the route or create a zip programmatically.

The `route` method returns the route that handles creating the zip. This is the same as using the `zip` tag in your views:

```php
\Aerni\Zipper\Zipper::route($files, $filename);
```

The `create` method creates and returns the zip directly:

```php
\Aerni\Zipper\Zipper::create($files, $filename);
```

The `$files` can be either one of the following:

```php
// An array of Statamic assets
$files = [
    Statamic\Assets\Asset,
    Statamic\Assets\Asset,
    Statamic\Assets\Asset,
]

// An array of URLs
$files = [
    '/path/to/file.jpg',
    '/path/to/file_2.jpg',
    '/path/to/file_3.jpg',
]

// An array of associative arrays with a 'url' key
$files = [
    [
        'url' => '/path/to/file.jpg',
    ],
    [
        'url' => '/path/to/file_2.jpg',
    ],
    [
        'url' => '/path/to/file_3.jpg',
    ],
]
```
