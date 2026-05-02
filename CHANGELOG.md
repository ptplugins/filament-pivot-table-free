# Changelog

All notable changes to `ptplugins/filament-pivot-table-free` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-05-02

### Added
- Initial release.
- `<x-pivot-free::pivot-table />` Blade component.
- Sum aggregation across one or two row dimensions × one or two column dimensions.
- `:records` (Collection / array / iterable) and `:query` (Eloquent Builder) data sources — exactly one required.
- Hierarchical headers via `rowspan` / `colspan` when 2 dimensions are used.
- Grand Total row.
- Inline-styled, light theme — works without a Tailwind build.
- Auto-discovers across **Filament 3, 4, and 5** from a single codebase (component-only, no Page wrapper).
- Throws `OutOfBoundsException` with an upgrade URL when more than 2 dimensions are passed on either axis.
- Preserves caller-defined ordering — use `->orderBy()` on the Eloquent query to control row / column order.
