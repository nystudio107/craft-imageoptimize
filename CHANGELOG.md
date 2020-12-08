# ImageOptimize Changelog

## 1.6.20 - 2020.12.08
### Changed
* Lowercase written instances of "Imgix"
* Added additional logging for image placeholder and color extraction
* Moved the CSS/JS buildchain over to webpack 5
* Updated to latest npm deps

## 1.6.19 - 2020.10.07
### Fixed
* Fixed improperly generated `webp` URL for Imgix

## 1.6.18 - 2020.09.28
### Changed
* If an Optimized Images field is edited, only re-save the transforms for that specific field
* Allow an optional `--field` to be passed into the `php craft image-optimize/optimize/create` console command to only create image variants associated with a specific Optimized Images field
* Allow an optional `--force` flag to be passed into the `php craft image-optimize/optimize/create` console command for force image variant creation

## 1.6.17 - 2020.09.18
### Changed
* Sped up image optimization on multi-site setups by not spawning a new optimizing images job if the element is merely propagating to other sites

### Fixed
* Fixes issue when using `asset.getUrl({ })` with Imgix, where `format` would be coerced to the image's extension format, when it should have been left as `null`.

## 1.6.16 - 2020.08.31
### Changed
* Cleaned up the field styling to make it look correct on Craft CMS > 3.5.0
* Default to `false` if `$variant['useAspectRatio']` is somehow null

## 1.6.15 - 2020.08.25
### Added
* Added support for Redis queue jobs
* If an Optimized Images field isn't set to create variants for the sub-folder that an Asset is in, it'll display N/A

### Changed
* Cleaned up the field formatting for Craft CMS 3.5 CSS layout changes

### Fixed
* Fixed an issue with `.webp` URLs not working for URLs with ports in them
* Make missing webp variants display better
* Fixed an issue where images that were in sub-folders of sub-folders didn't have image variants created for them, if only specific volume sub-folders were chosen for optimization

## 1.6.14 - 2020.06.08
### Fixed
* Ensure image quality is set to `null` as “Auto” setting of image quality

## 1.6.13 - 2020.04.16
### Fixed
* Fixed Asset Bundle namespace case

## 1.6.12 - 2020.03.21
### Added
* Added the `create-asset` console command for regenerating the responsive image variants for a single Asset ID

### Changed
* Generated image URLs that have no file format extension are now properly displayed in the GUI (an issue mostly with serverless Sharp)

## 1.6.11 - 2020.02.12
### Changed
* Sanity check the inputs before normalizaing the file format

## 1.6.10 - 2020.02.11
### Changed
* Normalize the file format to lowercase before transforming

## 1.6.9 - 2020.02.06
### Changed
* Properly documented how the data in the `imageTransformTypeSettings` config is stored

### Fixed
* Fixed an issue where an SVG sent in to an image transform handler as a thumbnail request without specifying another format to convert to

## 1.6.8 - 2020.01.27
### Fixed
* Fixed an issue where an SVG sent in to image transform handler without specifying another format to convert to
* Fixed an issue where `MTIME` or other query string params could be listed as part of the image format

## 1.6.7 - 2020.01.08
### Added
* Added the ability to retrieve the dominant color palette as RGB values, making things like gradients easier to do
* ImageOptimize will now send back an Imgix or other transform method URL for `asset.getUrl()` with no transform applied

## 1.6.6 - 2019.11.19
### Added
* Added a Preferences item to control whether allow limiting the creation of Optimized Image Variants for images by sub-folders

## 1.6.5 - 2019.11.13
### Added
* Added the ability to choose the sub-folders in an asset volume in which Optimized Image Variants will be created

### Changed
* Parse volumes sub-folders as environment variables

## 1.6.4 - 2019.10.31
### Changed
* If an image is an animated `.gif` file, never change the file format

