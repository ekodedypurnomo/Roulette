<?php

declare(strict_types=1);

namespace Roulette\Tests\Model;

use Roulette\Data\Option;
use Roulette\Model\ViewOption;
use Roulette\Tests\TestCase;

class ViewOptionTest extends TestCase
{
    public function testIsOption(): void
    {
        $vo = new ViewOption();
        $this->assertInstanceOf(Option::class, $vo);
    }

    public function testIsViewOption(): void
    {
        $vo = new ViewOption();
        $this->assertInstanceOf(ViewOption::class, $vo);
    }

    public function testDefaultRender(): void
    {
        $vo = new ViewOption();
        $this->assertTrue($vo->isRender(), 'render defaults to true');
    }

    public function testRenderFalse(): void
    {
        $vo = new ViewOption(['render' => false]);
        $this->assertFalse($vo->isRender(), 'render set to false');
    }
}
