# Changelog

All notable changes to this extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- `deepclone_to_array()` now rejects internal classes that hold non-property
  state (custom `create_object` handler, no `__serialize` / `__unserialize` /
  `__sleep` / `__wakeup`, not `Serializable`) by throwing
  `DeepClone\NotInstantiableException`. Previously, such classes silently
  round-tripped to a default-state instance, losing their hidden state.
  Newly caught: `SplFileInfo`, `SplFileObject`, `PDO`, `ZipArchive`,
  `mysqli` and friends.
  Classes that declare a serialization API are unaffected (`ArrayObject`,
  `SplFixedArray`, `SplDoublyLinkedList`, `SplObjectStorage`, `DateTime*`,
  `DateInterval`, `DateTimeZone`, `DatePeriod`, …).

## [0.1.0] - 2026-04-08

### Added

- `deepclone_to_array(mixed $value): array` — walks a PHP value graph and
  produces a pure-array payload compatible with the wire format used by
  `Symfony\Component\VarExporter\DeepCloner::toArray()`.
- `deepclone_from_array(array $data): mixed` — reconstructs a value graph
  from a payload previously produced by `deepclone_to_array()`.
- Two typed exceptions under the `DeepClone\` namespace, both extending
  `\InvalidArgumentException`:
  - `DeepClone\NotInstantiableException` — thrown by `deepclone_to_array()`
    when the input contains a resource or a non-instantiable class
    (anonymous class, `Reflection*`, `*IteratorIterator`, …).
  - `DeepClone\ClassNotFoundException` — thrown by `deepclone_from_array()`
    when the payload references a class that no longer exists.
  In both cases the exception message is the bare class or type name.
- Compatible with PHP 8.2 through 8.5, NTS and ZTS builds, on x86_64 and
  i386 Linux, macOS, and Windows.
