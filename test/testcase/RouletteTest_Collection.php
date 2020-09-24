<?php 

use Roulette\Collection;

class RouletteTest_Collection extends RouletteUnittest_Model {

    public $name = 'Roulette\Collection';

    protected $skip = array('is','isNot');

    public function __construct(){
        parent::__construct();
    }

    function index() {
        $me = $this;
        $class = $this->name;

        /*static*/
        $me->test('create', method_exists($class, 'create'), function() use($me, $class)
        {
            $obj = Collection::create();
            $me->test('create, return a new Collection', is_a($obj, $class));
        });

        $me->test('isAssoc', method_exists($class, 'isAssoc'), function () use ($me, $class) 
        {
            $me->test('isAssoc, true assoc', $class::isAssoc(array( 0=>'0',  1=>'1', 'c'=>'2')) == true);
            $me->test('isAssoc, numeric array', $class::isAssoc(array('0', '1', '2')) === false);
            $me->test('isAssoc, string numeric array', $class::isAssoc(array('0'=>'0', '1'=>'1', '2'=>'2')) === false);
        });

        $me->test('enum', method_exists($class, 'enum'), function() use ($me, $class) 
        {
            $me->test('enum, valid array', $class::enum('1', array('1','2')) === '1');
            $me->test('enum, valid array strict mode', $class::enum(1, array('1','2'), null, true) === null);
            $me->test('enum, empty array without fallback', $class::enum('-1', array()) === null);
            $me->test('enum, empty array with fallback', $class::enum('-1', array(), '0') === '0');
            $me->test('enum, array with fallback', $class::enum('-1', array('0', '1', '2'), '0') === '0');
        });

        $me->test('set', method_exists($class, 'set'), function() use($me)
        {
            $obj = new Collection();
            $obj->set('one',1);
            $me->test('set, plain mode',
                $obj->get('one') === 1 
            );
            $obj->set('two');
            $me->test('set, incomplite params', 
                $obj->get('two') === null 
            );

            $obj->set(array(
                'three' => 3,
                'four' => 4
            ));
            $me->test('set, array set', 
                $obj->get('three') === 3 and $obj->get('four') === 4 
            );

            $obj->set(array(
                'one' => 'one'
            ));
            $obj->set('two','two');
            $me->test('set, replace existing', 
                $obj->get('one') === 'one' && $obj->get('two') === 'two' 
            );

            $me->test('set, count after set', 
                $obj->getCount() === 4 
            );
        });

        $me->test('setIf', method_exists($class, 'setIf'), function() use ($me, $class) 
        {
            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3
            ));
            $me->test('setIf, default', $obj->setIf('two', 4));
        });

        $me->test('setIfNot', method_exists($class, 'setIfNot'), function() use ($me, $class) 
        {
            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3
            ));
            $me->test('setIfNot, default', $obj->setIfNot('five', 5));
        });

        $me->test('fill', method_exists($class, 'fill'), function() use ($me, $class) 
        {
            $obj = new $class();
            
            $array = $obj->fill()->getAll();
            $me->test('fill, empty value', empty($array));
            
            $array = $obj->fill(1, 'b')->getAll();
            $me->test('fill, valid and exact value', $array['b'] === 1);
            
            $array = $obj->fill(0, array('c', 'd'))->getAll();
            $me->test('fill, array value', $array['c'] === 0 and $array['d'] === 0);
        });

        $me->test('fillIf', method_exists($class, 'fillIf'), function() use ($me, $class) 
        {
            $obj = new $class(array('a' => null));
            $array = $obj->fillIf(1, 'a')->getAll();
            $me->test('fillIf, exist key', $array['a'] === 1);
            
            $array = $obj->fillIf(1, 'b')->getAll();
            $me->test('fillIf, key', !array_key_exists('b', $array));
            
            $array = $obj->fillIf(0, array('a', 'b'))->getAll();
            $me->test('fillIf, mixed array', $array['a'] === 0 and !array_key_exists('b', $array));
        });

        $me->test('fillIfNot', method_exists($class, 'fillIfNot'), function() use ($me, $class) 
        {
            $obj = new $class(array('a' => null));
            $array = $obj->fillIfNot(1, 'a')->getAll();
            $me->test('fillIfNot, exist key', $array['a'] == 1);

            $obj = new $class(array('a' => null));
            $array = $obj->fillIfNot(1, 'a', false)->getAll();
            $me->test('fillIfNot, exist key strict', $array['a'] == null);
            
            $array = $obj->fillIfNot(1, 'b')->getAll();
            $me->test('fillIfNot, key', array_key_exists('b', $array) && $array['b'] === 1);
            
            $array = $obj->fillIfNot(0, array('a', 'b'))->getAll();
            $me->test('fillIfNot, mixed array', $array['a'] == 0 and array_key_exists('b', $array) && $array['b'] === 1);
        });

        $me->test('get', method_exists($class, 'get'), function() use($me)
        {
            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3
            ));
            $me->test('get, valid params', 
                $obj->get('one') === 1 
            );
            $me->test('get, empty params', 
                $obj->get() === null 
            );
            $me->test('get, invalid params', 
                $obj->get('x') === null,
                function() use($me, $obj){
                    $me->test('get, invalid params, with fallback', 
                        $obj->get('x', 1) === 1 
                    );
                });

            $data = $obj->get(array('one','two'));
            $me->test('get, array string params', 
                $data['one'] === 1 and $data['two'] === 2 
            );
        });

        $me->test('add', method_exists($class, 'add'), function() use($me)
        {
            $obj = new Collection();
            $added = $obj->add('one');
            $me->test('add, return value', $added === $obj);
            $me->test('add, added', $added->contain('one') === true);
        });

        $me->test('getCount', method_exists($class, 'getCount'), function() use($me)
        {
            $obj = new Collection();
            $obj->add('item1');
            $obj->add('item2');
            $me->test('getCount, count', $obj->getCount() === 2);
            $obj_nonblank = new Collection(array('one'=>1));
            $me->test('getCount, nonblank collection', $obj_nonblank->getCount() === 1);  
            $obj_blank = new Collection();
            $me->test('getCount, blank collection', $obj_blank->getCount() === 0);
        });

        $me->test('getKeys', method_exists($class, 'getKeys'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $me->test('getKeys, default',is_array($obj->getKeys()) && count($obj->getKeys()) === 2 );
            $keys = $obj->getKeys();
            $me->test('getKeys, valid keys name', $keys[0] === 'one' && $keys[1] === 'two' );
        });

        $me->test('getValues', method_exists($class, 'getValues'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $me->test('getValues, default', 
                is_array($obj->getValues()) && count($obj->getValues()) === 2
            );
            $values = $obj->getValues();
            $me->test('getValues, valid values', 
                $values[0] === 1 && $values[1] === 2 
            );
        });

        $me->test('getAll', method_exists($class, 'getAll'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $data = $obj->getAll();
            $me->test('getAll, valid data item', 
                count($data) === 2 and $data['one'] === 1 
            );
        });

        $me->test('reset', method_exists($class, 'reset'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $me->test('reset, default', 
                count($obj->reset()->getCount() === 0)
            );
        });

        $me->test('clear', method_exists($class, 'clear'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $me->test('clear, default', 
                count($obj->clear()->getCount() === 0) 
            );
        });

        $me->test('clean', method_exists($class, 'clean'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $me->test('clean, default', $obj->clean());
            $obj = new Collection();
            $me->test('clean, null', count($obj->clear()->getCount() === 0));
        });

        $me->test('contain', method_exists($class, 'contain'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $me->test('contain, valid item', 
                $obj->contain('1')
            );
            $me->test('contain, invalid item and strict', 
                $obj->contain('1', true) === false
            );
            $me->test('contain, invalid item', 
                $obj->contain('six') === false 
            );
            $me->test('contain, wrong params', 
                $obj->contain(array()) === false 
            );
            $me->test('contain, wrong params 2', 
                $obj->contain() === false 
            );
        });

        $me->test('has', method_exists($class, 'has'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2, 'three'=>null));
            $me->test('has, valid item', 
                $obj->has('one')
            );
            
            $me->test('has, invalid item', 
                $obj->has('three') === false
            );
            $me->test('has, invalid item strict', 
                $obj->has('three', true) === false
            );
            $me->test('has, invalid item unstrict', 
                $obj->has('three', false) === true
            );

            $me->test('has, array', 
                $obj->has(array()) === false 
            );

            $me->test('has, wrong params', 
                $obj->has() === false 
            );
            $me->test('has, wrong params 2', 
                $obj->has((object)array()) === false 
            );
        });

        $me->test('each', method_exists($class, 'each'), function() use($me)
        {
            $obj = new Collection(array(
                'one'=>1,'two'=>2
            ));

            $me->test('each, invalid params', 
                $obj->each(function(){}) === true // now return is boolean instead of object itself 
            );

            $x = array();
            $obj->each(function($item, $key, $data, $instance) use(&$x){
                $x[$key] = $item;
            });
            $me->test('each, valid callable', 
                $x === $obj->getAll() 
            );

            $x = array();
            $obj->each(function($key, $item, $data, $instance) use(&$x){
                $x[$key] = $item;
                return false;
            });
            $me->test('each, valid callable and stop on first', 
                count($x) === 1 
            );
        });

        $me->test('filter', method_exists($class, 'filter'), function() use($me)
        {
            $obj = new Collection(array(
                'one'=>1,'two'=>2
            ));
            $filtered = $obj->filter(function(){
                return true;
            });
            $me->test('filter, empty params', $filtered->getCount() === $obj->getCount());
        });

        $me->test('reject', method_exists($class, 'reject'), function() use($me)
        {
            $obj = new Collection(array(
                'one'=>1,'two'=>2
            ));
            $filtered = $obj->filter(function(){
                return true;
            });
            $me->test('reject, default', false);
        });

        $me->test('first, default', method_exists($class, 'first'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $all = $obj->getAll();
            $me->test('first', 
                $obj->first() === reset($all) 
            );
        });

        $me->test('last', method_exists($class, 'last'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2));
            $all = $obj->getAll();
            $me->test('last, default', 
                $obj->last() === end($all) 
            );
        });

        $me->test('remove', method_exists($class, 'remove'), function() use($me)
        {
            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3,'four'=>4
            ));
            $old = $obj->getAll(); // keep the data for next use

            /*remove one*/
            $remove = $obj->remove(1) === 'one';
            $all = $obj->getAll();
            $me->test('remove, one',
                $remove and reset($all) === 2 
            );
            /*removeOn*/
            $remove = $obj->removeOn('two') === 2;
            $all = $obj->getAll();
            $me->test('removeOn', 
                $remove and reset($all) === 3 
            );
            /*removeIf*/;
            $remove = $obj->removeIf(3) == array('three'=>3);
            $all = $obj->getAll();
            $me->test('removeIf', 
                $remove and reset($all) === 4 
            );
            /*removeIfNot*/;
            $remove = $obj->removeIfNot(3) == array('four'=>4);
            $all = $obj->getAll();
            $me->test('removeIfNot', 
                $remove and count($all) === 0 
            );

            /*removeIn*/;
            $obj = new Collection(array('one'=>1,'two'=>2,'three'=>3,'four'=>4));

            $remove = $obj->removeIn(array(1,2)) == array('one'=>1,'two'=>2);
            $all = $obj->getAll();

            $me->test('removeIn, array params', 
                $remove and count($all) === 2 
            );

            $remove = $obj->removeIn(3) == array('three'=>3);
            $all = $obj->getAll();

            $me->test('removeIn, single string params', 
                $remove and count($all) === 1 
            );
            
            /*removeKey*/
            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3,'four'=>4
            ));
            $me->test('removeKey', $obj->removeKey('two') === 2);

            /*removeIn*/
            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3,'four'=>4
            ));
            $remove = $obj->removeIn(array(3));
            $me->test('removeIn', count($obj) === 3);

            /*removeBy*/
            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3,'four'=>4
            ));
            $condition = array('one');

            $obj = new Collection(array(
                'one'=>1,'two'=>2,'three'=>3,'four'=>4
            ));
            $remove = $obj->removeBy(array(3));
            $me->test('removeBy', count($obj) === 3);
        });


        $me->test('removeEx', method_exists($class, 'removeEx'), function() use($me)
        {
            $obj = new Collection(array('one'=>1,'two'=>2,'three'=>3,'four'=>4));
            $remove = $obj->removeEx(array(3,4)) == array('one'=>1,'two'=>2);
            $all = $obj->getAll();
            $me->test('removeEx, array params', $remove and count($all) === 2);
            $remove = $obj->removeEx(2) == array('three'=>3,'four'=>4);
            $all = $obj->getAll();
            $me->test('removeEx, single string params', $remove and count($all) === 0);
            $obj = new Collection(array('one'=>1,'two'=>2,'three'=>3,'four'=>4));            
            $me->test('removeEx, default, remove All', $obj->removeEx() == true);
            $obj = new Collection(array('one'=>1,'two'=>2,'three'=>3,'four'=>4));
            $me->test('removeEx, ex one', $obj->removeEx(array(1)) == true);
        });

        $me->test('remap', method_exists($class, 'remap'), function() use($me)
        {
            $obj = new Collection(array(
                'a'=>'A',
                'b'=>'B',
                'c'=>'C',
                'd'=>'D',
            ));
            $obj->remap(array(
                'a'=>'A',
                'b'=>'B',
            ));
            $me->test('remap, default', $obj->get('A') === 'A' and $obj->get('B') === 'B' ); 
        });
        
        $me->test('sum', method_exists($class, 'sum'), function() use($me){
            $obj = new Collection(array(
                1,2,3,4
            ));
            $me->test('sum, default', $obj->sum() == 10);
        });

        $me->test('min', method_exists($class, 'min'), function() use($me){
            $obj = new Collection(array(
                1,2,3,4
            ));
            $me->test('min, default', $obj->min() == 1 );
        });

        $me->test('max', method_exists($class, 'max'), function() use($me){
            $obj = new Collection(array(
                1,2,3,4
            ));
            $me->test('max, default', $obj->max() == 4 );
        });

        $me->test('average', method_exists($class, 'average'), function() use($me){
            $obj = new Collection(array(
                1,2,3,4
            ));
            $me->test('average, default', $obj->average() == 2.5 );
        });

        $me->test('isEmpty', method_exists($class, 'isEmpty'), function() use($me){
            $obj = new Collection();
            $me->test('isEmpty, default', $obj->isEmpty() == true);
            $obj = new Collection(array(1,2,3,4));
            $me->test('isEmpty, value', $obj->isEmpty() == false);
        });

        $me->test('getAt', method_exists($class, 'getAt'), function() use($me){
            $obj = new Collection(array('A','F','G','H'));
            $me->test('getAt, default', $obj->getAt() == 'A');
            $me->test('getAt, valid, value', $obj->getAt(2) == 'G');
        });

        $me->test('getFirst', method_exists($class, 'getFirst'), function() use($me){
            $obj = new Collection(array('A','F','G','H'));
            $me->test('getFirst, default', $obj->getFirst() == 'A');
        });

        $me->test('getLast', method_exists($class, 'getLast'), function() use($me){
            $obj = new Collection(array('A','F','G','H'));
            $me->test('getLast, default', $obj->getLast() == 'H');
        });

        $me->test('toArray', method_exists($class, 'toArray'), function() use($me){
            $obj = new Collection(array('A','F','G','H'));
            $me->test('toArray, default, array value', $obj->toArray() == true);
            $obj = new Collection(1);
            $me->test('toArray, valid, integer value', $obj->toArray() == true);
            $obj = new Collection('A');
            $me->test('toArray, valid, string value', $obj->toArray() == true);
        });

        $me->test('toJson', method_exists($class, 'toJson'), function() use($me){
            $obj = new Collection(array('A','F','G','H'));
            $me->test('toJson, default', json_decode($obj->toJson()) == array('A','F','G','H'));
        });

        $me->test('hasKey', method_exists($class, 'hasKey'), function() use($me){
            $obj = new Collection(array('A'=>1,'F'=>5,'G'=>8,'H'=>10));
            $me->test('hasKey, default', $obj->hasKey('A') == true);
        });

        $me->test('hasItem', method_exists($class, 'hasItem'), function() use($me){
            $obj = new Collection(array('A'=>1,'F'=>5,'G'=>8,'H'=>10));
            $me->test('hasItem, default', $obj->hasItem(1) == true);
        });

        $me->test('iterable', method_exists($class, 'iterable'), function() use($me){
            $obj = new Collection(array('A'=>1,'F'=>5,'G'=>8,'H'=>10));
            $me->test('iterable, default, null', count($obj->iterable()) == 0);
            $me->test('iterable, valid, array', is_array($obj->iterable(array(1,2,3))) == true);
        });

        $me->test('sort', method_exists($class, 'sort'), function() use($me){
            $obj = new Collection(array('A'=>1,'F'=>5,'G'=>8,'H'=>10));
            $me->test('sort, default', $obj->sort());
        });

        $me->test('addAll', method_exists($class, 'addAll'), function() use($me){
            $obj = new Collection(array(9));
            $me->test('addAll, default, value', count($obj->addAll(2)) == 2);
            $me->test('addAll, valid, array', count($obj->addAll(array(3))) == 3);
        });

        $me->test('containIn', method_exists($class, 'containIn'), function() use($me){
            $obj = new Collection(array('A'=>1,'F'=>5,'G'=>8,'H'=>10));
            $me->test('containIn, invalid, null', $obj->containIn() == false);
            $obj = new Collection(array(1,3));
            $me->test('containIn, valid, value', $obj->containIn(array(1)) == true);
            $me->test('containIn, invalid, value', $obj->containIn(array(9)) == false);
        });

        $me->test('shuffle', method_exists($class, 'shuffle'), function() use($me){
            $obj = new Collection(array(1,3,9));
            $me->test('shuffle, default', $obj->shuffle() == true);
        });

        $me->test('diff', method_exists($class, 'diff'), function() use($me){
            $obj = new Collection(array(1,3,9));
            $me->test('diff, default', $obj->diff(1) == true);
        });

        $me->test('diffKeys', method_exists($class, 'diffKeys'), function() use($me){
            $obj = new Collection(array(1,3,9));
            $me->test('diffKeys, default', $obj->diffKeys(1) == true);
        });

        $me->test('invoke', method_exists($class, 'invoke'), function() use($me){
            $obj = new Collection(array(1,3,9));
            $me->test('invoke, default', 
                $obj->invoke(function($item, $key){
                    return $item * 2;
                })
            );
        });

        $me->test('every', method_exists($class, 'every'), function() use($me){
            $obj = new Collection(array(1,3,9));
            $me->test('every, default', 
                $obj->every(function($item, $key){
                    return $item * 2;
                })
            );
        });

        $me->test('some', method_exists($class, 'some'), function() use($me){
            $obj = new Collection(array(1,2,3,4));
            $me->test('some, default', 
                $obj->some(function($item, $key){
                    return true;
                })
            );
        });

        $me->test('chunk', method_exists($class, 'chunk'), function() use($me){
            $obj = new Collection(array(1,3,9));
            $me->test('chunk, default', $obj->chunk(1) == true);
        });

        $me->test('flip', method_exists($class, 'flip'), function() use($me){
            $obj = new Collection(array(1,3,9));
            $me->test('flip, default', $obj->flip() == true);
        });

        #reject

        $me->test('implode', method_exists($class, 'implode'), function() use($me){
            $obj = new Collection([1,2,5,8]);
            $me->test('implode, default', $obj->implode(',') == true);
        });

        #groupBy
        
        $me->test('intersect', method_exists($class, 'intersect'), function() use($me){
            $obj = new Collection(['Desk', 'Sofa', 'Chair']);
            $me->test('intersect, default', count($obj->intersect(['Desk', 'Chair', 'Bookcase'])) === 2);
        });

        #intersectKey
        #pipe
        #pluck
        #search
        $me->test('reverse', method_exists($class, 'reverse'), function() use($me){
            $obj = new Collection(['one'=>1,'two'=>2,'three'=>3]);
            $me->test('reverse, default', $obj->reverse());
        });
        
        $me->test('random', method_exists($class, 'random'), function() use($me){
            $obj = new Collection([1,2,5,8]);
            $me->test('random, default', $obj->random() == true);
        });

        $me->test('count', method_exists($class, 'count'), function() use($me){
            $obj = new Collection([1,2,5,8]);
            $me->test('count, default', $obj->count() == 4);
        });

        $me->test('getIterator', method_exists($class, 'getIterator'), function() use($me){
            $obj = new Collection([1,2,5,8]);
            $me->test('getIterator, default', $obj->getIterator());
        });

        $me->test('jsonSerialize', method_exists($class, 'jsonSerialize'), function() use($me){
            $obj = new Collection([1,2,5,8]);
            $me->test('jsonSerialize, default', $obj->jsonSerialize());
        });

        $me->test('__construct', method_exists($class, '__construct'), function() use($me){
            $obj = new Collection([1,2,5,8]);
            $me->test('__construct, default', $obj->__construct(array(3,4)));
        });

        $me->test('__toString', method_exists($class, '__toString'), function() use($me){
            $obj = new Collection([1,2,5,8]);
            $me->test('__toString, default', $obj->__toString());
        });

        
        $this->createUnfinishedTask();

    }

}