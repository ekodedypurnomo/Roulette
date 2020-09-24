<?php
if ( ! defined('ROULETTE_ROOT') ) {
    define('ROULETTE_ROOT', dirname(__FILE__) . '/Roulette/');
    Roulette_ClassLoader::register();
}

class Roulette_ClassLoader
{

    static function register() {
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }
        //  Register ourselves with SPL
        return spl_autoload_register(array(get_class(), 'load'));
    }

    static function load($classname){
        if ( class_exists($classname,FALSE) ) {
            return FALSE;
        }

        $classfilepath = ROULETTE_ROOT.$classname.'.php';

        if ((file_exists($classfilepath) === false) || (is_readable($classfilepath) === false)) {
            //  Can't load
            return FALSE;
        }

        require($classfilepath);
    }

}