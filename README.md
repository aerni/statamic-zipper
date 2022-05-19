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

The tag returns the URL to the route that handles creating the zip. The zip will be streamed and won't be saved to disk. You may opt in to save the file to disk to be used on subsequent requests.

### Filename

By default, the filename of the downloaded zip will be the current timestamp. You can also customize the filename. The example below binds the filename of the zip to the title of the current page.

```html
{{ zip:images :filename='title' }}
```
