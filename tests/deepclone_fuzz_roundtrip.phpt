--TEST--
deepclone_to_array() / deepclone_from_array() randomized round-trip fuzz test
--EXTENSIONS--
deepclone
--FILE--
<?php

/*
 * Generates random object graphs with a seeded PRNG and verifies that
 * serialize(original) === serialize(deepclone_from_array(deepclone_to_array(original)))
 * for each one. Catches crashes, assertion failures, and semantic mismatches.
 */

function fuzz_value(int &$seed, int $depth = 0): mixed {
    $seed = ($seed * 1103515245 + 12345) & 0x7FFFFFFF;
    $type = $seed % 8;

    if ($depth > 6) {
        // Leaf: scalar only
        return match ($seed % 4) {
            0 => $seed % 1000,
            1 => 'str_' . ($seed % 100),
            2 => ($seed % 2000) / 7.0,
            3 => (bool)($seed % 2),
        };
    }

    return match ($type) {
        0 => null,
        1 => $seed % 10000,
        2 => 'fuzz_' . ($seed % 500),
        3 => ($seed % 2000) / 3.0,
        4 => (bool)($seed % 2),
        5 => fuzz_array($seed, $depth),
        6 => fuzz_object($seed, $depth),
        7 => fuzz_object_graph($seed, $depth),
    };
}

function fuzz_array(int &$seed, int $depth): array {
    $seed = ($seed * 1103515245 + 12345) & 0x7FFFFFFF;
    $n = $seed % 8;
    $arr = [];
    for ($i = 0; $i < $n; $i++) {
        $arr[] = fuzz_value($seed, $depth + 1);
    }
    return $arr;
}

function fuzz_object(int &$seed, int $depth): stdClass {
    $seed = ($seed * 1103515245 + 12345) & 0x7FFFFFFF;
    $n = ($seed % 5) + 1;
    $o = new stdClass();
    for ($i = 0; $i < $n; $i++) {
        $prop = 'p' . $i;
        $o->$prop = fuzz_value($seed, $depth + 1);
    }
    return $o;
}

function fuzz_object_graph(int &$seed, int $depth): mixed {
    $seed = ($seed * 1103515245 + 12345) & 0x7FFFFFFF;
    $n = ($seed % 4) + 2;
    $objects = [];
    for ($i = 0; $i < $n; $i++) {
        $objects[$i] = new stdClass();
        $objects[$i]->id = $i;
    }
    // Random links
    for ($i = 0; $i < $n; $i++) {
        $seed = ($seed * 1103515245 + 12345) & 0x7FFFFFFF;
        $target = $seed % $n;
        $objects[$i]->link = $objects[$target];
        $objects[$i]->val = fuzz_value($seed, $depth + 1);
    }
    return $objects[0];
}

$errors = 0;
$iterations = 500;

for ($i = 0; $i < $iterations; $i++) {
    $seed = $i * 7919 + 42;
    $value = fuzz_value($seed);

    try {
        $arr = deepclone_to_array($value);
        $clone = deepclone_from_array($arr);
        $orig_ser = serialize($value);
        $clone_ser = serialize($clone);
        if ($orig_ser !== $clone_ser) {
            echo "MISMATCH at seed=$i\n";
            $errors++;
            if ($errors > 5) break;
        }
    } catch (\Throwable $e) {
        // NotInstantiableException is expected for some random inputs
        if (!$e instanceof \DeepClone\NotInstantiableException) {
            echo "ERROR at seed=$i: " . get_class($e) . ": " . $e->getMessage() . "\n";
            $errors++;
            if ($errors > 5) break;
        }
    }
}

echo $errors === 0 ? "ok ($iterations iterations)\n" : "FAILED ($errors errors)\n";
?>
--EXPECT--
ok (500 iterations)
