![Statamic](https://flat.badgen.net/badge/Statamic/4.0+/FF269E) ![Packagist version](https://flat.badgen.net/packagist/v/aerni/zipper/latest) ![Packagist Total Downloads](https://flat.badgen.net/packagist/dt/aerni/zipper)

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

## Basic Usage

To create a zip of your assets, you must call the `zip` tag followed by the `variable` containing your assets. The tag returns the URL to the route that handles creating the zip. The zip will be streamed without being saved to disk, but you may opt-in to save the file to disk for later use.

Somewhere in your content files:

```yaml
images:
  - sega-genesis.jpg
  - snes.jpg
```

Somewhere in your views:

```antlers
{{ zip:images }}
```

### Filename

You may optionally pass a filename using the `filename` parameter. The filename defaults to the current timestamp when the Zipper object is created. The example below binds the zip name to the page title.

```antlers
{{ zip:images :filename="title" }}
```

### Link Expiry

If you want to expire your links after a certain time, you can either set the expiry globally in the config or use the `expiry` parameter on the tag. The expiry is to be set in minutes. Note that the expiry on the tag will override the expiry in the config.

```antlers
{{ zip:images expiry="60" }}
```

## Cleanup Old References

Zipper saves an encrypted instance of the Zipper class every time it returns a URL. This class is later retrieved and decrypted when a user downloads a zip. These reference files are stored in `storage/zipper/{id}`.

With time, the number of saved reference files will grow. To control this, Zipper provides a scheduled command that will delete old reference files daily. Just make sure that your Scheduler is running.

### Cleanup Scopes

There are a couple of cleanup scopes to choose from in the config.:

| Option    | Description                                                                                                                                                                       |
|-----------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `expired` | Only delete expired references files. This only affects references of zips that used the `expiry` option                                                                          |
| `all`     | Delete all reference files, excluding unexpired files. This will delete references of expired zips and zips that didn't use the expiry option. It will not delete unexpired zips. |
| `force`   | Delete all reference files, including unexpired files. This will completely wipe all references.                                                                                  |

### Clean Command

You may also use the `clean` command to delete reference files at your will. The scope defaults to `expired`.

```bash
php please zipper:clean
php please zipper:clean --scope=all
php please zipper:clean --scope=force
```

## Advanced Usage

You may also use this addon programmatically, as shown below.

```php
use Aerni\Zipper\Facades\Zipper;

// Prepare an array of Statamic assets, paths, or URLs.
$files = [
    Statamic\Assets\Asset,
    '/home/ploi/site.com/storage/app/assets/file_1.jpg',
    'https://site.com/path/to/file_2.jpg',
];

// Make a zip with the files above.
$zip = Zipper::make($files);

// Set an optional filename. This defaults to the timestamp when the object was created.
$zip->filename('obi-wan-kenobi');

// Set an optional expiry time in minutes. This defaults to the expiry set in the config.
$zip->expiry(60);

// Get the URL that handles creating the zip.
$zip->url();

// Create a new zip or download a previously cached zip.
$zip->get();
```
