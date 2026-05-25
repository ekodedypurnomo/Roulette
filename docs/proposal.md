# API Design Reference

Early design notes and API comparison used during initial development of Roulette.

---

## Framework Comparison

How similar ORMs handle common operations:

```php
// Laravel
$student = Student::where('name', '=', 'todd')->first();
$faculty = $student->getFaculty();

// CodeIgniter
$student = $this->model_student->get();

// Phalcon
$student = Student::findFirst();

// Roulette
$student = Student::load(['name' => 'todd']);
$faculty  = $student->lookup('faculty');
$parents  = $student->lookup('parents');
```

---

## Model Definition

```php
use Roulette\Model;

class Surat extends Model
{
    static function init()
    {
        static::prototype([
            'table'   => 'surat',
            'primary' => 'surat_id',
            'fields'  => [
                [
                    'name'       => 'surat_id',
                    'updateable' => false,
                    'nullable'   => false,
                ],
                'surat_no',
                'surat_rak' => [
                    'type'       => 'string',
                    'validation' => ['maxlength' => 36, 'minlength' => 36],
                ],
                'surat_pembuat',
                'surat_penyetuju',
            ],
            'associations' => [
                'rak'      => ['type' => 'hasOne',  'model' => Rak::class,   'foreignKey' => 'surat_rak'],
                'jenis'    => ['type' => 'hasOne',  'model' => Jenis::class, 'foreignKey' => 'surat_jenis'],
                'pembuat'  => ['type' => 'hasOne',  'model' => Staf::class,  'foreignKey' => 'surat_pembuat'],
                'penerima' => ['type' => 'hasMany', 'model' => Staf::class],
            ],
        ]);
    }
}
```

---

## Working with a Single Record

```php
// Create
$surat = new Surat(['surat_no' => 'id123']);
$surat->save();

// Load
$surat = Surat::load('id123');
$surat = Surat::load(['surat_id' => 'id123']);

// Update
$surat->set('surat_no', 'id456');
$surat->save();

// Destroy
$surat->destroy();

// Access associations
$rak      = $surat->lookup('rak');           // single record
$penerima = $surat->lookup('penerima');      // Store (collection)

$rakNama       = $rak->get('rak_nama');
$penerimaData  = $penerima->getData();
```

---

## Working with Multiple Records

```php
// Basic find
$surats = Surat::find(['pembuat' => 'rahmat']);

// With query builder
$surats = Surat::query()
    ->where(['pembuat' => 'rahmat'])
    ->orderBy(['surat_no' => 'ASC'])
    ->take(10)
    ->execute();

// Iterate and merge related data
$surats->each(function ($record) {
    $data = array_merge(
        $record->getData(),
        $record->lookup('rak')->getData(),
        $record->lookup('jenis')->getData()
    );
});
```

---

## Query Builder

```php
Surat::query()
    ->select(['surat_id', 'surat_no'])
    ->where(['active' => true])
    ->orderBy(['surat_no' => 'ASC'])
    ->groupBy(['surat_jenis'])
    ->take(20)
    ->skip(0)
    ->execute();
```