## 1.6.3 - 2019.10.24
### Changed
* Force update to `"nystudio107/craft-imageoptimize-imgix": "^1.1.1"`
* Fixed aspect ratio for CP thumbnail images
* Default `createPlaceholderSilhouettes` to `false` so people need to enable this setting if they want to use them (they can be on the larger side)

## 1.6.2 - 2019.09.25
### Changed
* Added support for native GraphQL in Craft 3.3

## 1.6.1 - 2019.08.13
### Changed
* Added more robust sanity checking if an invalid URL or path is being passed into `ImageTransform::appendExtension()`

## 1.6.0 - 2019.07.05
### Added
* Added support for Sharp via [AWS Serverless Image Handler](https://aws.amazon.com/solutions/serverless-image-handler/) as a Transform Method

### Changed
* Fixed an issue where ImageOptimize was not handling assets it didn't know how to transform property, resulting in generic thumbnails if you used the [Embedded Assets](https://github.com/spicywebau/craft-embedded-assets/) plugin
* Updated `ImageTransformInterface` to remove the deprecated `$params`

## 1.5.7 - 2019.06.13
### Added
* Added a `lightness` calculation based on a weighted average of the extracted dominant color palette to give you an idea of the image's overall lightness

### Changed
* Don't force the format for fallback images
* Rebuilt assets to fix npm vulnerabilities

## 1.5.6 - 2019.05.21
### Changed
* Fixed an issue where newly added Image Variant blocks had incorrect ids on the Retina checkboxes
* Updated build system

## 1.5.5 - 2019.04.22
### Changed
* Updated Twig namespacing to be compliant with deprecated class aliases in 2.7.x

## 1.5.4 - 2019.02.22
### Changed
* Fixed an issue where focal points weren't always respected for Imgix

## 1.5.3 - 2019.02.07
### Changed
* Fixed an issue where `.env` vars were not actually parsed

## 1.5.2 - 2019.02.07
### Changed
* If you're using Craft 3.1, ImageOptimize will use Craft [environmental variables](https://docs.craftcms.com/v3/config/environments.html#control-panel-settings) for secrets

## 1.5.1 - 2018.12.28
### Changed
* Refactored the Imgix and Thumbor Image Transforms out to external packages

## 1.5.0 - 2018.12.27
### Changed
* Refactored out the `ImageTransform` interface to use Yii2's DI to allow them to be stand-alone components
* Respond to `Assets::EVENT_GET_ASSET_THUMB_URL` for direct thumbnail generation from third party image transform services

## 1.4.45 - 2018.11.28
### Changed
* Call `App::maxPowerCaptain()` whenever a queue is manually run (both via web and console request)
* Minor Thumbor fixes

## 1.4.44 - 2018.11.18
### Changed
* Default format and quality to null so they can be omitted if you're generating transforms via Twig
* Fix an issue with Imgix with images that have no focal point set

## 1.4.43 - 2018.11.05
### Changed
* Fix Thumbor focal point order
* Fix a regression that broke images in sub-folders for Imgix
* Retooled the JavaScript build system to be more compatible with edge case server setups

## 1.4.42 - 2018.10.15
### Added
* Added the ability to have OptimizedImages fields ignore `SVG` and/or `GIF` files
* Added support [Thumbor](http://thumbor.org/) resizing service

## 1.4.41 - 2018.10.11
### Changed
* Fixed an issue where volumes with sub-folders such as Amazon S3 or Google Cloud didn't generate the correct URLs
* Fixed the build process so it will no longer look for `devServer` on installs
* Added a link to the **variant does not exist** to aid in diagnosing what is wrong 
* Changed the signature of `::getWebPUrl()` to send in all of the transform params

## 1.4.40 - 2018.10.05
### Added
* Add Super Table conditional for field

### Changed
* Updated build process

## 1.4.39 - 2018.09.25
### Changed
* Added a try/catch around ColorThief, to catch errors thrown due to empty/transparent images
* Fix a regression that could cause Optimized Images to not be generated correctly
* Modernized package.json and webpack build

## 1.4.38 - 2018.08.20
### Changed
* Fixed an incompatibility with the Spoon plugin by removing `matrix-field` class from field type settings
* Fixed an erroneous 2:2 aspect ratio in the default settings
* Moved to a modern webpack build config for the Control Panel
* Added install confetti

## 1.4.37 - 2018.08.09
### Changed
* Reverted an errant commit that removed a fix for propagating field data in multi-site environments
* Ensure that `.webp` variants have fully qualified absolute URLs

## 1.4.36 - 2018.08.06
### Added
* Added the ability to access each image variant's height

### Changed
* Update to the latest version of the Imgix PHP library

## 1.4.35 - 2018.07.12
### Changed
* Catch all exceptions that happen during `ResaveOptimizedImages()` job so that execution can continue

## 1.4.34 - 2018.07.12
### Changed
* Smarter appending of variant creator suffixes, to handle URLs that have query strings

## 1.4.33 - 2018.05.24
### Changed
* Handle JPEGs coming in as both `jpg` & `jpeg` for the detected file format
* Remove vestigal `empty()` checks

## 1.4.32 - 2018.05.09
### Added
* Improved CraftQL support by allowing parameter passing to `src` and `srcUrls`

## 1.4.31 - 2018.04.22
### Added
* Added CraftQL support

## 1.4.30 - 2018.04.09
### Added
* Added additional profiling information

## 1.4.29 - 2018.04.06
### Added
* Added profiling to the image variant creation

### Changed
* Vastly increase the speed with which the transforms are created when done via Twig templating code
* Increased the speed of the variant creation in general

## 1.4.28 - 2018.04.02
### Changed
* Switched over to using the Unsharp Mask (`usm`) filter for auto-sharpening Imgix images

## 1.4.27 - 2018.04.02
### Changed
* Removed unused composer dependencies

## 1.4.26 - 2018.03.31
### Changed
* Fixed a typo with `$generatePlacholders` that would cause saving images to fail

## 1.4.25 - 2018.03.30
### Changed
* Fixed a typo in the `config.php` settings, changed `generatePlacholders` -> `generatePlaceholders`
* Made the reduced quality of retina images if `lowerQualityRetinaImageVariants` is enabled less aggressive

## 1.4.24 - 2018.03.19
### Changed
* Fixed an issue with propagating field data in multi-site environments
* Scrutinizer code cleanup / refactoring
* If `.webp` support isn't configured, we don't display the images for the `webp` variants
* If images are being generated via template (not recommended) disable running the image optimizers and variant creators by default

### Added
* Added `craft.imageOptimize.serverSupportsWebP()` function to determine whether the server is capable of creating `.webp` variants
* Added validation rules for `imgixSecurityToken` so the value can be saved in the model

## 1.4.23 - 2018.03.03
### Changed
* The lengthy generation of placeholders should _really_ be off by default when doing them via Twig now
* Asset move operations now cause Optimized Image Variants to be re-saved via a non-blocking queue job

## 1.4.22 - 2018.03.02
### Changed
* Using Image Optimize via Twig should have less of a performance hit now, since all placeholder image/color palette generation is disabled
* Fixed deprecation errors from Craft CMS 3 RC13

## 1.4.21 - 2018.02.27
### Changed
* Show a warning if people try to add an OptimizedImages field in a Matrix block

## 1.4.20 - 2018.02.27
### Added
* Allow for CLI processing of specific Asset Volumes via `image-optimize/optimize/create volumehandle`
* Added `automaticallyResaveImageVariants` to `config.php` to allow disabling of the automatic re-saving of Image Variants for people who want to do it manually via the CLI
* Added a `.srcWebp()` method for OptimizedImages
* Added an optional `width` parameter that can be passed into `.src()` and `.srcWebp()`
* Added support for mostly deprecated `1x`, `2x`, etc. srcsets by passing an optional `true` parameter to `.srcsetWidth(XXX, true)` and `.srcsetWidthWebp(XXX, true)`

### Changed
* Fix more deprecation errors from Craft CMS 3 RC 12

## 1.4.19 - 2018.02.23
### Changed
* Fix deprecation errors from Craft CMS 3 RC 12 (which were causing slowdowns)

## 1.4.18 - 2018.02.19
### Added
* Fix a division by 0 error with some custom Craft transforms when using Imgix as a transform method

## 1.4.17 - 2018.02.16
### Added
* Added lightswitch controls to the OptimizedImages field that let you control what is displayed

### Changed
* Ensure we handle all of the potential `$transform` types passed into `EVENT_GET_ASSET_URL`
* Both `createColorPalette` and `createPlaceholderSilhouettes` are now off by default when using `craft.imageOptimize.createOptimizedImages()` via Twig, but can be overridden 

## 1.4.16 - 2018.02.15
### Added
* Added support for optional [security token](https://docs.imgix.com/setup/securing-images) used to sign image URLs from Imgix
* Added `image-optimize/optimize/create` and `image-optimize/optimize/clear` console commands for command line Optimized Image Variant creation

### Changed
* Display the non-webp version of an image when it exists, but the browser doesn't support displaying `.webp`
* Handles error conditions with `filesize` better

## 1.4.15 - 2018.02.14
### Changed
* Resave all asset volumes after the settings are changed (not just the Transform Method)
* Save the Optimized Image Variants via an async queue when possible
* Detect `gd` installation via `function_exists` instead of `extension_loaded`
* Handle protocol-relative siteUrls better

## 1.4.14 - 2018.02.09
### Changed
* Fixed an issue where certain settings could not be saved via the Control Panel
* Check to ensure that `GD` is installed before attempting to generate silhouette placeholders
* Show a warning on the Settings page if `GD` is not installed

## 1.4.13 - 2018.02.05
### Changed
* Removed *Edit Image* button, since Craft CMS 3 now includes one in the Asset preview

## 1.4.12 - 2018.02.01
### Changed
* Removed the `api-key` parameter from Imgix image transform URLs

## 1.4.11 - 2018.01.30
### Changed
* Renamed the composer package name to `craft-imageoptimize`
* Fixed a regression that broke protocol-relative siteUrls

## 1.4.10 - 2018.01.26
### Added
* Shows an error if an OptimizedImages field is added to anything other than an Asset's layout
* Added a warning if a config setting is being overridden by the `config.php`
* Added a number of config settings to the Control Panel GUI

### Changed
* `UrlHelper::urlWithProtocol` -> `UrlHelper::urlWithScheme` for Craft CMS 3 RC7 compatibility
* Fixed an issue where you could delete the last Image Variant in the field settings, even though the option was disabled
* Handle the display of Optimized Image Variant names better when coming from Imgix

## 1.4.9 - 2018.01.20
### Added
* Added an *Edit Image* button to the Optimized Images field view

### Changed
* Fixed `getWebPUrl()` for the Imgix image transform driver
* Fixed an issue where image uploads/changes would fail if you had an invalid or non-existent `activeImageVariantCreators` set
* Fixed an issue with protocol-relative siteUrls


## 1.4.8 - 2018.01.11
### Changed
* Fixed a flaw in the default aspect ratios
* Fixed Focal Point resaving issue
* Resaving Optimized Image Variants after making field changes should be quicker now

## 1.4.7 - 2017.12.29
### Changed
* Fixed a regression that would cause issues with PHP 7.0 (but not later versions of PHP)

## 1.4.6 - 2017.12.27
### Changed
* Handle the case of no Optimize Image Variants being created due to upscaling by just returning the original image
* Made the documentation on the OptimizedImages Field more clear, in that it needs to be added to an Asset Volume's layout

## 1.4.5 - 2017.12.22
### Added
* Added `.getMaxSrcsetWidth()` to work around issues with `<img srcset>` choosing sizes larger than are available
* Added a max size of 30K for generated silhouette SVGs, at which point it returns a simple SVG box

### Changed
* Better exception handling

## 1.4.4 - 2017.12.19
### Added
* Image variant creators now clean up after themselves when an Asset transform is deleted

### Changed
* ImageOptimize now _requires_ at least Craft CMS 3 RC3 or later

## 1.4.3 - 2017.12.19
### Added
* Image transforms that are scaled down >= 50% are now auto-sharpened (controllable via the `autoSharpenScaledImages` setting in `config.php`)
* It's now possible to set the default aspect ratios in the `config.php`

### Changed
* No longer possible delete the last Optimized Image variant
* At least one Optimized Image variant will always be present

## 1.4.2 - 2017.12.15
### Changed
* Fixed an issue where `.webp` variants would not be created

## 1.4.1 - 2017.12.13
### Changed
* Re-create the Responsive Image Variants when an Asset is replaced
* Handle cache busting for the Responsive Image Variants by appending an `?mtime=` string to the URL
* Better display of image variants when no variants are created
* No longer throws errors if you don't have the an image variant creator installed
* Wrap URLs and data returned to templates with `Template::raw()` so they won't be HTML entity encoded by default

## 1.4.0 - 2017.12.12
### Added
* Added `craft.imageOptimize.createOptimizedImages` to allow for the dynamic creation of Optimized Image variants
* Added `craft.imageOptimize.placeholderBox` to create a transparent SVG placeholder box of an arbitrary size and color
* Automatically reduce the quality of retina images (controllable via the `lowerQualityRetinaImageVariants` setting in `config.php`)
* Don't create Optimized Image Variants if it means the original image would be up-scaled (controllable via the `allowUpScaledImageVariants` setting in `config.php`)
* Purge URLs for assets that are deleted from services like Imgix

### Changed
* Fixed an issue where deleted Optimized Image Variant blocks would cause an error once saved
* Changed the default `optipng` compression to `-o3` so the compression time is more reasonable
* Updated README.md to reflect the new features

## 1.3.6 - 2017.12.10
### Changed
* Fixed an error with Imgix transforms

## 1.3.5 - 2017.12.06
### Changed
* Switched from `file_exists()` to `is_file()` for detecting image processor and variant commands
* Fixed an issue with Optimized Image Variant generation on certain setups
* The OptimizedImages field now displays protocol-relative images properly
* Added better debug logging
* Updated to require craftcms/cms `^3.0.0-RC1`

## 1.3.4 - 2017.12.03
### Changed
* Fixed an issue where selecting image variants would result in the wrong aspect ratio
* Changed the default image variant widths

## 1.3.3 - 2017.11.30
### Added
* Auto-purge remote Imgix image URLs when assets are changed

### Changed
* Fixed an issue with newly uploaded assets that have an OptimizedImages field in their Volume Layout

## 1.3.2 - 2017.11.14
### Added
* Added media query srcset sizes for precise control over the output sizes

## 1.3.1 - 2017.11.13
### Added
* Added the display of the dominant color palette and the lazy load placeholder images to the Field
* Optimize the placeholder images regardless of user settings
* Fixed an issue where newly uploaded assets would not resave on the AJAX request
* Added portrait & landscape responsive image variant presets

## 1.3.0 - 2017.11.10
### Changed
* Added support for globally replacing native Craft transforms with a service like Imgix, with zero template changes
* Added a Welcome page after installing ImageOptimize
* Performance improvements when generating the placeholders

## 1.2.10 - 2017.11.04
### Changed
* Fixed broken OptimizedImages Field settings
* Fixed `optipng` path in `config.php`
* Implemented an abstract `ImageTransform` class & interface

## 1.2.9 - 2017.11.03
### Added
* Added support for using [Imgix](https://www.imgix.com/) to create the responsive image transforms

### Changed
* Switched over to using `ResaveElements` queue to handle newly uploaded Assets
* Implemented optimized SVG data URIs, which can shave 15% off of the size of the SVG size

## 1.2.8 - 2017.10.19
### Changed
* More paranoid sanity checking of the `activeImageProcessors` and `activeImageVariantCreators` `config.php` settings

### Added
* Added the ability to control when image variants are created via `generateTransformsBeforePageLoad` `config.php` setting
* Added a `createColorPalette` in `config.php` to control dominant color palette creation
* Added a `createPlaceholderSilhouettes` in `config.php` to control silhouette placeholder image creation

## 1.2.7 - 2017.10.18
### Added
* Added the ability to make inline SVG silhouette images as placeholder images for lazing loading

## 1.2.6 - 2017.10.16
### Changed
* Moved the default variants to the `config.php` and Settings model, so they can be overridden

### Added
* Added the variant setting **Enforce Aspect Ratio** to allow for images with no fixed aspect ratio

## 1.2.5 - 2017.10.11
### Added
* Added the ability to automatically set the placeholder color to the image's dominant color
* Added the ability to extract the 5 dominant colors from an image

## 1.2.4 - 2017.10.10
### Added
* Added automatic generation of Instagram-style low resolution placeholder images for lazing loading

## 1.2.3 - 2017.09.16
### Added
* Added support for 2x and 3x retina images
* Added a `.src()` method to get the default responsive variant

### Changed
* Updated the README.md to be more descriptive

## 1.2.2 - 2017.09.11
### Changed
* Responsive Image Variants now default to `jpg` as a file format, for client-proofing purposes
* Fixed an issue where re-arranging to adding/deleting Responsive Image Variants could cause issues
* Fixed an issue with non-manipulatable images like `PDF`

## 1.2.1 - 2017.09.10
### Changed
* Fixed an issue that could leave stale image variants around
* Ensure that the optimized image variants are re-created if the image is edited
* Added logging to show the savings for image variants
* Fixed the way the Settings page is rendered
* Updated `README.md`

## 1.2.0 - 2017.09.08
### Added
* Added `OptimzedImages` Field
* Updated `README.md`

## 1.1.0 - 2017.08.07
### Added
* Added support for automatic `webp` image variant creation

## 1.0.10 - 2017.08.06
### Changed
* Fixed support for the `mozjpeg` image processor

## 1.0.9 - 2017.07.15
### Changed
* Craft 3 beta 20 compatibility

## 1.0.8 - 2017.04.20
### Changed
* Fixed the packagist.org package name to coincide with the plugin renaming
* Added debug `trace` logging in the event handler
* Updated `README.md`

## 1.0.7 - 2017.04.10
### Changed
* Renamed the plugin from `ImageOptim` to `ImageOptimize`
* Added `.webp` support

## 1.0.6 - 2017.04.08
### Changed
* Added `createSettingsModel()` to the main plugin class

## 1.0.5 - 2017.03.24
### Changed
* `hasSettings` -> `hasCpSettings` for Craft 3 beta 8 compatibility
* Added Craft 3 beta 8 compatible settings
* Modified config service calls for Craft 3 beta 8

## 1.0.4 - 2017.03.12
### Added
- Added code inspection typehinting for the plugin & services

### Changed
- Removed unused `resources/img`

## 1.0.3 - 2017.03.12
### Added
- Added support for `gif` images via `gifsicle`
- Added `craft/cms` as a composer dependency

### Changed
- Code refactor/cleanup

## 1.0.2 - 2017.03.11
### Added
- Added logging of the savings for each image optimization if `devMode` is on

## 1.0.1 - 2017.03.11
### Added
- Added `mikehaertl/php-shellcommand` as a dependency in `composer.json`

### Changed
- Updated `README.md` with more information

## 1.0.0 - 2017.03.11
### Added
- Initial release
