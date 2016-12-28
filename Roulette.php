<?php
/**
 * Roulette
 *
 * @package     Roulette
 * @author      Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 * @copyright   Sekawan Media <sekawanmedia.com>
 * @version     1.0.0
 */

require __DIR__ . DIRECTORY_SEPARATOR.'RouletteHelper.php';

/**
 * Class Roulette.
 * Roulette made easy to perform SQL DML by using CRUD mode.
 *
 * #Modeling
 * Simply, all table should be defined as model using Roulette, lets take a look how it be:
 *
 *      // we have table `employees` in database and its fields are `id`,`name`,`age`,`phone`,`email`,`division`
 *
 *      // so we have to create a class for modeling the table
 *      $employees_model = new Roulette();
 *      $employees_model->setTable('employees');
 *      $employees_model->addField('id','name','age','phone','email');
 *
 * Just it? how about custom model which can handling some function.
 *
 *      // Just extend it and done.
 *      class Employees extends Roulette{
 *          function __construct(){
 *              parent::__construct();
 *          }
 *          function customFuntion(){
 *              // awesome code
 *          }
 *      }
 *
 * Roulette provide new concept to apply value to its property (from now can be called config) by using `configure`.
 * It is simple, pass an array which contain any config to `__construct` function.
 *
 *      $employees_model = new Roulette(array(
 *          'table' => 'employees'
 *      ));
 *
 *      // how it works for subclass?
 *      // do kinda like this
 *      class Employees exntends Roulette{
 *          function __construct(){
 *              return call_user_func_array('parent::__construct',func_get_args()); // and done.
 *          }
 *      }
 *
 *      // why not use direct `paret::__construct()` instead of `call_user_func_array('parent::__construct',func_get_args())`
 *      // it is for subclass be able to use Roulette style.
 *      // or use the `configure` instead
 *      class Employees exntends Roulette{
 *          function __construct(){
 *              parent::__construct();
 *              return call_user_func_args(array($this, 'configure'), func_get_args());
 *          }
 *      }
 *
 *      // and create an object to use it
 *      $model = new Employees(array(
 *          // some config goes here
 *      ));
 *
 * It look strange creating an object to be able manipulating record, why dont use itself like the others ORM.
 * Roulette provide it too, there some static function on it, createRecord, loadRecord, etc.. (see doc)
 *
 *      class Employees exntends Roulette{
 *          function __construct(){
 *              parent::__construct();
 *              return call_user_func_args(array($this, 'configure'), func_get_args());
 *          }
 *      }
 *
 *      // `createRecord` instead of `create`
 *      $john = Employees::createRecord(array(
 *          'name'=>'john',
 *          'division'=>'technical support'
 *      ));
 *
 *      // `findRecords` instead of `find`
 *      $myteam = Employees::findRecords(array(
 *          'division'=>'technical support'
 *      ));
 *
 *      // .. and so on, see doc for details
 *
 * #Record Manipulation
 * Create, load, destroy, find and save record using the model.
 *
 *      // create record
 *      // record is not a model, so these are different
 *      $john = $employees_model->create(array(
 *          'id'=>1,
 *          'name'=>'john'
 *      ));
 *      // we can manipulate $john as record
 *      $john->set('phone','123123');
 *      $john->save();
 *
 *      // load model
 *      // load is use for fetching record from database using record id
 *      $john = $employees_model->load(1);
 *      $john->get('name'); // 'john'
 *
 *      // save record
 *      // saving record is simple, just execute `save` function and done
 *      $john = $employees_model->load(1);
 *      $john->set('email', 'john@email.com');
 *      $john->save();
 *
 *      //  destroy record
 *      $john = $john = $employees_model->load(1);
 *      $john->destroy();
 *
 *      // finding records
 *      $myteam = $employees_model->find(array(
 *          'division'=>'Technical Support'
 *      ));
 *      $john = $myteam[0]; // just make it easy, we assume if john is on index 0
 *      $john->set('phone','777777')->save();
 *
 * #Associating Model
 *
 * #Observable
 *
 * #And More
 *
 *
 * @author      Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 * @since       Version 1.0.0
 */
class Roulette 
{

    /**
     * @property Array Collection of associations.
     * Each are association config object
     */
    protected $associations = array();

    /**
     * @property boolean Using auto generated id.
     */
    protected $autoId = false;

    /**
     * @property Array Fields collection.
     * Contains all fields (each is Field object)
     */
    protected $fields = array();

    /**
     * @property callable Id generator.
     * It can be a function which return unique generated id,
     * or a callable string function
     */
    protected $idGenerator = null;

    /**
     * @property string Primary field name.
     */
    protected $primary = null;

    /**
     * @ignore
     */
    protected $responseMessages = array();

    /**
     * @property string Table name.
     */
    protected $table = null;

    /**
     * @ignore
     */
    protected $validationMessages = array();

    /**
     * @property Object Connection object, it can be a framework database connection instance
     */
    protected $_connection = null;

    /**
     * @property Array Data collection.
     */
    protected $_data = array(); // each(array(rawValue=>@mixed, renderedValue=>@mixed, oldRawvalue@mixed, isModified=>@boolean))

    /**
     * @property Array Event collection.
     */
    protected $_events = array();

    /**
     * @ignore
     * @property Array Error messages collection.
     */
    protected $_errors = array();

    /**
     * Instance model for some static method purpose.
     * @property object Roulette model instance.
     * @ignore
     */
    protected static $_instance = null;

    /**
     * @property Boolean indicated if model is captured for any event triggered
     */
    protected $_isObservable = true;

    /**
     * @property Boolean indicated if Object is a record, not a model
     */
    protected $_isRecord = false;

    /**
     * @property Array Collection of listeners.
     */
    protected $_listeners = array();

    /**
     * @property Boolean indicated if using converter.
     * Its will affect when get field data, any data from fields will be converted by its converter.
     */
    protected $_useConverter = false;

    /**
     * Class constructor.
     * To create an instance of Roulette is easy, just like old style and or pass an array of override config.
     *
     *      // create instance of `roulette` with default config, old style.
     *      $model = new Roulette();
     *
     *      // or create an instance and override the config
     *      $model => new Roulette(array(
     *          'table' => 'employees'  // override `table` value to `employees`
     *      ));
     *      $model->getConfig('table'); // return 'employees'
     *
     *      // config is array, so it would not work for else.
     *      $model = new Roulette('table','employees');
     *      $model->getConfig('table'); // return NULL
     *
     * The truth constructor will call `configure` method, so the config can be passed after construct with `configure`.
     *
     *      $model = new Roulette();
     *      $model->configure(array(
     *          'table' => 'employees'
     *      ));
     *
     *      $model->getConfig('table'); // return 'employees'
     *
     * @param array $config A set of override config.
     * @return Roulette
     */
    function __construct(array $config = array()) 
    {
        $this->configure($config);
        return $this;
    }

    /**
     * Configure the using given array config.
     * Any config items will be set into its property.
     * Property/Config started by `_` (underscore) will be ignored.
     *
     *      $model = new Roulette();
     *      $model->configure(array(
     *          'table'=>'employees',
     *          'primary'=>'id',
     *          '_isRecord'=>true
     *      ));
     *
     *      echo $model->getConfig(); // String 'employess'
     *      echo $model->getPrimary(); // // String 'id'
     *
     *      $model->isRecord(); // Bollean true, the config _isRecord is uncofigurable
     *
     * @param array $config Array config items
     * @return Roulette
     */
    function configure(array $config = array()) 
    {
        if (!is_array($config)) 
        {
            return $this;
        }

        foreach ($config as $configName => $configValue) 
        {
            $this->setConfig($configName, $configValue, true);
        }

        return $this;
    }

    /**
     * Sorthand for getConfig and setConfig.
     * Sorthand for getConfig if passed one argument, sorthand for setConfig if passed two or more arguments.
     *
     *      $model = new Roulette(array(
     *          'table'=>'employees'
     *      ));
     *
     *      // config as getConfig
     *      $model->config('table'); // string 'employees'
     *
     *      // config as setConfig
     *      $model->config('table','developer');
     *      $model->config('table'); // now return string 'developer'
     *
     * @return Mixed Value depend on the arguments passed
     */
    function config() 
    {
        $args_num = func_num_args();
        if ($args_num <= 1) 
        {
            return $this->getConfig(func_get_arg(0));
        } 
        else 
        {
            return $this->setConfig(func_get_arg(0), func_get_arg(1));
        }
    }

