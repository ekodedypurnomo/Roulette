<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Roulette_base_test extends Roulette_unittest_model {

    public $title = 'Roulette\Base';

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        return $this->test();
    }

    protected function test_is_roulette_base(){
        $test = array();
        
        $obj = new \Roulette\Base;
        $test[] = (is_object($obj));
        $test[] = is_a($obj, '\Roulette\Base');
        
        return $test;
    }

    protected function test_configure(){
        $test = array();

        $testdata = array(
            'first_name'=>'john',
            'last_name'=>'doe'
        );

        $obj = new \Roulette\Base;
        $obj->configure($testdata);
        $test[] = ( $obj->get_config('first_name') == $testdata['first_name'] );
        
        return $test;
    }

    protected function test_reconfigure(){
        $test = array();

        $testdata = array(
            'first_name'=>'john',
            'last_name'=>'doe'
        );
        $testdata_2 = array(
            'first_name'=>'john',
            'last_name'=>'doe'
        );

        $obj = new \Roulette\Base($testdata);
        $obj->reconfigure($testdata_2);
        $test[] = ( $obj->get_config_init() == $testdata );
        
        return $test;
    }

    protected function test_get_config(){
        $test = array();

        $obj = new \Roulette\Base;
        $obj->set_config('makan',5);
        $test[] = ( $obj->get_config('makan') === 5 );
        
        return $test;
    }

    protected function test_set_config(){
        $test = array();

        $obj = new \Roulette\Base;
        $val = rand ( 1 , 100 );
        $obj->set_config('age', $val);
        $test[] = ( $obj->get_config('age') == $val );
        
        return $test;
    }

    protected function test_config(){
        $test = array();

        $obj = new \Roulette\Base;
        $val = rand ( 1 , 100 );
        $obj->config('age', $val);
        $test[] = ( $obj->config('age') == $val );
        
        return $test;
    }

    protected function test_enable(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->enabled('walk') === false );
        
        $obj->enable('walk');
        $test[] = ( $obj->enabled('walk') === true );

        $obj->enable('walk', false);
        $test[] = ( $obj->enabled('walk') === false );

        $obj->enable('walk', true);
        $test[] = ( $obj->enabled('walk') === true );

        return $test;
    }

    protected function test_enable_config(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->enabled('walk') === false );
        
        $obj->enable_config('walk');
        $test[] = ( $obj->enabled('walk') === true );

        $obj->enable_config('walk', false);
        $test[] = ( $obj->enabled('walk') === false );

        $obj->enable_config('walk', true);
        $test[] = ( $obj->enabled('walk') === true );

        return $test;
    }

    protected function test_enabled(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->enabled('walk') === false );

        $obj->enable('walk');
        $test[] = ( $obj->enabled('walk') === true );

        $obj->enable('walk', false);
        $test[] = ( $obj->enabled('walk') === false );
        
        $test[] = ( $obj->enabled() === false );

        $test[] = ( $obj->enabled(1) === false );

        return $test;
    }

    protected function test_config_enabled(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->config_enabled('walk') === false );

        $obj->enable('walk');
        $test[] = ( $obj->config_enabled('walk') === true );
        
        $obj->enable('walk', false);
        $test[] = ( $obj->config_enabled('walk') === false );

        $test[] = ( $obj->config_enabled() === false );

        $test[] = ( $obj->config_enabled(1) === false );

        return $test;
    }

    protected function test_disable(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->disabled('walk') === true );
        
        $obj->disable('walk');
        $test[] = ( $obj->disabled('walk') === true );

        $obj->disable('walk', true);
        $test[] = ( $obj->disabled('walk') === true );

        $obj->disable('walk', false);
        $test[] = ( $obj->disabled('walk') === false );

        return $test;
    }

    protected function test_disable_config(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->disabled('walk') === true );
        
        $obj->disable_config('walk');
        $test[] = ( $obj->disabled('walk') === true );

        $obj->disable_config('walk', true);
        $test[] = ( $obj->disabled('walk') === true );

        $obj->disable_config('walk', false);
        $test[] = ( $obj->disabled('walk') === false );

        return $test;
    }

    protected function test_disabled(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->disabled('walk') === true );

        $obj->disable('walk');
        $test[] = ( $obj->disabled('walk') === true );

        $obj->disable('walk', false);
        $test[] = ( $obj->disabled('walk') === false );
        
        $test[] = ( $obj->disabled() === true );

        $test[] = ( $obj->disabled(1) === true );

        return $test;
    }

    protected function test_config_disabled(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->config_disabled('walk') === true );

        $obj->disable('walk');
        $test[] = ( $obj->config_disabled('walk') === true );

        $obj->disable('walk', false);
        $test[] = ( $obj->config_disabled('walk') === false );
        
        $test[] = ( $obj->config_disabled() === true );

        $test[] = ( $obj->config_disabled(1) === true );

        return $test;
    }

    protected function test_trigger(){
        $test = array();

        $obj = new \Roulette\Base;
        $obj->on('sing', function(){
            return 'la la la';
        });
        $test[] = ( $obj->trigger('sing') == 'la la la' );

        return $test;
    }

    protected function test_add_event(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->has_event('xxx') === false );

        $obj->add_event('xxx');
        $test[] = ( $obj->has_event('xxx') === true );

        $obj->add_event(array('yyy'));
        $test[] = ( $obj->has_event('yyy') === true );
        
        $obj->add_event('xxx', 'zzz');
        $test[] = ( $obj->has_event('zzz') === true );
        
        return $test;
    }
    
    protected function test_has_event(){
        $test = array();

        $obj = new \Roulette\Base;
        $test[] = ( $obj->has_event('xxx') === false );

        $obj->add_event('xxx');
        $test[] = ( $obj->has_event('xxx') === true );

        $obj->remove_event('xxx');
        $test[] = ( $obj->has_event('xxx') === false );

        return $test;
    }
    
    protected function test_remove_event(){
        $test = array();

        $obj = new \Roulette\Base;
        $obj->add_event('xxx','yyy','zzz');
        
        $test[] = ( $obj->has_event('xxx') === true );
        $obj->remove_event('xxx');
        $test[] = ( $obj->has_event('xxx') === false );

        $test[] = ( $obj->has_event('yyy') === true );
        $obj->remove_event(array('yyy'));
        $test[] = ( $obj->has_event('yyy') === false );

        $test[] = ( $obj->has_event('zzz') === true );
        $obj->remove_event('xxx', 'zzz');
        $test[] = ( $obj->has_event('zzz') === false );

        return $test;
    }
    
    protected function test_enable_events(){
        $test = array();

        $obj = new \Roulette\Base;

        $test[] = ( $obj->events_enabled() === true );

        $obj->enable_events(false);
        $test[] = ( $obj->events_enabled() === false );

        $obj->enable_events(true);
        $test[] = ( $obj->events_enabled() === true );

        $obj->enable_events(false);
        $test[] = ( $obj->events_enabled() === false );

        $obj->enable_events();
        $test[] = ( $obj->events_enabled() === true );

        return $test;
    }

    protected function test_events_enabled(){
        $test = array();

        $obj = new \Roulette\Base;

        $test[] = ( $obj->events_enabled() === true );

        $obj->enable_events(false);
        $test[] = ( $obj->events_enabled() === false );

        return $test;
    }

    protected function test_disable_events(){
        $obj = new \Roulette\Base;

        $test[] = ( $obj->events_disabled() === false );

        $obj->disable_events(true);
        $test[] = ( $obj->events_disabled() === true );

        $obj->disable_events(false);
        $test[] = ( $obj->events_disabled() === false );

        $obj->disable_events(true);
        $test[] = ( $obj->events_disabled() === true );

        $obj->disable_events();
        $test[] = ( $obj->events_disabled() === true );

        return $test;
    }

    protected function test_events_disabled(){
        $test = array();

        $obj = new \Roulette\Base;

        $test[] = ( $obj->events_disabled() === false );

        $obj->disable_events(false);
        $test[] = ( $obj->events_disabled() === false );

        $obj->disable_events();
        $test[] = ( $obj->events_disabled() === true );

        return $test;
    }

    protected function test_enable_event(){
        $test = array();

        $obj = new \Roulette\Base;
        
        $obj->add_event('xxx');
        $test[] = ( $obj->event_enabled('xxx') === true );

        $obj->enable_event('xxx', false);
        $test[] = ( $obj->event_enabled('xxx') === false );

        $obj->enable_event('xxx');
        $test[] = ( $obj->event_enabled('xxx') === true );

        return $test;
    }

    protected function test_event_enabled(){
        $test = array();

        $obj = new \Roulette\Base;

        $test[] = ( $obj->event_enabled() === false );
        $test[] = ( $obj->event_enabled('xxx') === false );

        $obj->add_event('xxx');
        $test[] = ( $obj->event_enabled('xxx') === true );

        $obj->enable_event('xxx', false);
        $test[] = ( $obj->event_enabled('xxx') === false );

        return $test;
    }    
    
    protected function test_disable_event(){
        $test = array();

        $obj = new \Roulette\Base;

        $obj->add_event('xxx');
        $test[] = ( $obj->event_disabled('xxx') === false );

        $obj->disable_event('xxx', false);
        $test[] = ( $obj->event_disabled('xxx') === false );

        $obj->disable_event('xxx');
        $test[] = ( $obj->event_disabled('xxx') === true );

        return $test;
    }
    
    protected function test_event_disabled(){
        $test = array();

        $obj = new \Roulette\Base;

        $test[] = ( $obj->event_disabled() === true );
        $test[] = ( $obj->event_disabled('xxx') === true );

        $obj->add_event('xxx');
        $test[] = ( $obj->event_disabled('xxx') === false );

        $obj->disable_event('xxx');
        $test[] = ( $obj->event_disabled('xxx') === true );

        return $test;
    }
    

    protected function test_add_listener(){
        $test = array();

        $obj = new \Roulette\Base;
        
        $test[] = ( $obj->has_listener('create') === false );
        $obj->add_listener('create', function(){
            return 'on create';
        });
        $test[] = ( $obj->has_listener('create') === true );

        $test[] = ( $obj->has_listener('load') === false );
        $test[] = ( $obj->has_listener('reload') === false );
        $obj->add_listener(array(
            'load' => function(){},
            'reload' => function(){}
        ));
        $test[] = ( $obj->has_listener('load') === true );
        $test[] = ( $obj->has_listener('reload') === true );
        
        return $test;
    }

    protected function test_remove_listener(){
        $test = array();

        $obj = new \Roulette\Base;

        $listener = function(){};

        $obj->add_listener('load', $listener);
        $test[] = ( $obj->has_listener('load') === true );

        $obj->remove_listener('load', $listener);
        $test[] = ( $obj->has_listener('load') === false );

        $obj->add_listener('reload', $listener);
        $test[] = ( $obj->has_listener('reload') === true );

        $obj->remove_listener(null, $listener);
        $test[] = ( $obj->has_listener('reload') === false );

        $obj->add_listener('destroy', $listener);
        $test[] = ( $obj->has_listener('destroy') === true );
        
        $obj->remove_listener(array('destroy'), $listener);
        $test[] = ( $obj->has_listener('destroy') === false );

        return $test;
    }

    protected function test_has_listener(){
        $test = array();

        $obj = new \Roulette\Base;

        $listener = function(){};

        $test[] = ( $obj->has_listener('load') === false );

        $obj->add_listener('load', $listener);
        $test[] = ( $obj->has_listener('load') === true );

        $obj->remove_listener('load', $listener);
        $test[] = ( $obj->has_listener('load') === false );

        return $test;
    }

    protected function test_clear_listener(){
        $test = array();

        $obj = new \Roulette\Base;

        $test[] = ( $obj->has_listener('load') === false );

        $obj->add_listener('load', function(){});
        $obj->add_listener('load', function(){});
        $obj->add_listener('load', function(){});
        $obj->add_listener('load', function(){});
        $obj->add_listener('load', function(){}); // `load` event should have 5 listeners
        $test[] = ( $obj->has_listener('load') === true );

        $obj->clear_listener('load');
        $test[] = ( $obj->has_listener('load') === false );

        return $test;
    }

    protected function test_reset_listeners(){
        $test = array();

        $obj = new \Roulette\Base;

        $obj->add_listener('load', function(){});
        $test[] = ( $obj->has_listener('load') === true );

        $obj->add_listener('reload', function(){});
        $test[] = ( $obj->has_listener('load') === true );

        $obj->reset_listeners();
        $test[] = ( $obj->has_listener('load') === false && $obj->has_listener('reload') === false );

        return $test;
    }

}