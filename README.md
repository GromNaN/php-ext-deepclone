# deepclone

[![CI](https://github.com/symfony/php-ext-deepclone/actions/workflows/test.yml/badge.svg)](https://github.com/symfony/php-ext-deepclone/actions/workflows/test.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**Export any serializable PHP value as a pure array — and rebuild it back into
the original object graph.** Also doubles as a native accelerator for Symfony's
[`VarExporter\DeepCloner`](https://symfony.com/doc/current/components/var_exporter.html).

## What it does

The extension exposes two functions:

```php
function deepclone_to_array(mixed $value): array;
function deepclone_from_array(array $data): mixed;
```

`deepclone_to_array()` walks any PHP value graph — objects, arrays, references,
closures, enums, internal types — and returns a payload that contains **only
scalars and nested arrays**: no objects, no resources, no surprises. Pass that
payload to anything that handles plain PHP arrays — `json_encode()`,
`var_export()`, APCu, igbinary, MessagePack, an HTTP body, a cache backend.

`deepclone_from_array()` does the reverse: feed it the payload and it rebuilds
the original graph, preserving object identity, internal references, private
properties, `__wakeup` / `__unserialize` semantics, and so on.

The wire format is the one used by Symfony's
`VarExporter\DeepCloner::toArray()` / `::fromArray()`. The two implementations
produce identical payloads, so you can produce on one side and consume on the
other.

## Why

- **A pure-array form for any value**, even ones that can't normally be JSON-encoded
  (objects with private state, cycles, references, closures over named methods, …).
  Once a graph is in array form it can travel through any serializer or cache
  layer that doesn't speak PHP objects.
- **Identity & reference preservation**: shared subgraphs stay shared, hard
  references stay live, cycles round-trip cleanly. Unlike `json_encode()` or
  `var_export()`, no information is lost on the way out.
- **4–5× faster** than the pure-PHP `DeepCloner` implementation on `toArray()`
  and end-to-end `deepClone()`.
- **On par with `unserialize(serialize($value))`**, while preserving
  copy-on-write for strings and scalar arrays — so memory usage stays low when
  the input is mostly immutable.

## Requirements

- PHP **8.2** or later (NTS or ZTS)
- 64-bit platform
- `ext-reflection` and `ext-spl` (always present in stock PHP builds)

## Installation

### With PIE (recommended)

[PIE](https://github.com/php/pie) is the modern installer for PHP extensions:

```bash
pie install symfony/deepclone
```

Then enable the extension in your `php.ini`:

```ini
extension=deepclone
```

### Manual build

```bash
git clone https://github.com/symfony/php-ext-deepclone.git
cd php-ext-deepclone
phpize
./configure --enable-deepclone
make
make test
sudo make install
```

Then enable in `php.ini` as above.

## Usage

### Standalone

```php
$graph = new MyComplexObject(/* ... */);

$payload = deepclone_to_array($graph);   // pure array
$copy    = deepclone_from_array($payload); // fresh deep copy
```

### Through Symfony's `DeepCloner`

If you have `symfony/var-exporter` installed, just load the extension — Symfony's
`DeepCloner` automatically picks it up at file-load time. No code change needed:

```php
use Symfony\Component\VarExporter\DeepCloner;

$copy = DeepCloner::deepClone($graph); // 4–5× faster with the extension loaded
```

## Error handling

Both functions throw `\Exception` (with a stable code) when the input cannot
be processed. The exception message is the bare class or type name; PHP-side
wrappers (e.g. Symfony's `NativeDeepClonerTrait`) translate them into
domain-specific exception types.

| Code | Constant                  | Meaning                                                          |
| ---- | ------------------------- | ---------------------------------------------------------------- |
| 5731 | `DC_ERR_NOT_INSTANTIABLE` | Resource, anonymous class, `Reflection*`, `*IteratorIterator`, … |
| 5732 | `DC_ERR_CLASS_NOT_FOUND`  | Payload references a class that no longer exists                 |

`deepclone_from_array()` additionally throws `\ValueError` on malformed input.

## Compatibility & stability

- **PHP versions**: 8.2, 8.3, 8.4, 8.5 — tested on each in CI.
- **Builds**: NTS and ZTS, on Linux, macOS, and Windows.
- **Wire format**: stable across patch releases. Any change to the wire format
  in a way that would break interoperability with the PHP `DeepCloner` will be
  a minor (`0.x`) bump pre-1.0 and a major bump post-1.0.

## Relationship with Symfony's `VarExporter`

This extension is a drop-in C accelerator for the PHP code at
[`Symfony\Component\VarExporter\DeepCloner`](https://github.com/symfony/symfony/tree/7.x/src/Symfony/Component/VarExporter).
It does **not** replace the component — Symfony's pure-PHP path remains the
reference implementation, the source of truth for the wire format, and the
fallback when the extension is not loaded. Loading the extension is purely
opt-in and transparent.

## License

Released under the [MIT license](LICENSE).
