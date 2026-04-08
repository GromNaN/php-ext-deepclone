--TEST--
deepclone_to_array() handles enums
--EXTENSIONS--
deepclone
--FILE--
<?php

enum Color {
    case Red;
    case Blue;
}

enum Suit: string {
    case Hearts = 'H';
    case Spades = 'S';
}

// ── Enum is static value ──
$d = deepclone_to_array(Color::Red);
var_dump(array_key_exists('value', $d));
var_dump($d['value'] === Color::Red);

// ── Backed enum is static value ──
$d = deepclone_to_array(Suit::Hearts);
var_dump($d['value'] === Suit::Hearts);

// ── Enum in array ──
$d = deepclone_to_array([Color::Red, Color::Blue]);
var_dump($d['value'] === [Color::Red, Color::Blue]);

// ── Enum as object property ──
$o = new stdClass();
$o->color = Color::Red;
$clone = deepclone_from_array(deepclone_to_array($o));
var_dump($clone->color === Color::Red);

// ── Enum hard reference ──
$x = Color::Red;
$v = [&$x, &$x];
$d = deepclone_to_array($v);
var_dump(isset($d['refs']));
var_dump(isset($d['refMasks']));
$clone = deepclone_from_array($d);
var_dump($clone[0] === Color::Red);
$clone[0] = Color::Blue;
var_dump($clone[1] === Color::Blue); // & preserved

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
Done
