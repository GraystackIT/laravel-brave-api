# Changelog

All notable changes to `graystack/brave-search` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-04-07

### Added
- Initial release
- `BraveSearchClient` with `searchImages()` method supporting query, count, and option overrides
- `BraveImageDownloader` with `download()` and `detectMimeType()` methods
- `ImageResult` DTO with `url`, `thumbnailUrl`, `title`, and `sourceDomain` properties
- MIME type detection for JPEG, PNG, GIF, and WebP via magic bytes
- Configurable defaults: `safesearch`, `search_lang`, `country`, `count`
- Laravel service provider with auto-discovery
- Config validation: clear `RuntimeException` when `BRAVE_API_KEY` is missing
