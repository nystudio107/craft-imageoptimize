# ImageOptimize Changelog

## 5.0.3 - UNRELEASED
### Fixed
* Don't add image variants if no variant creator for them exists ([#410](https://github.com/nystudio107/craft-imageoptimize/issues/410))
* Fix a visual issue with the sizing arrows for Optimized Image fields
* Don't apply background placeholder CSS to images that may be transparent like SVGs or GIFs ([#410](https://github.com/nystudio107/craft-imageoptimize/issues/410))

## 5.0.2 - 2024.06.19
### Fixed
* Fixed an issue where `srcsetMaxWidth()` could return incorrect results ([#407](https://github.com/nystudio107/craft-imageoptimize/issues/407))
* Fixed an issue where the data-uri for inline SVG styles were incorrect in some browsers because the spaces were not URL-encoded ([#408](https://github.com/nystudio107/craft-imageoptimize/issues/408))

## 5.0.1 - 2024.05.09
### Fixed
* Fixed an issue where field content was not propagated to other sites on multi-site installs, causing missing images
* Fixed an issue where the `.imgTag()` and `.pictureTag()` would output and invalid `style` attribute for lazy loaded images ([#400](https://github.com/nystudio107/craft-imageoptimize/issues/400))
* Fixed an issue where the Subpath wasn't being included for remote volumes like S3 & Google Cloud ([#403](https://github.com/nystudio107/craft-imageoptimize/issues/403))

## 5.0.0 - 2024.04.15
### Added
* Stable release for Craft CMS 5

## 5.0.0-beta.2 - 2024.04.04
### Added
* Added the ability to pass in a config array to `.imgTag()`, `.pictureTag()` and `.linkPreloadTag()`

### Changed
* Changed `.loading()` → `.loadingStrategy()`, `.artDirection()` → `addSourceFrom()`

## 5.0.0-beta.1 - 2024.04.02
### Added
* Initial Craft CMS 5 compatibility
* Add `.imgTag()` to the `OptimizedImage` model, which generates a complete `<img>` tag from the `OptimizedImage`
* Add `.pictureTag()` to the `OptimizedImage` model, which generates a complete `<picture>` tag from the `OptimizedImage`
* Add `.linkPreloadTag()` to the `OptimizedImage` model, which generates a complete `<link rel="preload">` tag from the `OptimizedImage`
* Add `craft.imageOptimize.renderLazySizesJs()` to render the LazySizes JavaScript for lazy loading images
* Add `craft.imageOptimize.renderLazySizesFallbackJs()` to render the LazySizes JavaScript with a support script that uses LazySizes as a fallback for browsers that don't support the `loading` property

### Changed
* Added **PDF** to the **Ignore Files** field settings ([#364](https://github.com/nystudio107/craft-imageoptimize/issues/364))
