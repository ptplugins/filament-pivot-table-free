# Changelog

All notable changes to `ptplugins/filament-pivot-table-free` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.1] - 2026-05-03

### Changed
- Updated `screenshot.png` (2560×1440) for the filamentphp.com listing — composite banner with plugin name and tagline overlay over the live pivot table render.

## [1.1.0] - 2026-05-02

### Added
- Tailwind CSS classes (light + **dark mode** via `dark:` variants) — replaces inline styles. Matches Filament's own admin panel styling out of the box.
- Translation support — labels (`grand_total`, `empty`, `powered_by`, `upgrade_cta`) load from `pivot-free::messages.*`. Publish vendor translations:
  ```bash
  php artisan vendor:publish --tag=pivot-free-translations
  ```
  English bundled. Override / add new locale by editing the published files at `lang/vendor/pivot-free/{locale}/messages.php`.

### Changed
- Component `emptyText` default changed from hardcoded English to `null` so the translation key applies. Pass `:emptyText="..."` to override per-instance.
- Outer wrapper, table, headers, cells now use Tailwind utility classes. Requires Tailwind in your build (you already have it if you use Filament). No config publish needed.

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
