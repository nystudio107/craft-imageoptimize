# ImageOptimize Changelog

## 1.4.43 - 2018.10.29
### Changed
* Fix Thumbor focal point order
* Fix a regression that broke images in sub-folders for Imgix

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
* Moved to a modern webpack build config for the AdminCP
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
* Fixed an issue where certain settings could not be saved via the AdminCP
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
* Added a number of config settings to the AdminCP GUI

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
