# SimpleTester
Simple class tester for PHP

Create your own PHP library then test it using SimpleTester

### Testing one or more classes
```php
<?php

// sample class to test
class SampleClass{

	private $book = 'a novel';
	
	public $written = null;
	
	function read(){
		return 'reading '.$this->book;
	}
	
	function write($value = null){
		$this->written = $value;
		return 'writing '.$value;
	}
}


// simply require then test
require 'SimpleTester.php';

$tester = new SimpleTester;

$tester->subtest('Test for SampleClass', function($subtest) use($tester){
	$obj = new SampleClass;
	$subtest->test('must reading a novel', $obj->read() == 'reading a novel');
	$subtest->test('must writing "something"', $obj->write('something') == 'writing something');
	$subtest->test('"something" should be keep on property', $obj->written == 'something');
});

```
### Haw about result ?
Create your own result style
```php
echo 'Result:<br/>';

foreach ( $tester->getSubtests() as $subtest) {
	$subtestName = $subtest->getName();
	$tested = $subtest->countTests();
	$passed = $subtest->countPassed();
	$failed = $subtest->countFailed();
	
	echo "<b> $subtestName: $tested tests ($passed passed, $failed failed). </b><br/>";
	
	foreach ($subtest->getTests() as $testName => $result) {
		echo "* $testName is => ".($result ? 'Success' : 'Failed')."<br/>";
	}
}
```

### Then what ?
its done, so simple is it?