    /**
     * Get value from a config/property.
     * Property name started `_` (underscore) is unreadable, so it `null`.
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'primary' => 'employee_id',
     *          '_isRecord' => true
     *      ));
     *
     *      $model->getConfig('table');  // string 'employees'
     *      $model->getConfig('_isRecord');  // null
     *
     *      // or grap some config in a moment
     *      $configs = $model->getConfig(array(
     *          'table', 'primary'
     *      ));
     *      echo $configs['table'];  // 'employees'
     *      echo $configs['primary']; // 'employee_id'
     *
     * @param string|array $configName Config/property name
     * @return Mixed Config/property value
     */
    function getConfig($configName = null) 
    {
        if(is_array($configName))
        {
            $configs = array();
            foreach ($configName as $cName) {
                if(is_string($cName))
                {
                    $configs[$cName] = $this->getConfig($cName);
                }
            }
            return $configs;
        }

        if (preg_match("/^(_+)/", $configName, $matches)) 
        {
            return;
        }

        if (property_exists($this, $configName)) 
        {
            return $this->$configName;
        }
    }

    /**
     * Set value for a config/property.
     * Config/property started with `_` (underscore) is inaccessible,
     * setConfig operation will be ignored.
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          '_isRecord' => true,
     *          'autoId' => false
     *      ));
     *
     *      $model->setConfig('table','developer'); // change value from `employees` to `developer`
     *      $model->getConfig('table'); // string 'developer'
     *
     *      $model->setConfig('_autoId', true); // _autoId is inaccessible because started with `_` (underscore)
     *      $model->getConfig('_autoId'); // null
     *
     * @param string $configName Config/property name.
     * @param mixed $configValue Value to be set into the property.
     * @param boolean $mergeConfig merge config with its existing config, used if existing value is array. default: `false`
     * @return Roulette
     */
    function setConfig($configName = null, $configValue = null, $mergeConfig = false) 
    {
        if(is_array($configName))
        {
            foreach ($configName as $cName => $cValue) {
                $this->setConfig($cName, $cValue);
            }
            return $this;
        }

        if (preg_match("/^(_+)/", $configName, $matches)) 
        {
            return $this;
        }

        switch ($configName) 
        {
            case 'fields':

                if ($mergeConfig !== true) 
                {
                    $this->fields = array();
                }

                foreach ($configValue as $index => $field) 
                {
                    $this->addField($field);
                }

                break;

            case 'associations':

                if ($mergeConfig !== true) 
                {
                    $this->associations = array();
                }

                foreach ($configValue as $index => $assoc) 
                {
                    $this->addAssociation($assoc);
                }

                break;

            case 'listeners':

                foreach ($configValue as $event => $listener) 
                {
                    $this->addListener($event, $assoc);
                }

                break;

            default:

                if (property_exists($this, $configName) and is_array($configValue) and $mergeConfig === true) 
                {
                    $this->$configName = array_merge($this->$configName, $configValue);
                } 
                else 
                {
                    $this->$configName = $configValue;
                }
                break;
        }

        return $this;
    }

    /**
     * Get current database connection.
     *
     *      $model = new Roulette();
     *
     *      $model->getConnection(); // null
     *
     *      $connection = mysqli_connect("myhost","myuser","mypassw","mybd");
     *      $model->setConnection($connection);
     *      $model->getConnection(); // $connection
     *
     * @return Mixed Database connection.
     */
    function getConnection() 
    {
        return $this->_connection;
    }

    /**
     * Set a new database connection to model.
     *
     *      $model = new Roulette();
     *
     *      $connection = mysqli_connect("myhost","myuser","mypassw","mybd");
     *      $model->setConnection($connection);
     *
     * @param Object $connection Database connection.
     * @return Roulette
     */
    function setConnection($connection = null) 
    {
        $this->_connection = $connection;
        return $this;
    }

    /**
     * Get table name.
     *
     *      $model->setTable('Foo')
     *      $model->getTable(); // Foo
     *
     * @return string Table name.
     */
    function getTable() 
    {
        return $this->table;
    }

    /**
     * Set table name.
     * All passed table name will be convert into string and trimmed.
     * Null and empty string table name will be return class name.
     *
     *      $model->setTable('  foo  ')->getTable(); // string 'foo'
     *      $model->setTable('  foo bar  ')->getTable(); // return string 'foo_bar'
     *      $model->setTable('foo bar')->getTable(); // return string 'foo_bar'
     *
     *      $model->setTable(1);
     *      $model->getTable(); // string '1'
     *
     * @param string $table
     * @return Roulette
     */
    function setTable($table = null) 
    {
        if (is_object($table) || is_array($table)) 
        {
            return $this;
        }

        $table = str_replace(' ', '_', trim(strval($table)));

        if (is_null($table) || $table == '') 
        {
            $table = preg_replace('/(_model)?$/', '', strtolower(get_class($this)));
        }

        $this->table = $table;
        return $this;
    }

    /**
     * Get primary key.
     *
     * @return string Current primary key.
     */
    function getPrimary() 
    {
        return $this->primary;
    }

    /**
     * Set the primary key.
     * Roulette only support one field as primary key.
     * Primary should be a string field name or the field name itself, and the field name should exist is fields.
     * Primary will not change if setPrimary given wrong primary value (field doesnt exist).
     *
     *      $model = new Roulette();
     *      $model->setPrimary('id');
     *
     * @param string $primary Primary key, name of one in fields.
     * @return Roulette
     */
    function setPrimary($primary = null) 
    {
        $f = $this->getField($primary);

        if(is_null($primary) or $f)
        {
            $this->primary = $primary;
        }
        
        return $this;
    }

    /**
     * Check if model has a primary key.
     * It will check if primary value is already set by check is_null and is not empty string.
     *
     *      $model = new Roulette();
     *      $model->setPrimary();
     *      $model->hasPrimary(); // false
     *      $model->setPrimary('id');
     *      $model->hasPrimary(); // true
     *
     * @return Boolean Has primary status.
     */
    function hasPrimary() 
    {
        $primary = $this->getPrimary();
        return (is_string($primary) || is_numeric($primary)) and is_null($primary) === false && trim($primary) !== '';
    }

    /**
     * Return current fields.
     * Array of fields, each field are object.
     *
     *      $model = new Roulette(array(
     *          'fields'=>array(
     *              'id'
     *          )
     *      ));
     *      $model->addField(array(
     *          'name'=>'name'
     *      ));
     *      $model->getFields();
     *      // array of fields, and each field is object
     *       Array
     *       (
     *           [0] => stdClass Object
     *               (
     *                   [name] => id
     *                   [map] => id
     *                   [display] => id
     *                   [private] =>
     *                   [type] =>
     *                   [unique] =>
     *                   [nullable] => 1
     *                   [insertable] => 1
     *                   [updateable] => 1
     *                   [readOnly] =>
     *                   [renderer] =>
     *                   [preparer] =>
     *                   [validation] =>
     *               )
     *           [1] => stdClass Object
     *               (
     *                   [name] => name
     *                   [map] => name
     *                   [display] => name
     *                   [private] =>
     *                   [type] =>
     *                   [unique] =>
     *                   [nullable] => 1
     *                   [insertable] => 1
     *                   [updateable] => 1
     *                   [readOnly] =>
     *                   [renderer] =>
     *                   [preparer] =>
     *                   [validation] =>
     *               )
     *       )
     *
     * @return Array Fields
     */
    function getFields() 
    {
        return $this->fields;
    }

    /**
     * Return one field with matched field->name.
     * Return null if no field found.
     *
     *      $model = new Roulette(array(
     *          'fields'=>array('name')
     *      ));
     *      $model->getField('name');
     *      // object field
     *      stdClass Object
     *      (
     *          [name] => name
     *          [map] => name
     *          [display] => name
     *          [private] =>
     *          [type] =>
     *          [unique] =>
     *          [nullable] => 1
     *          [insertable] => 1
     *          [updateable] => 1
     *          [readOnly] =>
     *          [renderer] =>
     *          [preparer] =>
     *          [validation] =>
     *      )
     *
     * @param string $field Field name.
     * @return object Field object, `NULL` if not found.
     */
    function getField($field = null) 
    {
        if (!is_array($this->fields)) 
        {
            $this->fields = array();
        }

        if (is_string($field) and !empty($field)) 
        {
            return $this->getFieldBy('name', $field);
        } 
        else 
        {
            foreach ($this->fields as $index => $f) 
            {
                if ($f === $field) 
                {
                    return $f;
                }
            }
        }
    }

    function getFieldBy($attribute = null, $condition = null) 
    {
        return $this->getFieldsBy($attribute, $condition, true);
    }

