<?php

declare(strict_types=1);

namespace Roulette\Tests;

use Roulette\Template;

class TemplateTest extends TestCase
{
    public function testSetGetMarker(): void
    {
        $this->assertTrue(method_exists(Template::class, 'setMarker'));
        $this->assertTrue(method_exists(Template::class, 'getMarker'));

        $tpl = new Template();
        $tpl->setMarker(['[', ']']);
        $this->assertEquals(['[', ']'], $tpl->getMarker(), 'change marker');

        $tpl->setMarker(['{', '}']);
        $this->assertEquals(['{', '}'], $tpl->getMarker(), 'rolling back marker');
    }

    public function testSetGetTemplate(): void
    {
        $this->assertTrue(method_exists(Template::class, 'setTemplate'));
        $this->assertTrue(method_exists(Template::class, 'getTemplate'));

        $obj = new Template();
        $obj->setTemplate('name : {name}');
        $this->assertEquals(['name : {name}'], $obj->getTemplate(), 'string');

        $obj->setTemplate(['name : {name}', 'gender : {gender}']);
        $this->assertEquals(['name : {name}', 'gender : {gender}'], $obj->getTemplate(), 'array');
    }

    public function testApply(): void
    {
        $this->assertTrue(method_exists(Template::class, 'apply'));

        $obj = new Template();
        $replacer = ['name' => 'john', 'gender' => 'male'];
        $expected = 'name : john gender : male';

        $obj->setTemplate('name : {name} gender : {gender}');
        $this->assertEquals($expected, $obj->apply($replacer), 'string');

        $obj->setTemplate(['name : {name}', ' ', 'gender : {gender}']);
        $this->assertEquals($expected, $obj->apply($replacer), 'array');

        $obj->setTemplate('name : {name} gender : {gender}');
        $this->assertEquals('name : me gender : ', $obj->apply(['name' => 'me']), 'unfulfilled');
    }

    public function testCompile(): void
    {
        $this->assertTrue(method_exists(Template::class, 'compile'));

        $replacer = ['name' => 'john', 'gender' => 'male'];
        $expected = 'name : john gender : male';

        $this->assertEquals($expected, Template::compile('name : {name} gender : {gender}')->apply($replacer), 'string');
        $this->assertEquals($expected, Template::compile(['name : {name}', ' ', 'gender : {gender}'])->apply($replacer), 'array');
    }

    public function testParse(): void
    {
        $this->assertTrue(method_exists(Template::class, 'parse'));

        $replacer = ['name' => 'john', 'gender' => 'male'];
        $expected = 'name : john gender : male';

        $this->assertEquals($expected, Template::parse('name : {name} gender : {gender}', $replacer), 'string');
        $this->assertEquals($expected, Template::parse(['name : {name}', ' ', 'gender : {gender}'], $replacer), 'array');
    }

}
