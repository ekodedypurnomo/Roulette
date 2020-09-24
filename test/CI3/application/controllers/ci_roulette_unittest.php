<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('../../Roulette_unittest.php');
use Roulette\Proxy;
use Roulette\tunnels\CI3\Tunnel;

class CI_roulette_unittest extends CI_Controller{

    function __construct(){
        parent::__construct();
        $this->load->database();
    }
    function index(){
        \Proxy::tunnel(new \Tunnel($this->db));
        $unittest = new \Roulette_unittest;
        $unittest->test();
        $unittest->printResult();
    }
}