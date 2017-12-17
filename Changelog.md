# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## Unreleased

## Added

- New `--cache` option on the `translation:download` allowing to clear the cache automatically if the downloaded translations have changed.

## 0.4.0

Major improvements on this version. Be aware that the default format to store translation has changed to XLIFF 2.0. If you
run the extract command you will automatically get updated files.

### Added

- More extractors from `php-translation/extractor`
- Show status after extract command
- Added status command
- Support for PHPUnit6
- Support for `php-translation/symfony-storage` 0.3.0
- Using dumper and loader from `php-translation/extractor`
- `CatalogueCounter` to show statistics about a catalogue
- Lots of more tests. Test coverage increased from 27% to 69%

### Changed

- `Importer` returns an `ImportResult` value object
- Improved internal management of metadata. Introduced a new `Metadata` model
- Renamed `MetadataAwareMerged` to `ReplaceOperation`, read the class doc for the updated syntax

### Fixed

- Issue with WebProfiler's `ClonerData` on Symfony 3.3

### Removed

- Removed `WebUIMessage` and `EditInPlaceMessage`. Use `Message` from `php-translation/common` instead
- Removed metadata related functions from `CatalogueManager`

### Changed

## 0.3.6

### Added

- Improve UI/UX in Profiler (loader during AJAX requests, select/deselect all messages)

### Changed

- Do not throw exception when SF Translation collector is not found.

## 0.3.5

### Fixed

- Fix incorrect EditInPlace activation on Twig `is_safe` comparison

## 0.3.4

### Fixed

- When using EditInPlace, we only mark twig filters (`trans` & `transchoice`) as "safe" when EditInPlace in active.

## 0.3.3

### Changed

- Add meta informations from the profiler to `Translation\Common\Model\Message`

### Fixed

- Make Dependency Injection work with Twig1

## 0.3.2

### Changed

- HTML fixes on profiler page

### Added

- Support for Symfony 3.3.x
- Option to not show "untranslatable" in WebUI
- Make sure we fail with an exception if SfTranslation is not enabled
- Improved testing

### Fixed

- Added translation cache dir on warmup

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
