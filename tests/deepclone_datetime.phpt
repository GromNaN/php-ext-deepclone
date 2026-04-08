--TEST--
deepclone_to_array() handles DateTime and related classes
--EXTENSIONS--
deepclone
--FILE--
<?php

// DateTime has __serialize/__unserialize
$dt = DateTime::createFromFormat('U', '0');
$d = deepclone_to_array($dt);
var_dump($d['objectMeta'][0][1] < 0); // __unserialize

$clone = deepclone_from_array($d);
var_dump($clone instanceof DateTime);
var_dump($clone->format('U') === '0');
var_dump($clone !== $dt);

// DateTimeImmutable
$dti = DateTimeImmutable::createFromFormat('U', '0');
$clone = deepclone_from_array(deepclone_to_array($dti));
var_dump($clone instanceof DateTimeImmutable);
var_dump($clone->format('U') === '0');

// DateTimeZone
$tz = new DateTimeZone('Europe/Paris');
$clone = deepclone_from_array(deepclone_to_array($tz));
var_dump($clone instanceof DateTimeZone);
var_dump($clone->getName() === 'Europe/Paris');

// DateInterval
$start = new DateTimeImmutable('2009-10-11');
$end = new DateTimeImmutable('2009-10-18');
$interval = $start->diff($end);
$clone = deepclone_from_array(deepclone_to_array($interval));
var_dump($clone instanceof DateInterval);
var_dump($clone->d === 7);

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
Done
