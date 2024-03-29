---
title: ImageOptimize plugin for Craft CMS
description: Documentation for the ImageOptimize plugin. The Transcoder plugin automatically creates & optimizes responsive image transforms, using either native Craft transforms or a service like imgix or Thumbor, with zero template changes
---
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nystudio107/craft-imageoptimize/badges/quality-score.png?b=v4)](https://scrutinizer-ci.com/g/nystudio107/craft-imageoptimize/?branch=v4) [![Code Coverage](https://scrutinizer-ci.com/g/nystudio107/craft-imageoptimize/badges/coverage.png?b=v4)](https://scrutinizer-ci.com/g/nystudio107/craft-imageoptimize/?branch=v4) [![Build Status](https://scrutinizer-ci.com/g/nystudio107/craft-imageoptimize/badges/build.png?b=v4)](https://scrutinizer-ci.com/g/nystudio107/craft-imageoptimize/build-status/v4) [![Code Intelligence Status](https://scrutinizer-ci.com/g/nystudio107/craft-imageoptimize/badges/code-intelligence.svg?b=v4)](https://scrutinizer-ci.com/code-intelligence)

# ImageOptimize plugin for Craft CMS

Automatically create & optimize responsive image transforms, using either native Craft transforms or a service like imgix or Thumbor, with zero template changes.

![Screenshot](./resources/img/plugin-banner.jpg)

**Note**: _The license fee for this plugin is $59.00 via the Craft Plugin Store._

## Requirements

This plugin requires Craft CMS 4.0.0 or later

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require nystudio107/craft-imageoptimize

3. Install the plugin via `./craft plugin/install image-optimize` via the CLI, or in the Control Panel, go to Settings → Plugins and click the “Install” button for Image Optimize.

You can also install ImageOptimize via the **Plugin Store** in the Craft Control Panel.

ImageOptimize works on Craft 4.x.

To use ImageOptimize with Cloudinary, install the [Cloudinary](https://github.com/timkelty/craft3-cloudinary) plugin that will make Cloudinary available as a file system for Craft CMS.

Brought to you by [nystudio107](https://nystudio107.com)
