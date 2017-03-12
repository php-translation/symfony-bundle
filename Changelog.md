# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release. 

## UNRELEASED

## 0.3.1

### Added

- More tests
- Using Extractor 1.1.1.

## 0.3.0

### Added

- Support for Twig2
- Allow to pass options ot the dumper
- Use stable version of extractor
- Clear cache when updating translations with EditInPlace

### Changed

- Updated namespace for EditInPlace feature

### Fixed

- Bug when DataCollector might be missing
- Better regex for EditInPlace attribute replacement

## 0.2.0

### Added

- `CatalogueWriter`
- Commands for downloading and syncing translations
- Added `Configuration` model to work with the `ConfigurationManager`.
- Implementation for all methods in the `StorageService`

### Changed

- `Translation\Bundle\Service\CatalogueFetcher` moved to `Translation\Bundle\Catalogue\CatalogueFetcher`
- Made most (if not all) classes final. 
- `CatalogueFetcher` requires a `Configuration` object.

### Removed

- Dead code in the `SymfonyProfilerController`
- `FileStorage` was moved to `php-translation/symfony-storage` 

### Fixed

- The bundle works without any configuration. 
- You may have an config named "default".

## 0.1.0

First release. 
