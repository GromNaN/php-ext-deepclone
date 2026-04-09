--TEST--
deepclone_to_array() handles deep chains that share a scope/name without UAF
--EXTENSIONS--
deepclone
--FILE--
<?php

/* Regression: dc_process_object()'s transpose loop used to cache a pointer
 * into properties[scope][name] across the recursive dc_copy_value() call.
 * When the recursion added more entries to the same hash, the bucket array
 * was reallocated and the cached pointer became a dangling reference into
 * freed memory. The crash surfaced as a use-after-free that was visible
 * starting at chain length ~14 (allocator dependent). */

function check_chain(int $n): void {
    $a = [];
    for ($i = 0; $i < $n; $i++) {
        $a[$i] = new stdClass();
    }
    for ($i = 0; $i < $n - 1; $i++) {
        $a[$i]->next = $a[$i + 1];
    }
    $arr = deepclone_to_array($a[0]);
    $clone = deepclone_from_array($arr);

    /* Walk the cloned chain and verify it has the right length. */
    $len = 0;
    $cur = $clone;
    while ($cur instanceof stdClass) {
        $len++;
        $cur = $cur->next ?? null;
    }
    var_dump($len === $n);
}

foreach ([1, 2, 8, 13, 14, 15, 16, 32, 64, 128, 1000] as $n) {
    check_chain($n);
}

/* Same shape but with a back-reference array on the head, to mirror the
 * original repro from the bug report. */
function check_chain_with_all(int $n): void {
    $a = [];
    for ($i = 0; $i < $n; $i++) {
        $a[$i] = new stdClass();
    }
    for ($i = 0; $i < $n - 1; $i++) {
        $a[$i]->next = $a[$i + 1];
    }
    $a[0]->all = $a;
    $arr = deepclone_to_array($a[0]);
    $clone = deepclone_from_array($arr);
    var_dump(\count($clone->all) === $n);
}

foreach ([14, 100, 500] as $n) {
    check_chain_with_all($n);
}

echo "ok\n";
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
ok
