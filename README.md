# ImageOptimize plugin for Craft CMS 3.x

Automatically optimize images after they've been transformed

## Installation

To install ImageOptimize, follow these steps:

1. Install with Composer via `composer require nystudio107/craft3-imageoptimize` from your project directory
2. Install plugin in the Craft Control Panel under Settings > Plugins

ImageOptimize works on Craft 3.x.

ImageOptimize won't do anything on its own; you'll need to also install the image optimization tools of your choice. Here's how to install a few on Ubuntu 16.04:

* **jpegoptim** - `sudo apt-get install jpegoptim`
* **mozjpeg** - [Installing mozjpeg on Ubuntu 16.04 (Forge)](https://nystudio107.com/blog/installing-mozjpeg-on-ubuntu-16-04-forge)
* **optipng** - `sudo apt-get install optipng`
* **svgo** - `sudo npm install -g svgo`
* **gifsicle** - `sudo apt-get install gifsicle`
* **webp** - `sudo apt-get install webp`

## ImageOptimize Overview

ImageOptimize allows you to optimize the images created by Craft 3's Image Transforms by automatically running a variety of image optimization tools on them. As configured by default, all of these are _lossless_ image optimizations that remove metadata and otherwise optimize the images without changing their appearance in any way.

Out of the box, ImageOptimize allows for the optimization of `JPG`, `PNG`, `SVG`, & `GIF` images, but you can add whatever additional types you want.

It's important to create optimized images for frontend delivery, especially for mobile devices. If you want to learn more about it, read the [Creating Optimized Images in Craft CMS](https://nystudio107.com/blog/creating-optimized-images-in-craft-cms) article.

Once ImageOptimize is installed, optimized versions of image transforms are created without you having to do anything. This makes it great for client-proofing websites.

ImageOptimize works equally well with both local and remote assets such as Amazon S3 buckets.

## Configuring ImageOptimize

The only configuration for ImageOptimize is in the `config.php` file, which is a multi-environment friendly way to store the default settings.  Don't edit this file, instead copy it to `craft/config` as `ImageOptimize.php` and make your changes there.

The `activeImageProcessors` array lets you specify which of the image optimization tools to use for which file types.

The `imageProcessors` array specifies the path and options for each of the image optimization tools.

See each image optimization tool's documentation for details on the options they allow you to use.

## Using ImageOptimize

Once ImageOptimize is set up and configured, there's nothing left to do. It just works.

If you have `devMode` on, ImageOptimize will log stats for images that it optimizes, e.g.:

```
2017-03-12 07:49:27 [192.168.10.1][1][-][info][nystudio107\ImageOptimize\services\Optimize::handleGenerateTransformEvent] zappa.png -> Original: 129.5K, Optimized: 100.8K -> Savings: 28.4%
```

## ImageOptimize Roadmap

Some things to do, and ideas for potential features:

* Add support for addition image optimization tools

Brought to you by [nystudio107](https://nystudio107.com)