    /**
     * Add one or more field to fields config.
     * Each argument passed will be add as one field.
     *
     *      $model = new Roulette();
     *
     *      $model->addField('id'); // add field with name is 'id'
     *
     *      // add field with name is 'name'
     *      $model->addField(array(
     *          'name'=>'name'
     *      ));
     *
     *      // add field with name is 'gender' and will fullfiled with complement field attributes
     *      $model->addField((object) array(
     *          'name'=>'gender'
     *      ));
     *
     *      // add 2 fields, fields with name is 'age' and fields with name is 'email'
     *      $model->addField('age', array('name'=>'email'));
     *
     * @param Object|Array|String|Mixed[] $field Field to be added in fields model.
     * @return Roulette
     */
    function addField($field = null) 
    {
        $args = func_get_args();

        $fields = array();

        foreach ($args as $index => $f) 
        {
            if (is_array($f)) 
            {
                if (!array_key_exists('name', $f) and array_key_exists(0, $f)) 
                {
                    $f['name'] = $f[0];
                    unset($f[0]);
                }
                $f = (object) $f;
            } 
            else if (is_string($f) || is_numeric($f) || is_bool($f)) 
            {
                $f = (object) array('name' => (string) $f);
            }

            if (is_object($f)) 
            {
                $f->name = property_exists($f, 'name') ? (string) $f->name : null;

                // ensure the model its not a copy/duplicate/same field
                if ($this->hasField($f->name)) 
                {
                    continue;
                }

                $f->map = property_exists($f, 'map') and !is_array($f->map) and !is_object($f->map) ? (string) $f->map : $f->name;
                $f->display = property_exists($f, 'display') and !is_array($f->display) and !is_object($f->display) ? (string) $f->display : $f->name;
                $f->type = property_exists($f, 'type') and !is_array($f->type) and !is_object($f->type) ? (string) $f->type : null;

                $f->private = property_exists($f, 'private') ? (boolean) $f->private : false;
                $f->readOnly = property_exists($f, 'readOnly') ? (boolean) $f->readOnly : false;
                $f->unique = property_exists($f, 'unique') ? (boolean) $f->unique : false;

                $f->nullable = property_exists($f, 'nullable') ? (boolean) $f->nullable : true;
                $f->queryable = property_exists($f, 'queryable') ? (boolean) $f->queryable : true;
                $f->updateable = property_exists($f, 'selectable') ? (boolean) $f->updateable : true;
                $f->insertable = property_exists($f, 'insertable') ? (boolean) $f->insertable : true;
                $f->updateable = property_exists($f, 'updateable') ? (boolean) $f->updateable : true;

                $f->renderer = property_exists($f, 'renderer') ? $f->renderer : false;
                $f->preparer = property_exists($f, 'preparer') ? $f->preparer : false;
                $f->validation = property_exists($f, 'validation') ? $f->validation : null;

                $fields[] = $f;
            } 
            else 
            {
                throw new Exception("Field " . $f . " is not valid, it should a String field name or Array config or Object");
            }
        }

        if (!is_array($this->fields)) 
        {
            $this->fields = array();
        }

        foreach ($fields as $index => $f) 
        {
            $this->fields[] = $f;
        }
        return $fields;
    }

    /**
     * Remove one or more fields.
     * Field removed will affect on next new or loaded record, previous record will not changed.
     *
     *      $model = new Roulette(array(
     *          'table'=>'employees',
     *          'fields'=>array(
     *              'id','name','age','phone','email'
     *          )
     *      ));
     *      $model->removeField('email'); // remove field by its name
     *      $model->removeField($model->getField('phone')); // remove field by the object itself
     *
     *      $model->removeField('id','name',$model->getField('age'); // remove multiple fields
     *
     * @param string|object|Mixed[] $field Fieldname or Field to be deleted
     * @return array Removed fields
     */
    function removeField($field = null) 
    {
        $args = func_get_args();

        $removed = array();

        foreach ($args as $index => $fieldToRemove) 
        {
            if (is_object($fieldToRemove)) 
            {
                foreach ($this->fields as $i => $f) 
                {
                    if ($f === $fieldToRemove) 
                    {
                        unset($this->fields[$i]);
                        $removed[] = $f;
                    }
                }
            } 
            else 
            {
                foreach ($this->fields as $i => $f) 
                {
                    if (is_object($f) and property_exists($f, 'name') and $f->name === $fieldToRemove) 
                    {
                        unset($this->fields[$i]);
                        $removed[] = $f;
                    }
                }
            }
        }

        return $removed;
    }

    /**
     * Check if model has field with given name.
     * Field name is casesensitive.
     *
     *      $model = new Roulette(array(
     *          'fields'=>array('id','name','age')
     *      ));
     *
     *      $model->hasField('id'); // return true
     *      $model->hasField($model->getField('age')); // return true
     *
     *      $model->hasField('phone'); // return false
     *
     * @param  string|Object  $field Field name or Field object
     * @return boolean        Has field status
     */
    function hasField($field = null) 
    {
        return (boolean) $this->getField($field);
    }

    /**
     * Get fields by the attribute with condition (simplify is filtering fields).
     * Field will be filter in strict mode (using `===` operator).
     *
     *      $model = new Roulette(array(
     *          'fields'=>array(
     *              array(
     *                  'name'=>'id',
     *                  'unique'=>true
     *              ),
     *              array(
     *                  'name'=>'name'
     *              ),
     *              'gender',
     *              array(
     *                  'name'=>'age',
     *                  'private'=>true
     *              ),
     *              array(
     *                  'name'=>'phone'
     *              ),
     *              array(
     *                  'name'=>'email',
     *                  'unique'=>true
     *              ),
     *              'homepage'
     *          )
     *      ));
     *
     *      $fields = $model->getFieldsBy('name', 'id'); // array
     *      echo $fields[0]->name; // 'id'
     *
     *      $fields = $model->getFieldsBy('unique', true);
     *      echo $fields[0]->name; // 'id'
     *      echo $fields[1]->name; // 'email'
     *
     *      // set firstMatch to true to get the first match only
     *      $field = $model->getFieldsBy('unique', true, true);
     *      echo $field->name; // 'id'
     *
     * Or using a function (callable) to filter it
     *
     *      $fields = $model->getFieldsBy(function($field){
     *          return $field->unique === true;
     *      });
     *      echo $fields[0]->name; // 'id'
     *      echo $fields[1]->name; // 'email'
     *
     *      // stop filtering on `false` return
     *      $fields = array();
     *      $model->getFieldsBy(function($field) use($fields){
     *          if($field->unique === true){
     *              $fields[] = $field;
     *              return false;
     *          }
     *      });
     *      echo $fields[0]->name; // 'id'
     *
     * @param  string|callable $attribute   String field attribute, or Function with a `true` value will be returned the field, `false` to stop filtering
     * @param  mixed           $condition   Condition to search
     * @param  boolean         $firstMatch  return the first match field instead of array matches
     * @return Array|Object                 Array of fields object, Object field if $firstMatch is `true` and null if not found
     */
    function getFieldsBy($attribute = 'name', $condition = null, $firstMatch = false)
    {
        $fields = array();
        if(is_callable($attribute))
        {
            foreach ($this->fields as $index => $field) 
            {
                $filtering = call_user_func_array($attribute, $field);
                if ($filtering === true) 
                {
                    if($firstMatch === true)
                    {
                        return $field;
                    }
                    else
                    {
                        $fields[] = $field;
                    }
                }
                if ( $filtering === false) break;
            }
        }
        else if(is_string($attribute))
        {
            foreach ($this->fields as $index => $field) 
            {
                if (is_object($field) and property_exists($field, $attribute) and $field->$attribute === $condition) 
                {
                    if($firstMatch === true)
                    {
                        return $field;
                    }
                    else
                    {
                        $fields[] = $field;
                    }
                }
            }
        }
        return $fields;
    }

    /**
     * Get value of the fields attribute.
     * Return will be array and fieldname -> value pairs.
     *
     *      $model = new Roulette(array(
     *          'fields'=>array(
     *              array(
     *                  'name'=>'id',
     *                  'unique'=>true
     *              ),
     *              array(
     *                  'name'=>'name'
     *              ),
     *              'gender',
     *              array(
     *                  'name'=>'age',
     *                  'private'=>true
     *              ),
     *              array(
     *                  'name'=>'phone'
     *              ),
     *              array(
     *                  'name'=>'email',
     *                  'unique'=>true
     *              ),
     *              'homepage'
     *          )
     *      ));
     *
     *      $model->getFieldsAttribute('private');
     *      // array('id'=>false,'name'=>false,'gender'=>false,'age'=>true,'phone'=>false,'email'=>false,'homepage'=>false)
     *
     *      $model->getFieldsAttribute('unique');
     *      // array('id'=>true,'name'=>false,'gender'=>false,'age'=>false,'phone'=>false,'email'=>false,'homepage'=>true)
     *
     * @param  string $attribute Field attribute, default: `name`
     * @return array             Array assoc $attribute=>$value pairs
     */
    function getFieldsAttribute($attribute = 'name') 
    {
        $fields = array();
        foreach ($this->fields as $index => $field) 
        {
            if (is_object($field) and property_exists($field, $attribute)) 
            {
                $fields[$field->name] = $field->$attribute;
            }
        }
        return $fields;
    }

    /**
     * Get all associations config.
     * Its not fetch associated records but only add association config to model.
     *
     * @return array Array of association objects
     */
    function getAssociations() 
    {
        return $this->associations;
    }

