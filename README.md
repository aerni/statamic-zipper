# Zipper

![Statamic](https://flat.badgen.net/badge/Statamic/3.0+/FF269E)

> This addon provides a simple way to zip your assets on the fly

## Installation
Install the addon using Composer.

```bash
composer require aerni/zipper
```

## Basic Usage

To create a zip of your assets, you have to call the tag followed by the variable containing your assets. The tag will return the url to the generated zip.

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

By default, the filename of the zip will be a timestamp. You may customize the filename to your liking.

```template
{{ zip:images :filename='title' }}
```
