<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class RouletteTest extends Controller
{
	function index()
	{
		require('../RouletteUnittest.php');
	    $unittest = new \RouletteUnittest;
	    $unittest->test();
	    $unittest->printResult();
	}
}
