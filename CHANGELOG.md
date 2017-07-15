# ImageOptimize Changelog

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
