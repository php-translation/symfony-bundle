# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release. 

## UNRELEASED

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
