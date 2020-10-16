# Zipper

![Statamic](https://flat.badgen.net/badge/Statamic/3.0+/FF269E)

> This addon provides a simple way to zip your assets on the fly

## Installation
Install the addon using Composer.

```bash
composer require aerni/zipper
```

You may also publish the config of the addon.

```bash
php please vendor:publish --tag=zipper-config
```

The following config will be published to `config/zipper.php`.

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Configure the filesystem disk you want to use for your zip files.
    | This will determine the path and url of the zip.
    |
    */

    'disk' => 'public',

];
```

## Basic Usage

To create a zip of your assets, you have to call the tag followed by the variable containing your assets. The tag will return the URL to the generated zip.

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

By default, the filename of the zip will be the current timestamp. You may customize the filename to your liking. I highly suggest you do so. If you don't, a new zip will be created on every request.

```template
{{ zip:images :filename='title' }}
```