    /**
     * Add one or more associations config to model.
     * Record of this model will be able to get the associated records based on this config
     * Any association should have a name and must be unique, it use in call the `associate` function.
     *
     *      class Employess extends Roulette
     *      {
     *          function __construct()
     *          {
     *              parent::__construct();
     *              $model = new Roulette(array(
     *                  'table' => 'employees',
     *                  'fields' => array('id','name','age','managerId'),
     *                  'associations' => array( // add association on init
     *                      array( // add one association called `assocManager`
     *                          'name' => 'assocManager',
     *                          'type' => 'hasOne',
     *                          'model' => 'Employees' // model name or the object model itself
     *                          'associationKey' => 'managerId'
     *                      )
     *                  )
     *              ));
     *              $model->addAssociation(array(
     *                  'name' => 'assocHobby',
     *                  'model' => 'hobby',
     *                  'type' => 'hasMany', // if type is `hasMany` so `associationKey` will refer to the rival model
     *                  'associationKey' => 'employeeId' // in the model `hobby` should have field with name `employeeId`
     *              ))
     *          }
     *      ...
     *
     * @property array|Object|arguments[] $association Association config.
     *                                                 Config should agree with the template config:.
     *                                                 `name` -> unique name for the association config.
     *                                                 `type` -> `hasOne` if associated with one record or `hasMany` for more.
     *                                                 `model` -> the rival model to be associated, String or model object
     *                                                 `associationKey` -> field name used to associated with,
     *                                                 if association type is `hasOne` so the field name must be on this model,
     *                                                 if `hasMany` so it must be the field name on rival model
     * @return Roulette
     */
    function addAssociation($association = null) 
    {
        $args = func_get_args();

        $associations = array();

        foreach ($args as $index => $a) 
        {
            if (is_array($a)) 
            {
                $a = (object) $a;
            } 
            else if (is_string($a) || is_numeric($a) || is_bool($a)) 
            {
                $a = (object) array('name' => strval($a));
            }

            if (is_object($a)) 
            {
                $a->name = property_exists($a, 'name') and !is_array($a->name) and !is_object($a->name) ? (string) $a->name : null;

                // ensure the model its not a copy/duplicate/same association
                if ($this->hasAssociation($a->name)) 
                {
                    continue;
                }

                if (property_exists($a, 'type') and in_array(strtolower($a->type), array('hasone', 'hasmany'))) 
                {
                    $a->type = strtolower($a->type) == 'hasmany' ? 'hasMany' : 'hasOne';
                } 
                else 
                {
                    $a->type = null;
                }

                $a->model = property_exists($a, 'model') ? $a->model : null;

                $a->associationKey = property_exists($a, 'associationKey') ? $a->associationKey : null;

                $associations[] = $a;
            } 
            else 
            {
                throw new Exception("association " . $a . " is not valid, it should a config as Array or Object");
            }
        }

        if (!is_array($this->associations)) 
        {
            $this->associations = array();
        }

        foreach ($associations as $index => $a) 
        {
            $this->associations = $a;
        }
        return $associations;
    }

    /**
     * Get matched association config.
     * It could be search by association->name or association object itself.
     *
     * @property string|object  $association A name or Object of association config.
     * @return object|null      Association Object, `NULL` if model doesn't have the assocition.
     */
    function getAssociation($association = null) 
    {
        if (is_string($association)) 
        {
            foreach ($this->associations as $index => $a) 
            {
                if (is_object($a) and property_exists($a, 'name') and $a->name === $association) 
                {
                    return $a;
                }
            }
        } 
        else 
        {
            foreach ($this->associations as $index => $a) 
            {
                if ($a === $association) 
                {
                    return $a;
                }
            }
        }
    }

    /**
     * Remove one or more associations.
     * It removed associations in array.
     *
     *      $model = new Roulette(array(
     *          'table'=>'employees',
     *          'fields' => array('id','name','age','managerId'),
     *          'associations'=>array(
     *              array(
     *                  'name' => 'assocManager',
     *                  'type' => 'hasOne',
     *                  'model' => 'Employees'
     *                  'associationKey' => 'managerId'
     *              ),
     *              array(
     *                  'name' => 'assocHobby',
     *                  'model' => 'hobby',
     *                  'type' => 'hasMany',
     *                  'associationKey' => 'employeeId'
     *              )
     *          )
     *      ));
     *      $model->removeAssociation('assocManager',$model->getAssociation('assocHobby')); // remove multiple associations with
     *
     * @param string|object|arguments[] $association String name or Object association to be deleted
     * @return array                    Removed associations
     */
    function removeAssociation($association = null) 
    {
        $args = func_get_args();

        $removed = array();

        foreach ($args as $index => $associationToRemove) 
        {
            if (is_object($associationToRemove)) 
            {
                foreach ($this->associations as $i => $f) 
                {
                    if ($f === $associationToRemove) 
                    {
                        unset($this->associations[$i]);
                        $removed[] = $f;
                    }
                }
            } 
            else 
            {
                foreach ($this->associations as $i => $f) 
                {
                    if (is_object($f) and property_exists($f, 'name') and $f->name === $associationToRemove) 
                    {
                        unset($this->associations[$i]);
                        $removed[] = $f;
                    }
                }
            }
        }

        return $removed;
    }

    /**
     * Check if model has the association.
     * Its not check the model has associated record, but check if model has associations config.
     *
     *      $model = new Roulette(array(
     *          'table'=>'employees',
     *          'fields' => array('id','name','age','managerId'),
     *          'associations'=>array(
     *              array(
     *                  'name' => 'assocManager',
     *                  'type' => 'hasOne',
     *                  'model' => 'Employees'
     *                  'associationKey' => 'managerId'
     *              ),
     *              array(
     *                  'name' => 'assocHobby',
     *                  'model' => 'hobby',
     *                  'type' => 'hasMany',
     *                  'associationKey' => 'employeeId'
     *              )
     *          )
     *      ));
     *      $model->hasAssociation('assocManager'); // true;
     *      $model->hasAssociation($model->getAssociation('assocManager')); // true;
     *
     * @param string|object|arguments[] $association String name or Object association to find
     * @return Boolean                  Has Association status.
     */
    function hasAssociation($association = null) 
    {
        return (boolean) $this->getAssociation($association);
    }

    /**
     * Get associated record depend on the association config.
     * Associated model should be exist and already loaded by framework.
     *
     *      $model = new Roulette(array(
     *          'table'=>'employees',
     *          'fields' => array('id','name','age','managerId'),
     *          'associations'=>array(
     *              array(
     *                  'name' => 'assocManager',
     *                  'type' => 'hasOne',
     *                  'model' => 'Employees'
     *                  'associationKey' => 'managerId'
     *              ),
     *              array(
     *                  'name' => 'assocHobby',
     *                  'model' => 'hobby',
     *                  'type' => 'hasMany',
     *                  'associationKey' => 'employeeId'
     *              )
     *          )
     *      ));
     *
     *      $model->associate('assocManager', function($manager){
     *          // the associated record could fetch in here.
     *          // or in the return;
     *      });
     *      $model->associate($model->getAssociation('assocManager')); // this is the same way
     *
     *      $model->associate('assocManager'); // array of record, because hasMany
     *
     * @param  string|object            $association    Association config.
     * @param  callable         $callback       A callable function as callback after assocaited record fetched.
     * @param  boolean                  $reload         Set `true` to ensure model get the fresh record, `false` chached records.
     * @return object|array                             return single object record if hasOne type or null if none, or array records if hasMany type.
     */
    function associate($association = null, $callback = null, $reload = false) 
    {
        $association = $this->getAssociation($association);

        if (!$assocition) 
        {
            return;
        }

        if (is_array($this->_associated)) 
        {
            $this->_associated = array();
        }

        // use chache instead of reload, if reload is false
        if (array_key_exists($this->_associated, $association->name) and $reload === false) 
        {
            return $this->associated[$association->name];
        }

        // reload for else
        $assocModel = RouletteHelper::getModel($assocition->model);

        if (!$assocModel) 
        {
            throw new Exception("Can not find model");
        }

        if (!is_a($assocModel, 'Roulette')) 
        {
            throw new Exception("Model should extend Roulette");
        }

        if (!in_array($assocition->type, array('hasMany', 'hasOne'))) 
        {
            throw new Exception("Association type should be hasOne or hasMany");
        }

        $assocRecord = null;

        switch ($assocition->type) 
        {
            case 'hasOne':
                $assocRecord = $assocModel->load($this->get($assocition->associtionKey));
                break;
            case 'hasMany':
                $assocRecord = $assocModel->find(array(
                    $assocition->associtionKey => $this->get($assocition->associtionKey),
                ));
                break;
        }

        $this->associated[$association->name] = $assocRecord;

        return $assocRecord;
    }

