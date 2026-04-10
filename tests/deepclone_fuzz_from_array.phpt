--TEST--
deepclone_from_array() randomized malformed-input fuzz test (no crashes)
--EXTENSIONS--
deepclone
--FILE--
<?php

/*
 * Feeds randomized (mostly malformed) payloads to deepclone_from_array()
 * and verifies it throws a clean exception instead of crashing.
 */

function fuzz_payload(int &$seed): array {
    $seed = (int) fmod($seed * 1103515245 + 12345, 2147483648);
    $type = $seed % 6;

    return match ($type) {
        // Missing required keys
        0 => [],
        1 => ['classes' => 'stdClass'],
        2 => ['classes' => 'stdClass', 'objectMeta' => 1],
        // Valid but with random prepared
        3 => [
            'classes' => 'stdClass',
            'objectMeta' => ($seed % 5),
            'prepared' => $seed % 3 === 0 ? $seed % 5 : [$seed % 10, 'str'],
        ],
        // Random mask/resolve shapes
        4 => [
            'classes' => 'stdClass',
            'objectMeta' => 1,
            'prepared' => 0,
            'mask' => [true, false, $seed % 3],
            'resolve' => ['stdClass' => ['x' => [0 => true]]],
        ],
        // Deeply nested but valid-ish
        5 => [
            'classes' => 'stdClass',
            'objectMeta' => 2,
            'prepared' => 0,
            'properties' => [
                'stdClass' => [
                    'next' => [0 => 1, 1 => 0],
                ],
            ],
            'resolve' => [
                'stdClass' => [
                    'next' => [0 => true, 1 => true],
                ],
            ],
        ],
    };
}

$crashes = 0;
$iterations = 200;

for ($i = 0; $i < $iterations; $i++) {
    $seed = $i * 6271 + 17;
    $payload = fuzz_payload($seed);
    try {
        $result = deepclone_from_array($payload);
    } catch (\ValueError|\DeepClone\ClassNotFoundException|\DeepClone\NotInstantiableException $e) {
        // Expected — clean exception
        continue;
    } catch (\Throwable $e) {
        // Unexpected exception type
        echo "UNEXPECTED at seed=$i: " . get_class($e) . ": " . $e->getMessage() . "\n";
        $crashes++;
        if ($crashes > 5) break;
    }
}

echo $crashes === 0 ? "ok ($iterations iterations)\n" : "FAILED ($crashes crashes)\n";
?>
--EXPECT--
ok (200 iterations)
