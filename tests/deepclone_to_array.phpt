--TEST--
deepclone_to_array() produces correct array format
--EXTENSIONS--
deepclone
--FILE--
<?php

// ── Scalars ──
var_dump(deepclone_to_array(42) === ['value' => 42]);
var_dump(deepclone_to_array('hello') === ['value' => 'hello']);
var_dump(deepclone_to_array(true) === ['value' => true]);
var_dump(deepclone_to_array(false) === ['value' => false]);
var_dump(deepclone_to_array(null) === ['value' => null]);
var_dump(deepclone_to_array(3.14) === ['value' => 3.14]);
var_dump(deepclone_to_array([]) === ['value' => []]);

// ── Simple stdClass ──
$o = new stdClass();
$o->foo = 'bar';
$o->num = 42;
$d = deepclone_to_array($o);
var_dump($d['classes'] === 'stdClass');
var_dump($d['objectMeta'] === 1);
var_dump($d['prepared'] === 0);
var_dump($d['properties']['stdClass']['foo'] === [0 => 'bar']);
var_dump($d['properties']['stdClass']['num'] === [0 => 42]);

// ── Nested objects ──
// resolve[scope][name] is now a bit-packed string (one slot per id of an
// object that has the property, in iteration order). For "child" with a
// single object id 0 and a TRUE marker (obj_ref), the bitstring is one
// 0b01 slot = 1 byte = "\x01".
$o = new stdClass();
$o->child = new stdClass();
$o->child->val = 'inner';
$d = deepclone_to_array($o);
var_dump($d['objectMeta'] === 2);
var_dump($d['properties']['stdClass']['val'] === [1 => 'inner']);
var_dump($d['resolve']['stdClass']['child'] === "\x01");

// ── Shared object reference ──
$child = new stdClass();
$child->x = 1;
$o = new stdClass();
$o->a = $child;
$o->b = $child;
$d = deepclone_to_array($o);
var_dump($d['properties']['stdClass']['a'][0] === $d['properties']['stdClass']['b'][0]);

// ── Circular reference ──
// Both ref slots (id 0 and id 1) carry obj_ref markers — bitstring has two
// 0b01 slots = (01<<0) | (01<<2) = 0x05.
$a = new stdClass();
$b = new stdClass();
$a->ref = $b;
$b->ref = $a;
$d = deepclone_to_array($a);
var_dump($d['objectMeta'] === 2);
var_dump($d['resolve']['stdClass']['ref'] === "\x05");

// ── Hard references (scalar) ──
// Top-level mask: 1 slot for prepared (an array) → 0b10, then 2 sub-slots
// of 0b01 (hard-ref markers, since the values are negative longs):
//   byte 0 = (10<<0) | (01<<2) | (01<<4) = 0x16
$v = [1];
$v[] = &$v[0];
$d = deepclone_to_array($v);
var_dump($d['prepared'] === [-1, -1]);
var_dump($d['mask'] === "\x16");
var_dump(isset($d['refs'][1]));

// ── Hard references (recursive) ──
// prepared = [-1] → top mask: 0b10 + sub-slot 0b01 = (10) | (01<<2) = 0x06.
// The single ref's value is [-1] (its own array form); refMasks has one
// 0b10 slot + a sub-slot 0b01 = same byte 0x06.
$v = [];
$v[0] = &$v;
$d = deepclone_to_array($v);
var_dump($d['prepared'] === [-1]);
var_dump($d['mask'] === "\x06");
var_dump(isset($d['refs'][1]));
var_dump(isset($d['refMasks']));

// ── Unshared reference (becomes static) ──
$x = [123];
$d = deepclone_to_array([&$x]);
var_dump(array_key_exists('value', $d));
var_dump($d['value'] === [[123]]);

// ── Named closure (global function) ──
// Top-level mask: 1 slot for prepared (an array, the encoded callable)
// with the "named closure" marker = 0b01 = "\x01".
$d = deepclone_to_array(strlen(...));
var_dump($d['prepared'] === [null, 'strlen']);
var_dump($d['mask'] === "\x01");

// ── Large string (COW) ──
$big = str_repeat('x', 10000);
$o = new stdClass();
$o->data = $big;
$d = deepclone_to_array($o);
var_dump($d['properties']['stdClass']['data'][0] === $big);

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
