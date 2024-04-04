# ImageOptimize Changelog

## 4.0.7 - 2024.04.04
### Added
* Added the ability to pass in a config array to `.imgTag()`, `.pictureTag()` and `.linkPreloadTag()`

### Changed
* Changed `.loading()` → `.loadingStrategy()`, `.artDirection()` → `addSourceFrom()`

## 4.0.6 - 2024.04.02
### Added
* Added a `--queue` option to the `image-optimize/optimize/create` console command if you want to defer the image generation to be run via queue job, rather than immediately via the console command
* Add `phpstan` and `ecs` code linting
* Add `code-analysis.yaml` GitHub action
* Add `.imgTag()` to the `OptimizedImage` model, which generates a complete `<img>` tag from the `OptimizedImage`
* Add `.pictureTag()` to the `OptimizedImage` model, which generates a complete `<picture>` tag from the `OptimizedImage`
* Add `.linkPreloadTag()` to the `OptimizedImage` model, which generates a complete `<link rel="preload">` tag from the `OptimizedImage`
* Add `craft.imageOptimize.renderLazySizesJs()` to render the LazySizes JavaScript for lazy loading images
* Add `craft.imageOptimize.renderLazySizesFallbackJs()` to render the LazySizes JavaScript with a support script that uses LazySizes as a fallback for browsers that don't support the `loading` property

### Changed
* Added **PDF** to the **Ignore Files** field settings ([#364](https://github.com/nystudio107/craft-imageoptimize/issues/364))
* Updated docs to use node 20 & a new sitemap plugin
* Switch over to Vite `^5.0.0` & Node `^20.0.0` for the buildchain
* PHPstan code cleanup
* ECS code cleanup

## 4.0.5 - 2023.05.19
### Changed
* Automate release generation via GitHub action
* Add versioning to the docs

### Fixed
* Side-step an issue which caused Craft 4.4.x to do unwanted image transform work even when using an external service to do the transforms ([#373](https://github.com/nystudio107/craft-imageoptimize/issues/373)) ([#13018](https://github.com/craftcms/cms/issues/13018))
* Remove the old Craft generated transform that's still sitting in the temp directory ([#380](https://github.com/nystudio107/craft-imageoptimize/pull/380))

## 4.0.4 - 2023.02.09
### Changed
* Use dynamic docker container name & port for the `buildchain`
* Update the `buildchain` to use Vite `^4.0.0`
* Updated docs to use VitePress `^1.0.0-alpha.29`
* Refactored the docs buildchain to use a dynamic docker container setup

## 4.0.3 - 2022.11.17
### Changed
* Fixed HMR in local dev with explicit alias that resolves to the actual directory

### Fixed
* Fixed an issue where the `craft image-optimize/optimize/create` CLI command did not properly optimize all images ([#350](https://github.com/nystudio107/craft-imageoptimize/issues/350))
* Fixed an issue which caused the Imgix `auto` to no longer work for auto format ([#357](https://github.com/nystudio107/craft-imageoptimize/issues/357))
* Fixed a JavaScript console error in field settings due to Garnish shuffling around how things work for menus
* Fixed an issue where `srcsetWidth()` wouldn't return the proper variant, due to strict comparison operator ([#327](https://github.com/nystudio107/craft-imageoptimize/issues/327))
* Fixed an issue where using a transform method other than Craft along with `asset.getUrl()` in your templates could throw an exception ([#363](https://github.com/nystudio107/craft-imageoptimize/issues/363))

## 4.0.2 - 2022.07.17
### Changed
* Add `allow-plugins` to `composer.json` to allow CI tests to work

### Fixed
* Fixed an issue where transforms don't get deleted on remote volumes if the format was set to `auto` ([#341](https://github.com/nystudio107/craft-imageoptimize/issues/341))
* Normalize for lowercase file extensions and normalize `jpeg` -> `jpg` everywhere

## 4.0.1 - 2022.07.08
### Fixed
* If there's no transform requested, return `null` so other plugins have a crack at it ([#349](https://github.com/nystudio107/craft-imageoptimize/issues/349))
### Fixed
* Fixed an issue where calling `generateUrl()` would throw an exception for the Imgix transform method ([#342](https://github.com/nystudio107/craft-imageoptimize/issues/342))

## 4.0.0 - 2022.06.28
### Changed
* If there's no transform requested, and we're not using some external service for image transforms, return `null` so other plugins have a crack at it

### Fixed
* Set the variant format to the current asset format if it is left empty, otherwise Craft recursively calls `getTransformUrl()` and hangs the queue ([#329](https://github.com/nystudio107/craft-imageoptimize/issues/329)) ([#343](https://github.com/nystudio107/craft-imageoptimize/issues/343))
* Fixed an issue where variants would not resave under certain circumstance due to a typing issue ([#335](https://github.com/nystudio107/craft-imageoptimize/issues/335))
* Make the properties in the `OptimizedImage` nullable to avoid null property values throwing an exception ([#345](https://github.com/nystudio107/craft-imageoptimize/issues/345))
* Fixed an issue that caused sub-folders to not appear in the OptimizedImages field settings ([#333](https://github.com/nystudio107/craft-imageoptimize/issues/333))
* Fixed an issue that could cause JavaScript errors for newly created OptimizedImages fields, and in slideouts ([#344](https://github.com/nystudio107/craft-imageoptimize/issues/344))

## 4.0.0-beta.3 - 2022.04.08
### Fixed
* Fix `ImageTransform::getAssetUri()` to properly handle S3 and other volume sub-directories

## 4.0.0-beta.2 - 2022.04.08
### Fixed

* Fixed an issue with properties not being initialized before being accessed, which would cause image uploads to fail ([#323](https://github.com/nystudio107/craft-imageoptimize/issues/323))

## 4.0.0-beta.1 - 2022.03.23

### Added

* Initial Craft CMS 4 compatibility
