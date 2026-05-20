<?php
require_once(str_replace('/', DIRECTORY_SEPARATOR, dirname(dirname(__DIR__)).'/vendor/simpletester/src/SimpleTester/SimpleTester.php'));
require_once(str_replace('/', DIRECTORY_SEPARATOR, dirname(dirname(__DIR__)).'/vendor/autoload.php'));

class RouletteUnittest
{
    public $connection = null;

    public $testDir = '';
    public $unittest = null;
    public $tested = null;
    public $testExt = 'php';
    public $testClass = array(
        'RouletteTest_Actor',                       // incomplete
        'RouletteTest_Base',                        // done
        'RouletteTest_Collection',                  // incomplete
        'RouletteTest_ManagedCollection',           // incomplete
        'RouletteTest_Template',                    // done
        'RouletteTest_Validation',                  // incomplete
        'RouletteTest_data_Value',                  // done
        'RouletteTest_data_Option',                 // notyet
        'RouletteTest_data_Join',                   // notyet
        'RouletteTest_data_Permission',             // incomplete
        'RouletteTest_Model',                       // incomplete
        'RouletteTest_model_Cache',                 // notyet
        'RouletteTest_model_Source',                // notyet
        'RouletteTest_model_ViewOption',            // notyet
        'RouletteTest_model_Fields',                // incomplete
        'RouletteTest_model_field_Field',           // incomplete
        'RouletteTest_model_field_Validation',      // notyet
        'RouletteTest_model_Properties',            // notyet
        'RouletteTest_model_Prototype',             // notyet
        'RouletteTest_model_Store',                 // notyet
        'RouletteTest_model_Policy',                // incomplete
        'RouletteTest_model_association_HasMany',   // incomplete
        // 'RouletteTest_model_association_HasOne',    // incomplete
        // 'RouletteTest_model_association_Relation',  // incomplete
        'RouletteTest_model_operation_Rights',      // incomplete
        'RouletteTest_query_Builder',               // notyet
        'RouletteTest_query_Operation',             // done
        'RouletteTest_query_option_Option',         // notyet
        'RouletteTest_query_option_Select',         // notyet
        'RouletteTest_query_option_Insert',         // notyet
        'RouletteTest_query_option_Update',         // notyet
        'RouletteTest_query_option_Delete',         // notyet
        'RouletteTest_Regexp',                      // notyet
    );

    function test()
    {
        $this->unittest = new SimpleTester();

        $this->tested = array();

        $testClass = $this->testClass;
        
        // add capability for custom test
        if(isset($_GET['test']) and !empty($_GET['test']))
        {
            $testClass = array();
            $testParam = explode(',', $_GET['test']);
            foreach ($testParam as $key => $value) 
            {
                $testClass[] = 'RouletteTest_'.$value;              
            }
        }

        foreach ($testClass as $i => $class) 
        {
            require(__DIR__.DIRECTORY_SEPARATOR.$this->testDir.$class.".".$this->testExt);
            
            $subtest = new $class;
            $subtest->index();

            $this->tested[$class] = $subtest;

            $this->unittest->addSubtest($subtest);
        }
    }

    function printResult()
    {   
        if(isset($_GET['debug']))
        {
            if(is_callable('dd'))
            {
                dd(
                    'Unittest', $this->unittest
                    ,'Duplicates', $this->unittest->getDuplicates()
                );
            }else{
                echo "<pre>";
                print_r('Unittest');print_r('<br/>');var_dump($this->unittest);
                print_r('Duplicates');print_r('<br/>');var_dump($this->unittest->getDuplicates());
                echo "</pre>";
            }
        }

        echo "
            <script>
            var toggle = function(el) {
                var div = el.nextSibling;
                console.log(div);
                if (div.style.display === 'block' || div.style.display === ''){
                    div.style.display = 'none';
                }else{
                    div.style.display = 'block';
                }
            }
            </script>
        ";

        echo "<div style='font-family:Tahoma; font-size:12px'>";
        echo "<h2>Test Result</h2>";
        echo sprintf(
                "<b>%s SubTests, %s Tests, %s Duplicates</b>", 
                $this->unittest->getCountSubtest(), 
                $this->unittest->getCountTest(),
                $this->unittest->getCountDuplicates()
            );
        // each detail
        foreach ($this->tested as $class => $testedClass)
        {
            echo "<hr style='background:#e0e0e0; border-width:0px; height:1px;'/>";
            echo "<span onclick='toggle(this)' style='cursor:pointer'>";
            echo sprintf(
                $testedClass->countPassed() == $testedClass->countTests() ? 
                "<b>%s</b>, %s tests <span style='color:limegreen'>complete</span>":
                "<b>%s</b>, %s tests <span style='color:red;'>%s failed</span>", 
                $testedClass->getName(),
                $testedClass->countTests(),
                $testedClass->countFailed(),
                $testedClass->countPassed());
            echo "</span>";

            echo "<div style='display:none; font-family:monospace;'>";
            foreach ($testedClass->getTests() as $test => $result)
            {
                echo "<div>";
                echo ( $result ? 
                    '[<span style="font-weight:bold; color:limegreen">1</span>]' : 
                    '[<span style="font-weight:bold; color:red;">0</span>]'
                    );
                echo " ".$test."</div>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
}

class RouletteUnittest_Model extends SimpleSubtester
{
    public $name = null;
    protected $should = null;
    protected $skip = array();

    function createUnfinishedTask()
    {
        $funcs = get_class_methods($this->name);
        $should = $this->should;
        $skip = $this->skip;
        foreach ((array)$funcs as $i => $func)
        {
            if(is_array($should) and in_array($func, $should) and !array_key_exists($func, $this->tests))
            {
                $this->test($func, false);
            }
            if(is_array($skip) and !in_array($func, $skip) and !array_key_exists($func, $this->tests))
            {
                $this->test($func, false);
            }
        }
    }
}