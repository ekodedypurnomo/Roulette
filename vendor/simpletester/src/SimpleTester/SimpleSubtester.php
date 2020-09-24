<?php

/**
 * 
 */
class SimpleSubtester{
    
    /**
     * Chained SimpleTester object.
     * @var null
     */
    public $tester = null;

    /**
     * Name of the SimpleSubtester.
     * @var null
     */
    public $name = null;

    /**
     * Test result
     * @var array
     */
    protected $tests = array();

    /**
     * Duplicate test 
     * @var array
     */
    protected $duplicates = array();

    /**
     * [__construct description]
     * @param array $config [description]
     */
    function __construct($config = array()){
        if(is_string($config)) $config = array('name'=> $config);

        if(is_array($config)){
            if(array_key_exists('tester', $config)) $this->tester = $config['tester'];
            if(array_key_exists('name', $config)) $this->name = $config['name'];
        }
        return $this;
    }

    function getName()
    {
        return $this->name;
    }

    /**
     * Alias of addTest
     * {@see addTest}
     */
    function test()
    {
        return call_user_func_array(array($this, 'addTest'), func_get_args());
    }

    /**
     * [addTest description]
     * @param [type]  $testName  [description]
     * @param boolean $assertion [description]
     * @param [type]  $success   [description]
     * @param [type]  $failure   [description]
     * @param [type]  $callback  [description]
     */
    function addTest($testName = null, $assertion = false, $success = null, $failure = null, $callback = null)
    {
        if(!is_array($this->tests)) $this->tests = array();
        
        if(is_callable($assertion)){
            $assertion = call_user_func_array($assertion, array($this));
        }
        # feature for duplicate test
        if(array_key_exists($testName, $this->tests))
        {
            if(!array_key_exists($testName, $this->duplicates))
            {
                $this->duplicates[$testName] = 0;
            }
            $this->duplicates[$testName] += 1;
        }

        $this->tests[$testName] = $isSuccess = (boolean) $assertion;
        
        // do the callbacks
        if(is_callable($callback)){
            call_user_func_array($callback, array($isSuccess, $this));
        }
        if(is_callable($failure) and !$isSuccess){
            call_user_func_array($failure, array($this));
        }
        if(is_callable($success) and $isSuccess){
            call_user_func_array($success, array($this));
        }
        return $this;
    }
    
    /**
     * [getTest description]
     * @param  [type] $testName [description]
     * @return [type]           [description]
     */
    function getTest($testName = null)
    {
        $tests = $this->getTests();
        foreach ($tests as $key => $test) {
            if($key === $testName) return $test;
        }
        return null;
    }

    function getDuplicates()
    {
        return $this->duplicates;
    }

    /**
     * [getTests description]
     * @return [type] [description]
     */
    function getTests()
    {
        if(!is_array($this->tests)) $this->tests = array();
        return $this->tests;
    }

    function countTests()
    {
        return count($this->getTests());
    }

    function countPassed()
    {
        return count(array_filter($this->tests, function($test){
            return $test === true;
        }));
    }

    function countFailed()
    {
        return count(array_filter($this->tests, function($test){
            return $test !== true;
        }));
    }

    function countDuplicate()
    {
        return count($this->duplicates);
    }
}