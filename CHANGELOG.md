# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.0.0] - 2026-10-26

### Added

- Session-backed global filters for Laravel Nova 5 dashboards, defined by
  subclassing `GlobalFilter` or fluently with `GlobalFilter::make()`.
- `DateInput`, `DateRangeInput`, `SelectInput`, `TextInput` and `BooleanInput`
  with validation of posted values.
- `DateRangeFilter` with a `DateRange` value object.
- `GlobalFilterCard` to render any registered filter on a dashboard and
  refresh the metrics in place, and `SectionHeader` to title a group of metrics.
- `RespondsToGlobalFilters` trait for Value, Trend, Partition and Progress
  metrics (`applyGlobalFilters`, `globalFilterValue`, per-metric options,
  scopes and cache-key support).
- `NovaGlobalFilters` facade (`register`, `filter`, `value`, `rawValue`,
  `summary`, `set`, `clear`).
- English and Dutch translations.

[Unreleased]: https://github.com/lucacermenati/nova-global-filters/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/lucacermenati/nova-global-filters/releases/tag/v1.0.0
