<?php

/**
 * @package SimpleTester
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 * @version 0.1.0
 */

require __DIR__ . DIRECTORY_SEPARATOR . 'SimpleSubtester.php';

/**
 * SimpleTester is a simple unit test class.
 */
class SimpleTester{

    /**
     * $name Name of test.
     * @var null
     */
    public $name = null;

    /**
     * $subtests Subtests will be saved on it.
     * @var array
     */
    protected $subtests = array();

    /**
     * Create new SimpleTest.
     * Example:
     *     
     *     $tester = new SimpleTest('somename');
     *     $tester->subtest('Test for module 1', function($subtest, $me){
     *         $subtest->test('Should have $name property', property_exists($me, 'name'));
     *     });
     * 
     * @param string $name Name of test.
     */
    function __construct($name = null){
        $this->name = $name;
    }

    function getSubtests()
    {
        if(!is_array($this->subtests)) $this->subtests = array();
        return $this->subtests;
    }

    /**
     * Get subtest object by name.
     * Example:
     * 
     *     $tester = new SimpleTest('somename');
     *     $tester->subtest('Test for module 1', function($subtest, $me){
     *         $subtest->test('Should have $name property', property_exists($me, 'name'));
     *     });
     *     $subtest = $tester->getSubtest('Test for module 1');
     *     $result = $subtest->result();
     *     print_r($result);
     * 
     * @param  string           $subtestName    Subtest name
     * @return SimpleSubtester                  Subtest object, `null` if not found.
     */
    function getSubtest($subtestName = null){
        if(!is_array($this->subtests)) $this->subtests = array();
        
        if(is_object($subtestName)){
            foreach ($this->subtests as $i => $s) {
                if($s === $subtestName) return $s;
            }
        }else{
            foreach ($this->subtests as $i => $s) {
                if($s->name === $subtestName) return $s;
            }
        }
    }

    /**
     * Add new subtest to the tester.
     * Subtest are part of this to be a result of some tests.
     * 
     * @param SimpleSubtester|null $subtest Subtester object to be added in.
     */
    function addSubtest(SimpleSubtester $subtest = null){
        if(!is_array($this->subtests)) $this->subtests = array();
        $this->subtests[] = $subtest;
        return $this;
    }

    /**
     * Create subtest and sun the test for it.
     * Tests are run under callback on the 2nd parameter, make it easy to use.
     * 
     *      $tester = new SimpleTester();
     *      $tester->subtest('testing module 1', function($subtest, $me){
     *          $subtest->test('testing argument1', true);
     *          $subtest->test('testing argument2', true);
     *      });
     *      $tester->subtest('testing module 2', function($subtest, $me){
     *          $subtest->test('testing argument1', true);
     *          $subtest->test('testing argument2', true);
     *      });
     *      // you cann add new tests in next time, by pass same subtest name
     *      $tester->subtest('testing module 1', ($subtest, $me){
     *          $subtest->test('testing argument3', true);
     *      });
     * 
     * @param  string   $subtestName Subtest name.
     * @param  callable $callback    A function to process the test, return `false` to prevent tests added to result. 
     * @return SimpleSubtester
     */
    function subtest($subtestName = null, $callback = null){
        $subtest = $this->getSubtest($subtestName);
        if(!$subtest){
            $subtest = new SimpleSubtester(array(
                'name'=>$subtestName,
                'tester'=>$this
            ));
            $this->addSubtest($subtest);
        }

        if(is_callable($callback)) call_user_func_array($callback, array($subtest, $this));

        return $subtest;
    }

    /**
     * Get the result of each subtest.
     * @return array Array Subtest name and test result pairs
     */
    function result(){
        $result = array();
        foreach ($this->getSubtests() as $i => $s) {
            $result[$s->name] = $s->getTests();
        }
        return $result;
    }

    function getCountSubtest()
    {
        return count($this->getSubtests());
    }

    function getCountTest($mode = 'test')
    {
        $count = 0;
        foreach ($this->getSubtests() as $i => $s) 
        {
            $localCount = 0;
            switch ($mode) 
            {
                case 'passed':
                    $localCount = $s->countPassed(); break;
                    break;
                case 'failed':
                    $localCount = $s->countFailed(); break;
                    break;
                case 'duplicate':
                    $localCount = $s->countDuplicate(); break;
                    break;
                default:
                    $localCount = $s->countTests();break;
            }
            $count += $localCount;
        }
        return $count;
    }

    function getCountPassed()
    {
        return $this->getCountTest('passed');
    }

    function getCountFailed()
    {
        return $this->getCountTest('failed');
    }

    function getCountDuplicates()
    {
        return $this->getCountTest('duplicate');   
    }

    function getDuplicates()
    {
        $duplicates = array();
        foreach ($this->getSubtests() as $key => $s) {
            $d = $s->getDuplicates();
            if(count($d))
            {
                $duplicates[$s->getName()] = $d;
            }
        }
        return $duplicates;
    }

}
