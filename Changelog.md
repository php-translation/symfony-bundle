# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 0.12.1

### Fixed

- Allow null values in CheckMissingCommand

## 0.12

### Added

- Service definition for form field title extractor
- Command translation:check-missing

## 0.11.3

### Added

- Command to delete empty translations
- Ability to send arbitrary options to a TransferableStorage with the download and sync command.

## 0.11.2

### Added

- Support Symfony Profiler dark mode

### Fixed

- Add missing AJAX requests headers ('X-Requested-With')

## 0.11.1

### Fixed

- Resolve environment placeholders.
- Correct incorrect service name `php_translator.fallback_translator.inner`.
- Extract command: Configure bundle dir before fetching catalogues.
- Deal with null values when retrieving catalogues.

## 0.11.0

## Added

- GUI updates on the Profiler page.

### Fixed

- Fixed "Unable to edit a missing translation from profiler" by updating SymfonyStorage

### Changed

- Modernized SymfonyProfilerController by stop using `$this->get()` and other bad practises.
- Modernized DownloadCommand
- `StorageService::download()` will return an array of `MessageCatalogue` after local copy is downloaded.
- DownloadCommand will automatically clear cache.

## 0.10.0

## Added

- Support for Symfony 5.
- PHP 7 type hints.
- Allow user to configure a custom dumper.

### Changed

- Use class names as service ids. Old service ids will still be available as aliases.
- Renamed all `*.yml` files to `*.yaml`.

### Removed

- Support for PHP < 7.2
- Support for Symfony < 3.4
- Support for php-translation/extractor 1.0 (including Twig 1 support)

## 0.9.1

### Fixed

- Fixed issue with translations falsely marked as obsolete

## 0.9.0

### Added

- Declared `Translation\Extractor\Visitor\Php\Symfony\FormTypeHelp` as a visitor
- Support for Symfony >= 4.2

### Changed

- Using Twig namespaces
- Twig block names to easier override web profiler js/css

### Removed

- Support for PHP < 7.1
- Support for Symfony < 3.4

## 0.8.2

### Added

- PHP 7.3 support
- Bing translator service

### Fixed

- Deprecated notice when using `symfony/config` >= 4.2
- Compatibility issues with Symfony 4.3
- Bad HTML generation when there is HTML in the translation
- Tweaked several Command descriptions
- Command translationdelete-obsolete did not run in non-interactive shells
- EditInPlaceResponseListener did not work properly with BinaryFileResponse

## 0.8.1

### Added

- Filter on empty messages in WebUI
- Use bootstrap icons

### Fixed

- Buhfix with logic operator in ImportService. See #258

## 0.8.0

### Added

- Bootstrap 4.1 CSS for web UI
- Support for stable `php-translation` dependencies

### Fixed

- Only add translation form `@desc` annotation to the default locale
- Ensure storage exists before using it in commands
- `FileDumper::setBackup()` deprecation notice
- Twig `strict_variabels` deprecation notice
- Avoid global bootstrap overrides - apply styles via new .configs CSS class

### Changed

- The `FallbackTranslator` will not try to translate to an empty locale. This could be considered as a BC break
 since now it will return the translation key instead of whatever the translator service returned (usually the translated string in original language).

## 0.7.0

### Added

- Support for `php-translation/common:0.3` and `php-translation/symfony-storage:0.5`
- Support for dumping to .po files.
- Support for `SourceLocation`'s context key `translation` which adds a default translation to the `Message`.
- Better respect blacklist and whitelist in `CatalogueFetcher`.

### Fixed

- Bug with config option `local_file_storage_options` not being used.
- Bug with edit-in-place and custom activator.

### Changed

- The "desc" filter will be used as default translation when extracting.

## 0.6.2

### Added

- User feedback when you use DeleteObsoleteCommand.
- Injecet depedencies in commands.
- Added argument for sync direction.

### Changed

- The service `php_translation.storage.default` is now public.
- The XliffDumper does not backup existing files before creating dump. This is the default behavior in
 Symfony 4.

### Fixed

- `Metadata::$notes` will not change when running `Metadata::getAllInCategory()`

## 0.6.1

### Fixed

-- Symfony 4 issues with the DownloadCommand.

## 0.6.0

### Added

- Support for Symfony 4
- Support for `desc` Twig filter
- Support for extract/update only for one bundle

### Fixed

- Dump configuration reference
- Improved statistics on WebUI

### Changed

- Commands are registered as services
- `EditInPlaceResponseListener::__construct` uses `UrlGeneratorInterface` instead of the concreate class `Router`
- The `php_translation.edit_in_place.activator` service is public

## 0.5.0

### Added

- Support for `desc` filter in Twig.

### Changed

- Twig extension `TranslationExtension` was renamed to `EditInPlaceExtension`

## 0.5.0

### Added

- Symfony 4 support
- New `--cache` option on the `translation:download` allowing to clear the cache automatically if the downloaded translations have changed.
- Support for Yandex translator

### Fixed

- Wrong paths in web profiler when using Twig2.x.
- Some JavaScript errors.

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
