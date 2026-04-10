# deepclone

[![CI](https://github.com/symfony/php-ext-deepclone/actions/workflows/test.yml/badge.svg)](https://github.com/symfony/php-ext-deepclone/actions/workflows/test.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A PHP extension that deep-clones any serializable PHP value while preserving
copy-on-write for strings and arrays — resulting in lower memory usage and
better performance than `unserialize(serialize())`.

It works by converting the value graph to a pure-array representation (only
scalars and nested arrays, no objects) and back. This array form is the wire
format used by Symfony's
[`VarExporter\DeepCloner`](https://symfony.com/doc/current/components/var_exporter.html),
making the extension a transparent drop-in accelerator.

## Use cases

**Repeated cloning of a prototype.** Calling `unserialize(serialize())` in a
loop allocates fresh copies of every string and array, blowing up memory.
This extension preserves PHP's copy-on-write: strings and scalar arrays are
shared between clones until they are actually modified.

```php
$payload = deepclone_to_array($prototype);
for ($i = 0; $i < 1000; $i++) {
    $clone = deepclone_from_array($payload);  // fast, COW-friendly
}
```

**OPcache-friendly cache format.** The pure-array payload is suitable for
`var_export()`. When cached in a `.php` file, OPcache maps it into shared
memory — making the "unserialize" step essentially free:

```php
// Write:
file_put_contents('cache.php', '<?php return ' . var_export(deepclone_to_array($graph), true) . ';');

// Read (OPcache serves this from SHM):
$clone = deepclone_from_array(require 'cache.php');
```

**Serialization to any format.** The array form can be passed to
`json_encode()`, MessagePack, igbinary, APCu, or any transport that handles
plain PHP arrays — without losing object identity, cycles, references, or
private property state.

```php
$payload = deepclone_to_array($graph);
$json = json_encode($payload);   // safe — no objects in the array
// ... send over the wire, store in a DB, etc.
$clone = deepclone_from_array(json_decode($json, true));
```

## API

```php
function deepclone_to_array(mixed $value, ?array $allowedClasses = null): array;
function deepclone_from_array(array $data, ?array $allowedClasses = null): mixed;
```

`$allowedClasses` restricts which classes may be serialized or deserialized
(`null` = allow all, `[]` = allow none). Case-insensitive, matching
`unserialize()`'s `allowed_classes` option. Closures require `"Closure"` in
the list.

## What it preserves

- Object identity (shared references stay shared)
- PHP `&` hard references
- Cycles in the object graph
- Private/protected properties across inheritance
- `__serialize` / `__unserialize` / `__sleep` / `__wakeup` semantics
- Named closures (first-class callables like `strlen(...)`)
- Enum values
- Copy-on-write for strings and scalar arrays

## Error handling

| Exception                            | Thrown by             | When                                                     |
| ------------------------------------ | --------------------- | -------------------------------------------------------- |
| `DeepClone\NotInstantiableException` | `deepclone_to_array`  | Resource, anonymous class, `Reflection*`, internal class without serialization support |
| `DeepClone\ClassNotFoundException`   | `deepclone_from_array`| Payload references a class that doesn't exist            |
| `ValueError`                         | both                  | Malformed input, or class not in `$allowedClasses`       |

Both exception classes extend `\InvalidArgumentException`.

## Requirements

- PHP 8.2+ (NTS or ZTS, 64-bit and 32-bit)

## Installation

### With PIE (recommended)

```bash
pie install symfony/deepclone
```

Then enable in `php.ini`:

```ini
extension=deepclone
```

### Manual build

```bash
git clone https://github.com/symfony/php-ext-deepclone.git
cd php-ext-deepclone
phpize && ./configure --enable-deepclone && make && make test
sudo make install
```

## With Symfony

`symfony/var-exporter` and `symfony/polyfill-deepclone` provide the same
`deepclone_to_array()` / `deepclone_from_array()` functions in pure PHP.
When this extension is loaded it replaces the polyfill transparently —
no code change needed, just a 4–5× speedup.

## License

Released under the [MIT license](LICENSE).
