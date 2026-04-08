--TEST--
deepclone_to_array() handles __serialize/__unserialize and __wakeup/__sleep
--EXTENSIONS--
deepclone
--FILE--
<?php

// ── __serialize + __unserialize ──
class SerializeObj {
    public function __construct(public string $name = '', public int $val = 0) {}
    public function __serialize(): array { return ['n' => $this->name, 'v' => $this->val]; }
    public function __unserialize(array $data): void { $this->name = $data['n']; $this->val = $data['v']; }
}

$o = new SerializeObj('test', 42);
$d = deepclone_to_array($o);
var_dump($d['objectMeta'][0][1] < 0); // negative wakeup = __unserialize
$clone = deepclone_from_array($d);
var_dump($clone instanceof SerializeObj);
var_dump($clone->name === 'test');
var_dump($clone->val === 42);

// ── __wakeup ──
class WakeupObj {
    public string $status = 'sleeping';
    public function __wakeup(): void { $this->status = 'awake'; }
}

$o = new WakeupObj();
$o->status = 'custom';
$d = deepclone_to_array($o);
var_dump($d['objectMeta'][0][1] > 0); // positive wakeup
$clone = deepclone_from_array($d);
var_dump($clone->status === 'awake'); // __wakeup was called

// ── __sleep ──
class SleepObj {
    public string $keep = '';
    public string $skip = '';
    public function __sleep(): array { return ['keep']; }
}

$o = new SleepObj();
$o->keep = 'yes';
$o->skip = 'no';
$d = deepclone_to_array($o);
var_dump(isset($d['properties']['stdClass']['keep']));
var_dump(!isset($d['properties']['stdClass']['skip']));

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
Done
