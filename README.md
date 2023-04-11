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

    /*
    |--------------------------------------------------------------------------
    | Cleanup Scope
    |--------------------------------------------------------------------------
    |
    | The scope to use when cleaning up your zip references with the scheduled command.
    |
    | Options:
    | "expired": Only delete expired reference files
    | "all": Delete all reference files excluding unexpired files
    | "force": Delete all reference files including unexpired files
    |
    */

    'cleanup' => 'expired',

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

You may optionally pass a filename using the `filename` parameter. The filename defaults to the timestamp when the Zip object was created. The example below binds the name of the zip to the title of the page.

```antlers
{{ zip:images :filename="title" }}
```

### Link Expiry

If you want to expire your links after a certain time, you can either set the expiry globally in the config, or use the `expiry` parameter on the tag. The expiry is to be set in minutes. Note, that the expiry on the tag will overide the expiry in the config.

```antlers
{{ zip:images expiry="60" }}
```

## Cleaning old references

Every time Zipper returns a URL, it will save the encrypted Zip instance to `storage/zipper/{id}`. This instance is then retrieved by the controller whenever a user requests to download a zip. As time goes on, the amound of saved references will grow. To get this under control, Zipper includes a scheduled command that daily cleans old references. Just make sure that your Scheduler is running.

### Cleanup Scopes

There are a couple of different cleanup scope options you can define in the config:

| Option    | Description                                                                                                                                                                             |
|-----------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `expired` | Only delete expired references files. This only affects references of zips that used the `expiry` option                                                                                |
| `all`     | Delete all reference files excluding unexpired files. This will delete references of zips that didn't use the expiry option as well as expired zips. It will not delete unexpired zips. |
| `force`   | Delete all reference files inlcuding unexpired files. This will completely wipe all references.                                                                                         |

### Command

You may also use the clean command at your will. The scope defaults to `expired` if you don't provide one:

```bash
php please zipper:clean
php please zipper:clean --scope=all
php please zipper:clean --scope=force
```

## Advanced Usage

You may also use this addon programmatically as shown below.

```php
use Aerni\Zipper\Zip;

// Prepare an array of Statamic assets, paths or URLs.
$files = [
    Statamic\Assets\Asset,
    '/home/ploi/site.com/storage/app/assets/file_1.jpg',
    'https://site.com/path/to/file_2.jpg',
];

// Make a zip with the files above.
$zip = Zip::make($files);

// Set an optional filename. This defaults to the timestamp when the object was created.
$zip->filename('obi-wan-kenobi')

// Set an optional expiry time in minutes. This defaults to the expiry set in the config.
$zip->expiry(60);

// Get the URL that handles creating the zip.
$zip->url();

// Create a new zip or download a previously cached zip.
$zip->get();
```
