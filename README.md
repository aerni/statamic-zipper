# Zipper

![Statamic](https://flat.badgen.net/badge/Statamic/3.0+/FF269E)

> This addon provides a simple way to zip your assets on the fly

## Installation
Install the addon using Composer.

```bash
composer require aerni/zipper
```

## Configuration
You may also publish the config of the addon.

```bash
php please vendor:publish --tag=zipper-config
```

The following config will be published to `config/zipper.php`.

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Route
    |--------------------------------------------------------------------------
    |
    | Define the route that handles creating the zip files.
    |
    */

    'route' => 'zipper',

    /*
    |--------------------------------------------------------------------------
    | Save To Disk
    |--------------------------------------------------------------------------
    |
    | Set this to 'true' to save the created zips to disk.
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

To create a zip of your assets, you have to call the `zip` tag followed by the `variable` containing your assets.

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

The tag returns the URL to the route that handles creating the zip. The zip will be streamed and won't be saved to disk. You can change this behaviour in the config.

### Filename

By default, the filename of the zip will be the current timestamp. You can also customize the filename. The example below binds the filename of the zip to the title of the current page.

```html
{{ zip:images :filename='title' }}
```
