![Statamic](https://flat.badgen.net/badge/Statamic/3.3.12+/FF269E) ![Packagist version](https://flat.badgen.net/packagist/v/aerni/zipper/latest) ![Packagist Total Downloads](https://flat.badgen.net/packagist/dt/aerni/zipper)

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
    | Set this to 'true' to save the created zips to disk.
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

    /*
    |--------------------------------------------------------------------------
    | Link Expiry
    |--------------------------------------------------------------------------
    |
    | Set the time in minutes after which a link should expire.
    |
    */

    'expiry' => null,

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

```antlers
{{ zip:images }}
```

### Filename

You may optionally pass a filename using the `filename` parameter. If you don't provide one, the filename will default to the timestamp at the time of download. The example below binds the name of the zip to the title of the page.

```antlers
{{ zip:images :filename='title' }}
```

### Link Expiry

If you want to expire your links after a certain time, you can either set the expiry globally in the config, or use the `expiry` parameter on the tag. The expiry is to be set in minutes. Note, that the expiry on the tag will overide the expiry in the config.

```antlers
{{ zip:images expiry="60" }}
```

## Advanced Usage

You may also use this addon programmatically as shown below.

```php
// Make a zip from an array of files.
$zip = \Aerni\Zipper\Zip::make($files);

// The files need to be an array of assets, paths or URLs.
$files = [
    Statamic\Assets\Asset,
    '/home/ploi/site.com/storage/app/assets/file_1.jpg',
    'https://site.com/path/to/file_2.jpg',
];

// Set a custom filename. If no filename is provided, it falls back to the current timestamp.
$zip->filename('obi-wan-kenobi')

// Set the time in minutes, after which the zip route should expire.
$zip->expiry(60);

// Get the route that handles creating the zip.
$zip->route();

// Create a new zip or download a previously cached zip.
$zip->get();
```
