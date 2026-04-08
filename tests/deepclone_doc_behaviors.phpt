--TEST--
deepclone_to_array() matches Symfony VarExporter documented behaviors
--EXTENSIONS--
deepclone
--FILE--
<?php

// 1. Missing class on deepclone_from_array — must throw an \Exception with code
//    5732 (DC_ERR_CLASS_NOT_FOUND) and the bare class name as the message, so
//    that a PHP-side wrapper (e.g. Symfony's NativeDeepClonerTrait) can wrap it
//    into its own ClassNotFoundException without parsing message text.
try {
    deepclone_from_array([
        'classes' => 'NonExistentClassXyz',
        'objectMeta' => 1,
        'prepared' => 0,
        'properties' => ['NonExistentClassXyz' => ['foo' => ['bar']]],
    ]);
    echo "1. FAIL: no exception\n";
} catch (\Exception $e) {
    echo '1. caught: ', $e::class, ' code=', $e->getCode(), ' msg=', $e->getMessage(), "\n";
}

// 2a. SplObjectStorage: identity preservation when an object appears both as a
//     storage key AND outside the storage.
$key = new stdClass();
$key->id = 'shared-key';
$storage = new SplObjectStorage();
$storage[$key] = 'value';
$graph = new stdClass();
$graph->storage = $storage;
$graph->keyOutside = $key;

$clone = deepclone_from_array(deepclone_to_array($graph));
$clonedKey = null;
foreach ($clone->storage as $k) {
    $clonedKey = $k;
}
echo '2a. SplObjectStorage shared key: ', ($clone->keyOutside === $clonedKey) ? 'OK' : 'FAIL', "\n";

// 2b. SplObjectStorage: identity preservation for stored values
$value = new stdClass();
$value->id = 'shared-value';
$key2 = new stdClass();
$storage2 = new SplObjectStorage();
$storage2[$key2] = $value;
$graph2 = new stdClass();
$graph2->storage = $storage2;
$graph2->valueOutside = $value;

$clone2 = deepclone_from_array(deepclone_to_array($graph2));
$clonedK = null;
foreach ($clone2->storage as $k) {
    $clonedK = $k;
}
echo '2b. SplObjectStorage shared value: ', ($clone2->valueOutside === $clone2->storage[$clonedK]) ? 'OK' : 'FAIL', "\n";

// 2c. ArrayObject: identity preservation for items inside
$shared = new stdClass();
$shared->id = 'shared';
$ao = new ArrayObject(['inside' => $shared]);
$graph3 = new stdClass();
$graph3->ao = $ao;
$graph3->outside = $shared;

$clone3 = deepclone_from_array(deepclone_to_array($graph3));
echo '2c. ArrayObject shared item: ', ($clone3->outside === $clone3->ao['inside']) ? 'OK' : 'FAIL', "\n";

// 2d. ArrayIterator: identity preservation for items inside
$shared2 = new stdClass();
$shared2->id = 'shared';
$ai = new ArrayIterator(['inside' => $shared2]);
$graph4 = new stdClass();
$graph4->ai = $ai;
$graph4->outside = $shared2;

$clone4 = deepclone_from_array(deepclone_to_array($graph4));
echo '2d. ArrayIterator shared item: ', ($clone4->outside === $clone4->ai['inside']) ? 'OK' : 'FAIL', "\n";

// 3. Reflection / IteratorIterator / RecursiveIteratorIterator / anonymous classes must
//    throw at serialize time
$nonInstantiable = [
    'ReflectionClass'           => new ReflectionClass(stdClass::class),
    'ReflectionMethod'          => new ReflectionMethod(ArrayObject::class, '__construct'),
    'ReflectionProperty'        => new ReflectionProperty(Error::class, 'message'),
    'IteratorIterator'          => new IteratorIterator(new ArrayIterator([1, 2])),
    'RecursiveIteratorIterator' => new RecursiveIteratorIterator(new RecursiveArrayIterator([[1]])),
    'anonymous class'           => new class { public int $x = 1; },
];

foreach ($nonInstantiable as $label => $value) {
    try {
        deepclone_to_array($value);
        echo "3. $label: FAIL (no exception)\n";
    } catch (\Exception $e) {
        // Must be \Exception (not \Error) with code 5731 (DC_ERR_NOT_INSTANTIABLE).
        $ok = $e::class === 'Exception' && 5731 === $e->getCode();
        echo "3. $label: ", $ok ? 'OK' : 'FAIL', "\n";
    }
}

echo "Done\n";
?>
--EXPECT--
1. caught: Exception code=5732 msg=NonExistentClassXyz
2a. SplObjectStorage shared key: OK
2b. SplObjectStorage shared value: OK
2c. ArrayObject shared item: OK
2d. ArrayIterator shared item: OK
3. ReflectionClass: OK
3. ReflectionMethod: OK
3. ReflectionProperty: OK
3. IteratorIterator: OK
3. RecursiveIteratorIterator: OK
3. anonymous class: OK
Done
