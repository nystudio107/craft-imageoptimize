# ImageOptimize Changelog

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
