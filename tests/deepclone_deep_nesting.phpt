--TEST--
deepclone_to_array() handles deeply nested objects without crashing
--EXTENSIONS--
deepclone
--FILE--
<?php

$head = null;
for ($i = 599; $i >= 0; --$i) {
    $node = new stdClass();
    $node->i = $i;
    $node->next = $head;
    $head = $node;
}

$arr = deepclone_to_array($head);
echo "deep nesting ok\n";
echo $arr['classes'] . "\n"; // single class → plain string

// A second independent call must also work.
$arr2 = deepclone_to_array($head);
echo "second call ok\n";
?>
--EXPECT--
deep nesting ok
stdClass
second call ok
