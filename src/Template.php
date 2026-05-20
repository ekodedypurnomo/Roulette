<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;
use Roulette\Base;

/**
 * Simple string template parser. (Experimental)
 * 
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class Template extends Base
{
    /**
     * Template string unformated (the template).
     *
     * @property    Array $template String template 
     * @access      protected
     */
    protected $template = null;
    
    /**
     * Marker is default caracter to parsing
     *
     * @property    Array $template String template default is { } pairs 
     * @access      protected
     */
    protected $marker = array('{', '}');

    /**
     * Merge template with the data
     *
     *      Example: 
     *      $data = array('name'=>'john','gender'=>'male'); //data dummy
     *      $result_should = 'name : '.$data['name'].' gender : '.$data['gender'];
     *
     *      #String
     *      $result = \Roulette\Template::parse('name : {name} gender : {gender}',$data);
     *
     *      #array
     *      $result = \Roulette\Template::parse(array('name : {name}',' ','gender : {gender}'),$data);
     *      
     * 
     * @param  Array $template
     * @param  Array $data
     * @return Array
     */
    public static function parse($template = null, $data = null)
    {
        return static::compile($template)->apply($data);
    }

    /**
     * Changing the template becomes static
     *
     *      Example:
     *      $data = array('name'=>'john','gender'=>'male'); //data dummy
     *      $result_should = 'name : '.$data['name'].' gender : '.$data['gender'];
     *
     *      #string
     *      $result = \Roulette\Template::compile('name : {name} gender : {gender}')->apply($data);
     *
     *      #array
     *      $result = \Roulette\Template::compile(array('name : {name}',' ','gender : {gender}'))->apply($data);
     * 
     * @param  String $template
     * @return Array
     */
    public static function compile($template = null)
    {
        return $tpl = new static(array(
            'template'=>$template
        ));
    }

    /**
     * Constructor, create in instance of this class.
     * 
     * @param Array $config A set of configuration
     * @return Roulette\Template 
     */
    function __construct($config = null)
    {
        if (is_string($config)) {
            $config = array('template' => $config);
        }
        if (is_array($config))
        {
            if (array_key_exists('template', $config)) $this->template = $config['template'];
            if (array_key_exists('marker', $config)) $this->marker = $config['marker'];
        }
    }

    /**
     * Adding a new character for parsing
     *
     *      Example:
     *      //load the model first
     *      $data = \Roulette\Template->setMarker(array('a','b'));
     * 
     * @param Array $marker
     */
    function setMarker($marker = null)
    {
        if (is_array($marker)) {
            if (isset($marker[0]))
                $this->marker[0] = $marker[0];
            if (isset($marker[1]))
                $this->marker[1] = $marker[1];
        }
        return $this;
    }

    /**
     * Taking characters for parsing
     *
     *      Example:
     *      //load the model first
     *      $data = \Roulette\Template->setMarker(array('a','b'));
     * 
     * @return Array
     */
    function getMarker()
    {
        return $this->marker;
    }

    /**
     * Append the template with params
     * 
     * @param Array $template
     */
    function setTemplate($template = null)
    {
        if (!is_array($template)){
            $template = array($template);
        }
        if (is_array($template)) {
            $this->template = $template;
        }
        return $this;
    }

    /**
     * Take an existing template
     * @return Array
     */
    function getTemplate()
    {
        return $this->template;
    }

    /**
     * Replace value from template with value from input
     * 
     * @param  String $str
     * @param  Array  $replacement
     * @return String
     */
    protected function mark($str = null, $replacement = null)
    {
        if (is_string($str) and is_array($replacement)) {
            foreach ($replacement as $key => $value) {
                if ( is_string($value) or is_numeric($value) ) {
                    $str = str_replace( (string) $this->marker[0] . $key . $this->marker[1], $value, $str);
                }
            }
        }
        return $str;
    }

    /**
     * Using the results of the input replace the contents of template values
     * 
     * @param  Array $replacement
     * @return Array
     */
    function apply($replacement = null)
    {
        $str = $this->template;

        $compiled = "";

        if (is_array($str)) 
        {
            $compiledArray = array();

            foreach ($str as $key => $value) {
                $compiledArray[] = $this->mark($value, $replacement);
            }
    
            $compiled = implode('', $compiledArray);
        } 
        else 
        {
            $compiled = $this->mark($str, $replacement);
        }

        // need to clear variables
        $clearRe = '#\\'.$this->marker[0].'(.*?)\\'.$this->marker[1].'#';
        $compiled = preg_replace_callback($clearRe, function(){
            return "";
        }, $compiled);
    
        return $compiled;
    }
}