# ImageOptimize Changelog

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
