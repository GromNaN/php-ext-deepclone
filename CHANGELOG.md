# Changelog

All notable changes to this extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-04-08

### Added

- `deepclone_to_array(mixed $value): array` — walks a PHP value graph and
  produces a pure-array payload compatible with the wire format used by
  `Symfony\Component\VarExporter\DeepCloner::toArray()`.
- `deepclone_from_array(array $data): mixed` — reconstructs a value graph
  from a payload previously produced by `deepclone_to_array()`.
- Stable error codes on `\Exception` instances thrown by both functions:
  `5731` (DC_ERR_NOT_INSTANTIABLE) and `5732` (DC_ERR_CLASS_NOT_FOUND).
  The exception message is the bare class/type name.
- Compatible with PHP 8.2 through 8.5, NTS and ZTS builds.
