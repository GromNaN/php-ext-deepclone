--TEST--
deepclone_to_array() and deepclone_from_array() $allowedClasses parameter
--EXTENSIONS--
deepclone
--FILE--
<?php

// ── to_array: null allows all ──
$o = new stdClass(); $o->x = 1;
$d = deepclone_to_array($o, null);
var_dump(isset($d['classes']));

// ── to_array: specific class allowed ──
$d = deepclone_to_array($o, ['stdClass']);
var_dump($d['classes'] === 'stdClass');

// ── to_array: case insensitive ──
$d = deepclone_to_array($o, ['STDCLASS']);
var_dump($d['classes'] === 'stdClass');

// ── to_array: class rejected ──
try {
    deepclone_to_array($o, ['DateTime']);
} catch (ValueError $e) {
    var_dump(str_contains($e->getMessage(), '"stdClass" is not allowed'));
}

// ── to_array: empty list rejects all ──
try {
    deepclone_to_array($o, []);
} catch (ValueError $e) {
    var_dump(str_contains($e->getMessage(), 'is not allowed'));
}

// ── to_array: invalid entry type ──
try {
    deepclone_to_array($o, [123]);
} catch (ValueError $e) {
    var_dump(str_contains($e->getMessage(), 'class names'));
}

// ── to_array: invalid class name ──
try {
    deepclone_to_array($o, ['not a class']);
} catch (ValueError $e) {
    var_dump(str_contains($e->getMessage(), 'class names'));
}

// ── to_array: Closure allowed ──
$d = deepclone_to_array(strlen(...), ['Closure']);
var_dump(isset($d['mask']));

// ── to_array: Closure rejected ──
try {
    deepclone_to_array(strlen(...), []);
} catch (ValueError $e) {
    var_dump(str_contains($e->getMessage(), '"Closure" is not allowed'));
}

// ── to_array: static values bypass check ──
$d = deepclone_to_array(42, []);
var_dump($d === ['value' => 42]);

// ── from_array: allowed ──
$d = deepclone_to_array($o);
$c = deepclone_from_array($d, ['stdClass']);
var_dump($c->x === 1);

// ── from_array: case insensitive ──
$c = deepclone_from_array($d, ['STDCLASS']);
var_dump($c->x === 1);

// ── from_array: rejected ──
try {
    deepclone_from_array($d, []);
} catch (ValueError $e) {
    var_dump(str_contains($e->getMessage(), '"stdClass" is not allowed'));
}

// ── from_array: Closure in mask rejected ──
$d = deepclone_to_array(strlen(...));
try {
    deepclone_from_array($d, ['stdClass']);
} catch (ValueError $e) {
    var_dump(str_contains($e->getMessage(), '"Closure" is not allowed'));
}

// ── from_array: null allows all ──
$d = deepclone_to_array($o);
$c = deepclone_from_array($d, null);
var_dump($c->x === 1);

// ── from_array: static values bypass check ──
$c = deepclone_from_array(['value' => 42], []);
var_dump($c === 42);

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
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
Done
