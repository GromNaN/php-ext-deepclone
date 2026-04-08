--TEST--
deepclone_to_array() handles Error/Exception properties (no remap needed)
--EXTENSIONS--
deepclone
--FILE--
<?php

class FinalError extends Error {}

$e = new Error('test');
(new ReflectionProperty(Error::class, 'trace'))->setValue($e, ['file' => 't.php']);
(new ReflectionProperty(Error::class, 'line'))->setValue($e, 234);

$d = deepclone_to_array($e);
// All Error properties stay under Error scope (no TypeError remap)
var_dump(isset($d['properties']['Error']['message']));
var_dump(isset($d['properties']['Error']['line']));
var_dump(isset($d['properties']['Error']['trace']));
var_dump(!isset($d['properties']['TypeError']));

// Same for subclass
$e2 = new FinalError(false);
(new ReflectionProperty(Error::class, 'trace'))->setValue($e2, []);
(new ReflectionProperty(Error::class, 'line'))->setValue($e2, 123);
$d2 = deepclone_to_array($e2);
var_dump(isset($d2['properties']['Error']['trace']));
var_dump(isset($d2['properties']['Error']['line']));

// Round-trip via C from_array
$clone = deepclone_from_array($d);
var_dump($clone instanceof Error);
var_dump($clone->getMessage() === 'test');

echo "Done\n";
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
Done
