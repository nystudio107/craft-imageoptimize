# ImageOptimize Changelog

## 5.0.0-beta.1 - UNRELEASED
### Added
* Initial Craft CMS 5 compatibility
* Add `.imgTag()` to the `OptimizedImage` model, which generates a complete `<img>` tag from the `OptimizedImage`
* Add `.pictureTag()` to the `OptimizedImage` model, which generates a complete `<picture>` tag from the `OptimizedImage`
* Add `.linkPreloadTag()` to the `OptimizedImage` model, which generates a complete `<link rel="preload">` tag from the `OptimizedImage`
* Add `craft.imageOptimize.renderLazySizesJs()` to render the LazySizes JavaScript for lazy loading images
* Add `craft.imageOptimize.renderLazySizesFallbackJs()` to render the LazySizes JavaScript with a support script that uses LazySizes as a fallback for browsers that don't support the `loading` property

