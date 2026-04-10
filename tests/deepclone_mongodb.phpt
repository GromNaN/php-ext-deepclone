--TEST--
deepclone_to_array() / deepclone_from_array() round-trip MongoDB BSON types
--EXTENSIONS--
deepclone
mongodb
--FILE--
<?php

use MongoDB\BSON;

function roundtrip(mixed $value): bool
{
    $clone = deepclone_from_array(deepclone_to_array($value));
    return serialize($clone) === serialize($value);
}

// Stateless types: MinKey, MaxKey
var_dump(roundtrip(new BSON\MinKey()));
var_dump(roundtrip(new BSON\MaxKey()));

// Value types carrying state via __serialize / __unserialize
var_dump(roundtrip(new BSON\ObjectId('507f1f77bcf86cd799439011')));
var_dump(roundtrip(new BSON\Binary("\x00\x01\x02\x03", BSON\Binary::TYPE_GENERIC)));
var_dump(roundtrip(new BSON\Binary(random_bytes(16), BSON\Binary::TYPE_UUID)));
var_dump(roundtrip(new BSON\UTCDateTime(1000)));
var_dump(roundtrip(new BSON\Regex('^foo', 'i')));
var_dump(roundtrip(new BSON\Decimal128('3.14159265358979323846')));
var_dump(roundtrip(new BSON\Int64(PHP_INT_MAX)));
var_dump(roundtrip(new BSON\Timestamp(1, 1234567890)));
var_dump(roundtrip(new BSON\Javascript('function(x) { return x; }')));
var_dump(roundtrip(BSON\Document::fromPHP(['_id' => new BSON\ObjectId('507f1f77bcf86cd799439011'), 'n' => 1])));
var_dump(roundtrip(BSON\PackedArray::fromPHP([new BSON\ObjectId('507f1f77bcf86cd799439011'), 42])));

// Shared references: two properties pointing to the same BSON object
$oid = new BSON\ObjectId('507f1f77bcf86cd799439011');
$obj = new stdClass();
$obj->a = $oid;
$obj->b = $oid;
$clone = deepclone_from_array(deepclone_to_array($obj));
var_dump($clone->a == $clone->b);    // same value
var_dump($clone->a === $clone->b);   // object identity preserved in the graph
var_dump($clone->a !== $oid);        // but distinct from the original

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
