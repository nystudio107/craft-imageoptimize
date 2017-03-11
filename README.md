# ImageOptim plugin for Craft CMS 3.x

Automatically optimize images after they've been transformed

## Installation

To install ImageOptim, follow these steps:

1. Install with Composer via `composer require nystudio107/craft3-imageoptim`
2. Install plugin in the Craft Control Panel under Settings > Plugins

ImageOptim works on Craft 3.x.

ImageOptim won't do anything on its own; you'll need to also install the image optimization tools of your choice. Here's how to install a few on Ubuntu 16.04:

* **jpegoptim** - `sudo apt-get install jpegoptim`
* **mozjpeg** - [Installing mozjpeg on Ubuntu 16.04 (Forge)](https://nystudio107.com/blog/installing-mozjpeg-on-ubuntu-16-04-forge)
* **optipng** - `sudo apt-get install optipng`
* **svgo** - `sudo npm install -g svgo`

## ImageOptim Overview

ImageOptim allows you to optimize the images created by Craft 3's Image Transforms by automatically running a variety of image optimization tools on them. As configured by default, all of these are _lossless_ image optimizations that remove metadata and otherwise optimize the images without change their appearance in any way.

It's important to create optimized images for frontend delivery, especially for mobile devices. If you want to learn more about it, read the [Creating Optimized Images in Craft CMS](https://nystudio107.com/blog/creating-optimized-images-in-craft-cms) article.

Once ImageOptim is installed, optimized versions of image transforms are created without you having to do anything. This makes it great for client-proofing websites.

## Configuring ImageOptim

The only configuration for ImageOptim is in the `config.php` file, which is a multi-environment friendly way to store the default settings.  Don't edit this file, instead copy it to `craft/config` as `imageoptim.php` and make your changes there.

The `activeProcessors` array lets you specify which of the image optimization tools to use for which file types.

The `imageProcessors` array specifies the path and options for each of the image optimization tools.

## Using ImageOptim

Once ImageOptim is set up and configured, there's nothing left to do. It just works.

## ImageOptim Roadmap

Some things to do, and ideas for potential features:

* Add support for addition image optimization tools

Brought to you by [nystudio107](https://nystudio107.com)
