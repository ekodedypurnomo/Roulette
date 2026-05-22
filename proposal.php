<?php
///////////
// CASES //
///////////

// laravel
DB::query();
Student::findFirst();
Student::find();
// CI
$this->model_student->get();
$this->model_student->getAll();
// phalcon
Student::find();
// Yii
$this->db->query();

// laravel relation
$todd = Student::where('name','=','todd')->findFirst();
$faculty = $todd->getFaculty(); // predefined query
$parents = $todd->getParents(); // predefined query

// Roulette overview
$todd = Student::create(['name'=>'todd']); // or
$todd = Student::load(['name'=>'todd']);
$faculty = $todd->lookup('faculty'); // no need to do query database
$parents = $todd->lookup('parents'); // no need to do query database

// 
// ?????
// 


///////////
// SETUP //
///////////
use Roulette\Model;

use App\model\Rak; // Surat has one Rak
use App\model\Jenis; // Surat has one Jenis
use App\model\Staf; // surat hasmany staf (penerima), surat hasone staf (pembuat)

class Surat extends Model
{
	static $prototype;
}

Surat::prototype([
	'table'=>'surat', // source table
	'primary'=>'surat_id',
	'fields'=>[
		[
			'name' => 'surat_id',
			'updateable'=> false,
			'nullable'=> false,
			'renderer' => function(){},
			'converter' => function(){}
		],
		'surat_no',
		'surat_rak' => [
			'type'=>'string',
			'validation'=>[
				'maxlength'=>36,
				'minlength'=>36
			]
		],
		'surat_pembuat',
		'surat_penyetuju'
	],
	'associations'=>[
		[
			'name'=>'rak',
			'type'=>'hasOne', // hasOne|hasMany
			'model'=>Rak::class,
			'field'=>'surat_rak'],
		[
			'name'=>'jenis',
			'type'=>'HASONE',
			'model'=>Jenis::class,
			'field'=>'surat_jenis'],
		[
			'name'=>'pembuat',
			'type'=>Association::HASONE,
			'model'=>self::class,
			'field'=>'surat_pembuat'],
		[
			'name'=>'penerima',
			'type'=>Association::HASMANY,
			'model'=>Staf::class]
	],
	'views'=>[
		[
			'name'=>'full',
			'source'=>'v_surat',
			'associations'=>[
				['association'=>'rak', 'identifier'=>'/^(rak_)/'],
				['association'=>'pembuat', 'identifier'=>[
					'by'=>'/^(pembuat_)/',
					'mask'=>'staf_'
				]],
				['association'=>'jenis', 'identifier'=>[
					'map'=>[
						'jenis_id'=>'surat_jenis_id', 
						'jenis_nama'=>'surat_jenis_nama'
					]
				]],
				['association'=>'pembuat', 'identifier'=>function(field){
					return in_array(field, ['penyetuju_id', 'penyetuju_nama']);
				}]
			]
		],
		[
			'name'=>'withRak',
			'source'=>'v_surat',
			'associations'=>[
				['association'=>'rak', 'identifier'=>'/^(rak_)/']
			]
		]
	]
]);

/////////////////////////
// WORKING WITH RECORD //
/////////////////////////
function ()
{
	// create
	$surat123 = new Surat(['surat_no'=>'id123']);
	$surat123 = Surat::create(['surat_no'=>'id123']);
	$surat123 = Surat::create()->set(['surat_no'=>'id123']);
	$surat123 = Surat::create();
		$surat123->set('surat_no','id123');

	// saving
	$surat123->save(); // true/false
	$surat123->save(function($operation)
	{
		if($operation->isSuccess())
		{
			echo "success";
		}else
		{
			echo $operation->getMessages();
		}
	});

	// destroy
	$surat123->destroy(function($operation){}); // true / false

	// load
	// could be by an id, or it custom field conditions
	$surat123 = Surat::load('id123'); // record
	$surat123 = Surat::load(['surat_id'=>'id123']); // record
	// $raksurat = Rak::load($surat123['surat_rak']);

	// find associated
	$rak = $surat123->lookup('rak');  // $surat123->associate('rak')->lookup();
	$rak = $surat123->getRak();
	$rakId = $rak->get('rak_nama');

	$surat123->lookup('rak')->get('rak_nama'); // record
	$surat123->lookup('rak')->rak_nama; // record

	$penerimaStore = $surat123->lookup('penerima'); // Roulette/Store
	$penerimaData = $penerimaStore->getData();

	$surat123->lookup('penerima')->getData(); // collection
	$surat123->getPenerima();
}

//////////////////////////
// WORKING WITH RECORDS //
//////////////////////////
function ()
{
	// sol 1, merge model or query on each, old school style
	$surats = Surat::find(['pembuat'=>'rahmat'],/*sorter*/,/*group*/,/*having*/,/*limit*/); // collection
	$data = [];
	$surats->each(function($r, $index){
		$data[$index] = array_merge(
			$r->getData(), 
			$r->lookup('rak')->getData()
			$r->lookup('jenis')->getData()
			$r->lookup('penerima')->getData()
			);
	});
	response()->json($data);

	// sol 2, view parser, roullete way
	$suratsfull = Surat::view('full')->find()->getData();
		$rak = $suratsfull[0]->lookup('rak'); // use cache / data from view
		$jenis = $suratsfull[0]->lookup('jenis'); // use cache / data from view

	// sol 2.1, view builder
	/**
	 * from database we will got these fields: 
	 * 		surat_id, surat_no, surat_rak, surat_jenis, surat_pembuat, surat_penyetuju
	 *   	rak_id, rak_nama, 
	 *    	//jenis_id as surat_jenis_id, jenis_nama as surat_jenis_nama
	 *    	staf_id as pembuat_id, staf_nama as pembuat_nama
	 *    	staf_id as penyetuju_id, staf_nama as penyetuju_nama
	 * in 1 row
	 */
	
	$d = Surat::find();// equivalent with this bellow
	$d = Surat::view()->find();
	$d = Surat::view()->load('1');

	$d = Surat::view('withRak')->load('1');
	$d->lookup('rak')->getData(); // no need to load
	$d->lookup('jenis')->getData(); // load from database, jenis_id = 1

	$d = Surat::view('withRak')->load('2');
	$d->lookup('jenis')->getData(); // cache, jenis_id = 1

	Surat::view()
		->order('name','asc')
		->order('id')
		->where('a = 1')
		->where('a','1')
		->where('a','<= 1')
		->where(function($builder){
			if($surat->isDestroyed())
			{
				$builder->where('surat_isdeleted', true);
			}
		})
		->group('faculty')
		->having(['1'='1'])
		->get();

	Surat::find([],[],[],[]); // old way


	// sol 3, view generator
	// comming soon

	$surat123 = Surat::load('id123');
	$surat123->getDataView('incRak')->get('rak_nama');
}