    /**
     * Set record id (simplify, set value for field primary).
     * Its shorthand for set value for primary field.
     *
     *      $model = new Roulette(array(
     *          'primary' => 'id',
     *          'fields' => array('id','name','age')
     *      ));
     *
     *      $model->setId(123);
     *      // is equivalent with:
     *      $model->set($model->getPrimary(), 123);
     *
     *      $model->getId(); // return 123
     *
     * @param mixed $id Id for primary field
     * @return Roulette
     */
    function setId($id = null) 
    {
        $this->set($this->getPrimary(), $id);
        return $this;
    }

    /**
     * Get value for primary field (simplify, get value from filed primary)
     *
     *      $model = new Roulette(array(
     *          'primary' => 'id',
     *          'fields' => array('id','name','age')
     *      ));
     *
     *      $model->setId(123);
     *
     *      $model->getId(); // return 123
     *
     *      // is equivalent with:
     *      $model->get($model->getPrimary());
     *
     * @return mixed Value of id
     */
    function getId() 
    {
        return $this->get($this->getPrimary());
    }

    /**
     * Check if record has a valid id.
     * Valid id is numeric and or string and not empty string.
     *
     *      $model = new Roulette(array(
     *          'primary' => 'id',
     *          'fields' => array('id','name','age')
     *      ));
     *
     *      $model->setId(null);
     *      $model->hasId(); // return false;
     *
     *      $model->setId('');
     *      $model->hasId(); // return false;
     *
     *      $model->setId(0);
     *      $model->hasId(); // return true; zero is valid id
     *
     *      $model->setId(true);
     *      $model->hasId(); // return false;
     *
     *      $model->setId(array('1'));
     *      $model->hasId(); // return false; // array or object is not valid id
     *
     * @return boolean [description]
     */
    function hasId() 
    {
        $id = $this->getId();

        if (is_string($id)) 
        {
            $id = trim($str);
            if ($id == '') 
            {
                return false;
            }

        }

        return is_numeric($id) || is_string($id);
    }

    /**
     * Check modified value status.
     * It will check modified field value, value from `load` or `create` initialization are is not modified.
     *
     *      $model = new Roulette(array(
     *          'primary' => 'id',
     *          'fields' => array('id','name','age')
     *      ));
     *
     *      $record = $model->load(123);
     *      $record->isModified(); // return false, because the fields values are given from load
     *
     *      $record->setId(456); // now modified is true
     *      $record->isModified(); // return true
     *
     *      // how about create?
     *      $record = $model->create(array(
     *          'id' => 123
     *      ));
     *      $record->isModified(); // return false, because the fields values are given from create
     *      // and also work like usual
     *      $record->setId(456); // now modified is true
     *      $record->isModified(); // return true
     *
     * @return boolean Modified status
     */
    function isModified($field = null) 
    {
        if (!property_exists($this, '_data') or !is_array($this->_data)) 
        {
            $this->_data = array();
        }

        if (!is_null($field)) 
        {
            $field = $this->getField($field);

            if ($field and array_key_exists($field->name, $this->_data)) 
            {
                if (is_array($this->_data) and array_key_exists('isModified', $this->_data)) 
                {
                    return (boolean) $dataField['isModified'];
                }

            }

            return false;
        } 
        else 
        {
            foreach ($this->getFields as $index => $f) 
            {
                if ($this->isModified($f->name) === true) 
                {
                    return true;
                }

            }
            return false;
        }
    }

    /**
     * Indicate if this is a record not a model.
     * Model is not record.
     *
     *      $model = new Roulette(array(
     *          'primary' => 'id',
     *          'fields' => array('id','name','age')
     *      ));
     *
     *      $record = $model->load(123);
     *      $record->isRecord(); // return true
     *
     *      $model->isRecord(); // return false
     *
     * @return boolean IsRecord status.
     */
    function isRecord() 
    {
        return (boolean) $this->_isRecord;
    }

    /**
     * @ignore
     * @return [type] [description]
     */
    function getError() 
    {
        return $this->_errors;
    }

    /**
     * Indicate if this is observable or captured from event listener.
     * By default Roulette model or record are observable, it will capture any event triggered on it.
     *
     *      $model = new Roulette(array(
     *          'primary' => 'id',
     *          'fields' => array('id','name','age')
     *      ));
     *
     *      $model->isObservable(); // return true
     *      $model->on('create', function(){
     *          echo 'create an instance from model';
     *      });
     *
     * @return boolean [description]
     */
    function isObservable() 
    {
        return $this->_isObservable;
    }

    /**
     * Enable or disable from capturing event.
     * By default Roulette model or record are observable, it will capture any event triggered on it.
     * {@see trigger}
     *      $model = new Roulette(array(
     *          'primary' => 'id',
     *          'fields' => array('id','name','age')
     *      ));
     *
     *      $model->isObservable(); // return true
     *      $model->on('create', function(){
     *          echo 'create an instance from model';
     *      });
     *      $model->create(array()); // at the same time the model will echo 'create an instance from model'
     *
     *      $model->setObservable(false);
     *      $model->create(array()); // model will no longer trigger any event, until it set to `true`
     *
     * @param boolean $observable Observable status
     * @return Roulette
     */
    function setObservable($observable = true) 
    {
        $this->_isObservable = (boolean) $observable;
        return $this;
    }

    /**
     * Get all event name with active status.
     * Event with `false` value mean the event is exclude from observer.
     * @return array Associative array 'event=>active' pairs.
     */
    function getEvents() 
    {
        return $this->_events;
    }

    /**
     * Check if model has the event.
     * @param  string  $event Event name
     * @return boolean        Status
     */
    function hasEvent($event = null) 
    {
        return array_key_exists($this->_events, $event);
    }

    /**
     * Register one or more event to model.
     * Event can be disabled or enabled, by default any events added are enabled.
     *
     *      $model = new Roulette();
     *      $model->addEvent('sing'); // $model will be able to `sing`
     *
     *      $model->addEvent('walk','dance'); // add 2 events
     *      // the same way
     *      $model->addEvent(array('walk','dance'));
     *
     *      // add 2 events
     *      $model->addEvent(array(
     *          'eat' => true,
     *          'drink' => false // drink event is disabled, it could enable with `$model->enableEvent('drink')`
     *      ));
     *
     * @param string|array|arguments[] $event Event name;
     * @return array                    Added Events
     */
    function addEvent() 
    {
        $args = func_num_args();

        $eventsAdded = array();

        foreach ($args as $index => $events) 
        {
            if (!is_array($events)) 
            {
                $events = array($events => true);
            }

            foreach ($events as $event => $eventEnabled) 
            {
                if (is_numeric($event)) 
                {
                    $event = $eventEnabled;
                    $eventEnabled = true;
                }

                if (!$this->hasEvent($event)) 
                {
                    $event[] = $eventsAdded;
                }

                $this->_events[$event] = true;
            }
        }

        return $eventsAdded;
    }

    /**
     * Enabling an event.
     * Each event can be enable or disable.
     *
     *      $model = new Roulette();
     *      $model->addEvent(array(
     *          'sing' => false // event `sing` is not active or is disabled
     *      ));
     *      $model->trigger('sing'); // `sing` will not triggered because already disabled
     *      $model->enableEvent('sing'); // enabling `sing`
     *      $model->trigger('sing'); // now will trigger `sing` event
     *
     *      $model->enableEvent('sing', false); // or disabling `sing`
     *
     * @param  string  $event  Event name.
     * @param  boolean $enable Enable status, default `true`.
     * @return object          Roulette.
     */
    function enableEvent($event = null, $enable = true) 
    {
        if ($this->hasEvent($event)) 
        {
            $this->_events[$event] = (boolean) $enable;
        }
        return $this;
    }

    /**
     * Disabling an event.
     * Shorthand for `enableEvent($eventName, false);`.
     *
     * @param  string  $event  Event name.
     * @param  boolean $enable Disable status, default `true`.
     * @return object          Roulette.
     */
    function disableEvent($event = null, $disable = true) 
    {
        $this->enableEvent($event, !$disable);
    }

    /**
     * Chech enabled status from an event.
     * Each event can be enable or disable.
     *
     *      $model = new Roulette();
     *      $model->addEvent(array(
     *          'sing' => false // event `sing` is not active or is disabled
     *      ));
     *      $model->eventEnabled('sing'); // return false
     *      $model->enableEvent('sing'); // enabling `sing`
     *      $model->eventEnabled('sing'); // return true
     *
     * @param  string $event Event name.
     * @return boolean       Enabled status.
     */
    function eventEnabled($event = null) 
    {
        return array_key_exists($event, $this->_events) && $this->_events[$event] === true;
    }

    /**
     * Chech disabled status from an event.
     * Shorthand for `!eventEnabled($event);`.
     *
     * @param  string $event Event name.
     * @return boolean       Disabled status.
     */
    function eventDisabled($event = null) 
    {
        return !$this->enableEvent($event);
    }

