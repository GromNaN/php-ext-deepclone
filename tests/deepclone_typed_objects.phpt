--TEST--
deepclone_to_array() handles typed objects with visibility and inheritance
--EXTENSIONS--
deepclone
--FILE--
<?php

class ParentClass {
    public string $pub = 'pub_default';
    protected int $prot = 0;
    private string $priv = 'parent_priv';

    public function setProt(int $v): void { $this->prot = $v; }
    public function setPriv(string $v): void { $this->priv = $v; }
}

class ChildClass extends ParentClass {
    private string $childPriv = 'child_default';
    public function setChildPriv(string $v): void { $this->childPriv = $v; }
}

// ── Parent with non-default values ──
$o = new ParentClass();
$o->pub = 'changed';
$o->setProt(42);
$o->setPriv('secret');
$clone = deepclone_from_array(deepclone_to_array($o));
var_dump($clone instanceof ParentClass);
var_dump($clone->pub === 'changed');
// Protected/private not directly accessible but clone should work
var_dump($clone == $o);
var_dump($clone !== $o);

// ── Child class with inherited properties ──
$o = new ChildClass();
$o->pub = 'child_pub';
$o->setProt(99);
$o->setPriv('parent_secret');
$o->setChildPriv('child_secret');
$clone = deepclone_from_array(deepclone_to_array($o));
var_dump($clone instanceof ChildClass);
var_dump($clone->pub === 'child_pub');
var_dump($clone == $o);

// ── Default values are skipped ──
$o = new ParentClass();
$d = deepclone_to_array($o);
// All properties are at defaults, so properties should be empty or minimal
var_dump(empty($d['properties']) || $d['properties'] === []);

// ── Readonly properties ──
class ReadonlyObj {
    public function __construct(
        public readonly string $name,
        public readonly int $value,
    ) {}
}

$o = new ReadonlyObj('test', 42);
$clone = deepclone_from_array(deepclone_to_array($o));
var_dump($clone->name === 'test');
var_dump($clone->value === 42);

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
