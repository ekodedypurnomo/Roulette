<?php

declare(strict_types=1);

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
     * @property    string|array|null $template String template
     * @access      protected
     */
    protected string|array|null $template = null;

    /**
     * Marker is default caracter to parsing
     *
     * @property    array $marker String template default is { } pairs
     * @access      protected
     */
    protected array $marker = ['{', '}'];

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
     * @param  string|array|null $template
     * @param  mixed $data
     * @return string
     */
    public static function parse(string|array|null $template = null, mixed $data = null): string
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
     * @param  string|array|null $template
     * @return static
     */
    public static function compile(string|array|null $template = null): static
    {
        return new static(['template' => $template]);
    }

    /**
     * Constructor, create in instance of this class.
     *
     * @param string|array|null $config A set of configuration
     */
    function __construct(string|array|null $config = null)
    {
        if (is_string($config)) {
            $config = ['template' => $config];
        }
        if (is_array($config)) {
            if (array_key_exists('template', $config)) $this->template = $config['template'];
            if (array_key_exists('marker', $config))   $this->marker   = $config['marker'];
        }
    }

    /**
     * Adding a new character for parsing
     *
     *      Example:
     *      //load the model first
     *      $data = \Roulette\Template->setMarker(array('a','b'));
     *
     * @param array|null $marker
     * @return static
     */
    function setMarker(?array $marker = null): static
    {
        if (is_array($marker)) {
            if (isset($marker[0])) $this->marker[0] = $marker[0];
            if (isset($marker[1])) $this->marker[1] = $marker[1];
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
     * @return array
     */
    function getMarker(): array
    {
        return $this->marker;
    }

    /**
     * Append the template with params
     *
     * @param string|array|null $template
     * @return static
     */
    function setTemplate(string|array|null $template = null): static
    {
        if (!is_array($template)) {
            $template = [$template];
        }
        $this->template = $template;
        return $this;
    }

    /**
     * Take an existing template
     * @return string|array|null
     */
    function getTemplate(): string|array|null
    {
        return $this->template;
    }

    /**
     * Replace value from template with value from input
     *
     * @param  string|null $str
     * @param  array|null  $replacement
     * @return string|null
     */
    protected function mark(?string $str = null, ?array $replacement = null): ?string
    {
        if (is_string($str) && is_array($replacement)) {
            foreach ($replacement as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $str = str_replace((string) $this->marker[0] . $key . $this->marker[1], (string) $value, $str);
                }
            }
        }
        return $str;
    }

    /**
     * Using the results of the input replace the contents of template values
     *
     * @param  mixed $replacement
     * @return string
     */
    function apply(mixed $replacement = null): string
    {
        $str = $this->template;
        $compiled = "";

        if (is_array($str))
        {
            $compiledArray = [];

            foreach ($str as $key => $value) {
                $compiledArray[] = $this->mark($value, $replacement);
            }

            $compiled = implode('', $compiledArray);
        }
        else
        {
            $compiled = (string) $this->mark($str, $replacement);
        }

        // need to clear variables
        $clearRe = '#\\'.$this->marker[0].'(.*?)\\'.$this->marker[1].'#';
        $compiled = (string) preg_replace_callback($clearRe, function() {
            return "";
        }, $compiled);

        return $compiled;
    }
}