    /**
     * Get listeners on an event.
     * If no event name given on first argument, it will check if model has any listeners on any events.
     *
     * @param string|mixed $event   (optional) Event name
     * @return array                Array of listeners on an event or more
     */
    function getListeners() 
    {
        if (func_num_args() > 0) 
        {
            $event = func_get_arg(0);
            return array_key_exists($event, $this->_listeners) ? $this->_listeners[$event] : array();
        } 
        else 
        {
            return $this->_listeners;
        }
    }

    /**
     * Check if model has any listeners on an event.
     * If no event name given on first argument, it will check if model has any listeners on any events.
     *
     *      $model = new Roulette();
     *      $model->hasListener(); // return false;
     *
     *      $model->addEvent('sing');
     *      $model->addListener
     *
     * @param string|mixed $event                   (optional) Event name
     * @param callable $listener    (optional) any passed argument on 2nd parameter will check if it already attached on the event
     * @return boolean                              Array of listeners on an event or more
     */
    function hasListeners() 
    {
        $args = func_get_args();
        if (count($args) > 0) 
        {
            $event = func_get_arg(0);

            if (count($args) >= 2) 
            {
                return is_array($this->_listeners) && array_key_exists($event, $this->_listeners) && in_array($args[1], $this->_listeners);
            } 
            else 
            {
                return is_array($this->_listeners) && array_key_exists($event, $this->_listeners) && !empty($this->_listeners[$event]);
            }
        } 
        else 
        {
            foreach ($this->_events as $index => $event) 
            {
                if ($this->hasListener($event)) 
                {
                    return true;
                }

            }
        }
    }

    /**
     * Add listener to event.
     * One event could have one or more listeners.
     * Duplicate listener will be ignored.
     *
     *      $model = new Roulette();
     *      $model->addEvent('sing');
     *      $model->addListener('sing', function(){}); // add a listener to `sing`
     *
     *      // how it work in non exist event
     *      $model->addListener('sleep', function(){}); // event `sleep` will be added immediately, and add a listener
     *
     * @param string|array $event                   String event or an array `event=>listener` pairs.
     * @param callable $listener    Listener to attach on to.
     * @return Roulette
     */
    function addListener($event = null, $listener = null) 
    {
        if (!is_array($event)) 
        {
            $event = array($event => $listener);
        }
        foreach ($event as $e => $l) 
        {
            if ($this->hasListener($e, $l)) 
            {
                continue;
            }

            if (array_key_exists($e, $this->_listeners) || !is_array($this->_listeners[$e])) 
            {
                $this->_listeners[$e] = array();
            }

            $this->_listeners[$e][] = $l;
        }
        return $this;
    }

    /**
     * Invoke each listeners on an event.
     * In some framework usually called `fireEvent`.
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'fields' => array('id','name','age')
     *          'listeners' => array(
     *              'sing' => function(){
     *                  echo 'lha lha lha';
     *              }
     *          )
     *      ));
     *      $model->addEvent('dance');
     *      $model->on('dance', function($me){
     *          echo $me.' swing swing swing';
     *      });
     *
     *      $model->trigger('sing'); // will echo 'lha lha lha'
     *      $model->trigger('dance', array($model->getTable())); // will echo 'employess swing swing swing'
     *
     * @param string|Array  $event      Event name.
     * @param array         $params     Arguments for the function.
     * @return boolean                  Return `true` by default, if any listeners return `false` it will stop invoke the rest listeners
     */
    function trigger($event = null, $params = array()) 
    {
        if ($this->isObservable() and $this->eventEnabled($event)) 
        {
            foreach ($this->getListener($event) as $index => $listener) 
            {
                if (is_callable($listener)) 
                {
                    $callback = call_user_func_array($listener, $params);
                    if ($callback === false) 
                    {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Shorthand for trigger.
     * {@see trigger}
     *
     * @param string|Array $event   Event name.
     * @param array     $params     Arguments for the function.
     * @return boolean Return `true` by default, if any listeners return `false` it will stop invoke the rest listeners
     */
    function fireEvent($event = null, $params = array()) 
    {
        return $this->trigger();
    }

    /**
     * Get status do the model is using converter.
     * Converter are invoked when get field value.
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'fields' => array(
     *              'id',
     *              array(
     *                  'name'=>'name',
     *                  'converter'=>function($value, $record){
     *                      return strtolower($value);
     *                  }
     *              ),
     *              'age'
     *          )
     *      ));
     *
     *      $record = $model->create(array(
     *          'name'=>'John Doe';
     *      ));
     *      $record->get('name'); // string 'John Doe'
     *      $record->convert()->get('name'); // wil return string 'john doe'
     *      $record->usingConverter(); // return true
     *
     *      $record->convert(false);
     *      $record->usingConverter(); // return false
     *
     * @return boolean status.
     */
    function usingConverter() 
    {
        return (boolean) $this->_useConverter;
    }

    /**
     * Enabling converter on field.
     * If this is enabled `get` field value will be convert using it field converter.
     * Using converter status can be obtain from `useConverter` function.
     * {@see usingConverter}
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'fields' => array(
     *              'id',
     *              array(
     *                  'name'=>'name',
     *                  'converter'=>function($value, $record){
     *                      return strtolower($value);
     *                  }
     *              ),
     *              'age'
     *          )
     *      ));
     *
     *      $record = $model->create(array(
     *          'name'=>'John Doe';
     *      ));
     *      $record->get('name'); // string 'John Doe'
     *      $record->convert()->get('name'); // wil return string 'john doe'
     *      $record->usingConverter(); // return true
     *
     *      $record->convert(false);
     *      $record->usingConverter(); // return false
     *
     * @param  boolean $useConverter useConverter status, default is `true`
     * @return object                Roulette record/model
     */
    function convert($useConverter = true) 
    {
        $this->_useConverter = (boolean) $useConvert;
        return $this;
    }

    /**
     * Set primary value with new generated id.
     * Be carefull with synched record, change id is not auto sync to database.
     * {@see generateId}
     *
     * @param  string $salt Some random string to make generated id more salty, default `NULL`
     * @return object       Roulette
     */
    function renewId($salt = null) 
    {
        $this->setId($this->generateId($salt));
        return $this;
    }

    /**
     * Generate a new random id.
     * By default idGenerator are return MD5 (32 char).
     *
     * @param  [type] $salt [description]
     * @return [type]       [description]
     */
    function generateId($salt = null) 
    {
        if (is_null($this->idGenerator)) 
        {
            return md5(get_class($this) . microtime(true) . rand(1, 1000) . $salt);
        }

        if (is_callable($this->idGenerator)) 
        {
            return $this->idGenerator($salt, $this);
        }
    }

    /**
     * Set a new value for one or more field.
     * Set value to readOnly field will be ignored.
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'fields' => array('id','name','age')
     *      ));
     *      $record = $model->crate();
     *      $record->get('name'); // return null
     *
     *      $record->set('name','John');
     *      $record->set(array(
     *          'id' => 123,
     *          'age' => 27
     *      ));
     *      $record->get('id'); // return 123
     *      $record->get('name'); // return 'John'
     *      $record->get('age'); // return '27'
     *
     * @param string|array  $field      Field name or array `field=>value` pairs
     * @param mixed         $value      Field value
     * @param boolean       $baseValue  It use for model/record to set new value from `load` and `create`, if `true` will mark as not modified
     * @return Object                   Roulette
     */
    function set($field = null, $value = null, $baseValue = false) 
    {
        $modified = array();

        if (!is_array($field)) 
        {
            $field = array((string) $field => $value);
        }

        if (is_array($field)) 
        {
            foreach ($field as $fieldName => $val) 
            {
                $f = $this->getField($field);

                // if set on invalid field name or in readOnly field
                if (!$f or ($f->readOnly == true and $baseValue !== true)) 
                {
                    continue;
                }

                if (!is_array($this->_data)) 
                {
                    $this->_data = array();
                }

                if (!(array_key_exists($field, $this->_data) and is_array($this->_data[$field]) and count($this->_data[$field]) >= 3)) 
                {
                    $this->_data[$field] = array('rawValue' => null, 'value' => null, 'oldRawValue' => null, 'isModified' => false);
                }

                $preparer = null;
                if ($f and is_object($f) and property_exists($f, 'preparer') and !empty($f->preparer)) 
                {
                    $preparer = $f->preparer;
                }

                $renderer = null;
                if ($f and is_object($f) and property_exists($f, 'renderer') and !empty($f->renderer)) 
                {
                    $renderer = $f->renderer;
                }

                if ($baseValue === true) 
                {
                    $this->_data[$field]['oldRawValue'] = $val;
                    $this->_data[$field]['isModified'] = false;

                    $this->_data[$field]['rawValue'] = $val;
                    $this->_data[$field]['value'] = $renderer ? (is_callable($renderer) ? call_user_func_array($renderer, array($val, $fieldName, $this)) : null) : $val;
                } 
                else 
                {
                    $this->_data[$field]['oldRawValue'] = $this->_data[$field]['rawValue'];
                    $this->_data[$field]['isModified'] = true;

                    $this->_data[$field]['rawValue'] = $preparer ? (is_callable($preparer) ? call_user_func_array($preparer, array($val, $fieldName, $this)) : null) : $val;
                    $this->_data[$field]['value'] = $renderer ? (is_callable($renderer) ? call_user_func_array($renderer, array($val, $fieldName, $this)) : null) : $val;
                }

                $modified[] = $f->name;
            }
        }

        return $modified;
    }

    /**
     * Get field value.
     * Return field rendered field value, converted value if usingConverter.
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'fields' => array(
     *              array('name'=>'id', 'unique'=>true),
     *              array('name'=>'name', 'renderer'=>function($value){
     *                  return strtolower($value);
     *              }),
     *              array('name'=>'gender', preparer($value){
     *                    // gender only accept value 0 or 1, o for female and 1 for male
     *                    return (int)((boolean) $value);
     *              }, 'converter'=>function($value){
     *                  // then render when output
     *                  $value = (int)((boolean) $value);
     *                  return $value === 1 ? return 'Male' : 'Female';
     *              }),
     *              array(
     *                  'name'=>'password', 'private'=>true
     *              )
     *          )
     *      ));
     *
     *      $record = $model->create(array(
     *          'id'=>123,
     *          'name'=>'John Doe',
     *          'gender'=>null,
     *          'password'=>'secret'
     *      ));
     *
     *      $record->get('id'); // return 123
     *
     *      $record->get('name'); // return string `john doe` be lower case from renderer
     *
     *      $record->get('gender'); // return 0
     *      $record->convert()->get('gender'); // `Female`
     *      $record->get('gender', true); // using raw mode, return 0
     *
     *      $record->convert(false); // turning off converter
     *      $record->get('password'); // return secret, field password will be exclude from getData()
     *
     * @param  sring|array  $field  Field name to fetch the value.
     * @param  boolean $raw         Raw/basic value mode, it used for model to save into database
     * @return mixed                value, single mixed value if field is string, array `field=>value` pairs if field is array
     */
    function get($field = null, $raw = false) 
    {
        $value = null;

        if (is_array($field)) 
        {
            $value = array();

            foreach ($field as $index => $f) 
            {
                $value[$f] = $this->get($f, $raw);
            }

            return $value;
        }
        //else

        if (is_object($field) or is_array($field)) 
        {
            return;
        }

        $f = $this->getField($field);

        if (!$f) 
        {
            return;
        }

        if (!is_array($this->_data)) 
        {
            $this->_data = array();
        }

        if (!array_key_exists($field, $this->_data) or !is_array($this->_data[$field])) 
        {
            $this->_data[$field] = $this->set($field, null, true);
        }

        if (array_key_exists('value', $this->_data[$field])) 
        {
            $value = $this->_data[$field]['value'];

            $converter = null;
            if ($this->usingConverter()) 
            {
                if (is_object($f) and property_exists($f, 'converter') and !empty($f->converter)) 
                {
                    $converter = $f->converter;
                }
                $value = $converter ? (is_callable($converter) ? call_user_func_array($converter, array($value, $f->name, $this)) : null) : $value;
            }
        }
        return $value;
    }

    /**
     * Get all fields value.
     * Value from private field will be excluded, but will no longer if field name are passed in arguments
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'fields' => array(
     *              array('name'=>'id', 'unique'=>true),
     *              array('name'=>'name', 'renderer'=>function($value){
     *                  return strtolower($value);
     *              }),
     *              array('name'=>'gender', preparer($value){
     *                    // gender only accept value 0 or 1, o for female and 1 for male
     *                    return (int)((boolean) $value);
     *              }, 'converter'=>function($value){
     *                  // then render when output
     *                  $value = (int)((boolean) $value);
     *                  return $value === 1 ? return 'Male' : 'Female';
     *              }),
     *              array(
     *                  'name'=>'password', 'private'=>true
     *              )
     *          )
     *      ));
     *
     *      $record = $model->create(array(
     *          'id'=>123,
     *          'name'=>'John Doe',
     *          'gender'=>null,
     *          'password'=>'secret'
     *      ));
     *
     *      $record->getData();
     *      // return is array,
     *      // array(
     *      //    'id'=>123,
     *      //    'name'=>'john doe',
     *      //    'gender'=>0
     *      // )
     *
     *      $record->getData(array('id', )); // return string `john doe` be lower case from renderer
     *
     *      $record->get('gender'); // return 0
     *      $record->convert()->get('gender'); // `Female`
     *      $record->get('gender', true); // using raw mode, return 0
     *
     *      $record->convert(false); // turning off converter
     *      $record->get('password'); // return `secret`, field password will be exclude from getData()
     *
     *
     * @param  [type] $fields [description]
     * @return [type]         [description]
     */
    function getData($fields = null) 
    {
        $args = func_get_args();

        $data = array();

        if (!count($args)) 
        {
            $fields = array_keys($this->getFieldsAttribute('private', false));
        } 
        else 
        {
            $fields = array();
            foreach ($args as $i => $field) 
            {
                if (is_array($field)) 
                {
                    foreach ($field as $fieldName) 
                    {
                        $fields[] = $fieldName;
                    }
                } 
                else if (is_object($field) and property_exists($field, 'name')) 
                {
                    $fields[] = $fieldName;
                } 
                else 
                {
                    $fields[] = (string) $fieldName;
                }
            }
        }

        if (is_array($fields)) 
        {
            foreach ($fields as $index => $field) 
            {
                $data[$field] = $this->get($field);
            }
        }
        return $data;
    }

    /**
     * Get modified fields.
     * Converter are not affect here.
     *
     * @return [type] [description]
     */
    function getModified() 
    {
        $modified = array();

        if (is_array($this->_data)) 
        {
            $this->_data = array();
        }

        foreach ($this->_data as $field => $data) 
        {
            if (is_array($data) and array_key_exists('isModified', $data) and $data['isModified'] === true) 
            {
                $f = $this->getField($field);
                if (!$field) 
                {
                    continue;
                }

                $modified[$field] = array(
                    is_callable($f->renderer) ? call_user_func_array($f->renderer, array($data['oldRawValue'], $this)) : null,
                    $data['value'],
                );

            }
        }

        return $modified;
    }

    /**
     * Cancel each modified field value.
     * On `create` mode, values will be returned on init/passed values when create.
     * On `load` mode, init value are from the database.
     *
     *      $model = new Roulette(array(
     *          'fields'=>array('id','name')
     *      ));
     *      $record = $model->create(array(
     *         'name'=>'John'
     *      ));
     *      $record->get('name'); // return `John`
     *      $record->set('name','Doe');
     *      $record->get('name'); // return `Doe`
     *
     *      $record->revert(); // return `John`
     *      $record->get('name'); // return `John`, be `John` again
     *
     *      // how about id? id doesnt have value on first init
     *      $record->get('id'); // return NULL
     *      $record->set('id',123);
     *      $record->revert();
     *      $record->get('id'); // NULL again
     *
     *      // first init value also work on `load` mode
     *      // for example we have record in database kinda array('id'=>99, 'name'=>'Some Name')
     *      $record = $model->load(99);
     *      $record->get('name'); // return `Some Name`
     *      $record->set('name','My Name');
     *      $record->get('name'); // return `My Name`
     *      $record->revert()->get('name'); // return `Some Name`
     *
     *      // we could revert on custom fields only
     *      // still using $record from load
     *      $record->set(array(
     *          'id'=>'100', // now id has value 100
     *          'name'=>'Your Name' // now name is 'Your Name'
     *      ));
     *      $record->revert('name');
     *      $record->get('name'); // return `Some Name`
     *      $record->get('id'); // return 100
     *
     * @return Roulette
     */
    function revert($fields = null) 
    {
        if (!is_array($fields) and !is_object($fields)) 
        {
            $fields = array($fields);
        }

        foreach ($fields as $i => $field) 
        {
            $f = $this->getField($field);
            if (!$f) 
            {
                continue;
            }

            $this->_data[$f->name]['rawValue'] = $this->_data[$f->name]['oldRawValue'];
            $this->_data[$f->name]['value'] = is_callable($f->renderer) ? call_user_func_array($f->renderer, array($this->_data[$f->name]['rawValue'], $this)) : $this->_data[$f->name]['rawValue'];
        }

        return $this;
    }

    /**
     * Save modified fields value into database.
     * Model will fire `update` if the record hasId and id already exist in database,
     * and fire `insert` hasId is `false` or id is not exist in database.
     *
     *      $model = new Roulette(array(
     *          'table' => 'employees',
     *          'primary' => 'id',
     *          'fields' => array(
     *              array('name'=>'id', 'unique'=>true),
     *              array('name'=>'name', 'renderer'=>function($value){
     *                  return strtolower($value);
     *              }),
     *              array('name'=>'gender', preparer($value){
     *                    // gender only accept value 0 or 1, o for female and 1 for male
     *                    return (int)((boolean) $value);
     *              }, 'converter'=>function($value){
     *                  // then render when output
     *                  $value = (int)((boolean) $value);
     *                  return $value === 1 ? return 'Male' : 'Female';
     *              }),
     *              array(
     *                  'name'=>'password', 'private'=>true
     *              )
     *          )
     *      ));
     *
     *      $record = $model->create(array(
     *          'id'=>123,
     *          'name'=>'John',
     *          'age'=>27
     *      ));
     *      $record->save(); // `true` if operation is success, `false` ortherwise
     *
     *      // or using callback to manage the response
     *      $record->save(function($success, $record, $errors){
     *          if($success === true) {
     *              // do your stuff here
     *          }
     *      });
     *
     *      // or using more style like this:
     *      $record->save(array(
     *          'success'=> function($record, $errors){},
     *          'failure'=> function($record, $errors){},
     *          'callback'=> function($success, $record, $errors){}
     *      ));
     *
     *      // how if error raise ?
     *      $record->getErrors(); // array of error message, error message are defined in config `messages`
     *
     * @param  callable|array   $fn Callback, executed whenever return operation return `true` or `false`
     * @param  callable         $fn Success, executed when operation success
     * @param  callable         $fn Failure, executed when operation failed
     * @return Roulette
     */
    function save($callback = null, $success = null, $failure = null) 
    {
        $connection = $this->getConnection();
        $table = $this->getTable();
        $fields = $this->getFields();
        $primary = $this->getPrimary();
        $primary_map = $this->getField($primary)->map;

        if (is_array($callback)) 
        {
            $func = func_get_arg(0);
            $callback = array_key_exists('callback', $func) ? $func['callback'] : null;
            $success = array_key_exists('success', $func) ? $func['success'] : null;
            $failure = array_key_exists('failure', $func) ? $func['failure'] : null;
        }

        $id = $this->get($primary, true);
        $data = array();
        $success = false;

        if ($this->validate()) 
        {
            foreach ($fields as $index => $f) 
            {
                $data[$f->map] = $this->get($f->name, true);
            }

            $this->load($id, function ($record) 
            {
                if ($record) 
                {
                    $success = RouletteHelper::update($connection, $table, $data, array($primary_map = $id));
                } 
                else 
                {
                    $success = RouletteHelper::insert($connection, $table, $data);
                }

            });
        }

        $errors = $this->getErrors();

        if (is_callable($callback)) 
        {
            call_user_func_array($callback, array($success, $this, $errors));
        }

        if (is_callable($success) and $success) 
        {
            call_user_func_array($success, array($this, $errors));
        }

        if (is_callable($failure) and !$success) 
        {
            call_user_func_array($failure, array($this, $errors));
        }

        return $success;
    }

    /**
     * [destroy description]
     * @return Roulette
     */
    function destroy() 
    {
        $connection = $this->getConnection();
        $table = $this->getTable();
        $fields = $this->getFields();
        $primary = $this->getPrimary();
        $primary_map = $this->getField($primary)->map;

        if (is_array($callback)) 
        {
            $func = func_get_arg(0);
            $callback = array_key_exists('callback', $func) ? $func['callback'] : null;
            $success = array_key_exists('success', $func) ? $func['success'] : null;
            $failure = array_key_exists('failure', $func) ? $func['failure'] : null;
        }

        $id = $this->get($primary, true);
        $data = array();
        $success = false;

        $success = RouletteHelper::delete($connection, $table, array($primary_map => $id));

        $errors = $this->getErrors();

        if (is_callable($callback)) 
        {
            call_user_func_array($callback, array($success, $this, $errors));
        }

        if (is_callable($success) and $success) 
        {
            call_user_func_array($success, array($this, $errors));
        }

        if (is_callable($failure) and !$success) 
        {
            call_user_func_array($failure, array($this, $errors));
        }

        return $success;
    }

    /**
     * [load description]
     * @param  array  $condition [description]
     * @param  [type] $callback  [description]
     * @return Roulette             An instance of Roullete as a record.
     */
    function load($condition = array(), $callback = null) 
    {
        $connection = $this->getConnection();
        $table = $this->getTable();
        $fields = array_flip($this->getFieldsAttribute('map'));

        $where = $condition;
        if (!is_array($where)) 
        {
            $where = array($this->getPrimary() => $where);
        }

        $where = $this->mapFieldIn($where);

        $results = RouletteHelper::select($connection, $table, $fields, $where, null, $limit = 1, $start = 0);

        $record = count($results) ? $this->create($results[0]) : null;
        return $record;
    }

    /**
     * [create description]
     * @param  array  $data     [description]
     * @param  [type] $callback [description]
     * @return [type]           [description]
     */
    function create($data = array(), $callback = null) 
    {
        return $record = new $this($data);
    }

    /**
     * [remove description]
     * @param  [type] $id       [description]
     * @param  [type] $callback [description]
     * @return [type]           [description]
     */
    function remove($id = null, $callback = null) 
    {
        $this->load($id, function ($record) 
        {
            if ($record) 
            {
                return $record->destroy($callback);
            } 
            else 
            {
                if (is_callable($callback)) 
                {
                    call_user_func_array($callback, array(false, $record));
                }

                return false;
            }
        });
    }

    /**
     * [find description]
     * @param  [type]  $condition [description]
     * @param  [type]  $order     [description]
     * @param  integer $limit     [description]
     * @param  integer $start     [description]
     * @param  [type]  $callback  [description]
     * @return [type]             [description]
     */
    function find($condition = null, $order = null, $limit = 0, $callback = null) 
    {
        $connection = $this->getConnection();
        $table = $this->getTable();
        $fields = array_flip($this->getFieldsAttribute('map'));
        $primary = $this->getPrimary();
        $condition = is_array($condition) ? $this->mapFieldIn($condition) : array();
        $order = is_array($order) ? $this->mapFieldIn($order) : array();

        $records = RouletteHelper::select($connection, $table, $fields, $condition, $order, $limit, $start);

        return $records;
    }

    /**
     * [findOne description]
     * @param  [type] $condition [description]
     * @param  [type] $order     [description]
     * @param  [type] $callback  [description]
     * @return [type]            [description]
     */
    function findOne($condition = null, $order = null, $callback = null) 
    {
        return $this->find($condition, $order, 0, 0, $callback);
    }

    /**
     * [findAll description]
     * @param  [type]  $order    [description]
     * @param  integer $limit    [description]
     * @param  integer $start    [description]
     * @param  [type]  $callback [description]
     * @return [type]            [description]
     */
    function findAll($order = null, $limit = 0, $callback = null) 
    {
        return $this->find($order, 0, 0, $callback);
    }

    /**
     * [exists description]
     * @param  [type]  $condition     [description]
     * @param  boolean $booleanReturn [description]
     * @param  [type]  $callback      [description]
     * @return [type]                 [description]
     */
    function exists($condition = null, $booleanReturn = false, $callback = null) 
    {
        return RouletteHelper::select_count($connection, $table, $fields, $condition);
    }

    function createInstance($config = null)
    {
        return new self($config);
    }

    /**
     * Get model instance, used by method `load` `create` and
     * @return [type] [description]
     */
    function getInstance() 
    {
        if (!(self::$_instance instanceof self)) 
        {
            self::$_instance = self::createInstance();
        }

        return self::$_instance;
    }

    /**
     * Static mode for `create`
     * @see create
     * @return [type] [description]
     */
    function createRecord() 
    {
        return call_user_func_array(array(self::getInstance(), 'create'), func_get_args());
    }

    /**
     * [loadRecord description]
     * @return [type] [description]
     */
    function loadRecord() 
    {
        return call_user_func_array(array(self::getInstance(), 'load'), func_get_args());
    }

    /**
     * [removeRecord description]
     * @return [type] [description]
     */
    function removeRecord() 
    {
        return call_user_func_array(array(self::getInstance(), 'remove'), func_get_args());
    }

    /**
     * [findRecords description]
     * @return [type] [description]
     */
    function findRecords() 
    {
        return call_user_func_array(array(self::getInstance(), 'find'), func_get_args());
    }

    /**
     * [findOneRecord description]
     * @return [type] [description]
     */
    function findOneRecord() 
    {
        return call_user_func_array(array(self::getInstance(), 'findOne'), func_get_args());
    }

    /**
     * [findAllRecords description]
     * @return [type] [description]
     */
    function findAllRecords() 
    {
        return call_user_func_array(array(self::getInstance(), 'findAll'), func_get_args());
    }

    /**
     * [existsRecords description]
     * @return [type] [description]
     */
    function existsRecords() 
    {
        return call_user_func_array(array(self::getInstance(), 'exists'), func_get_args());
    }

}