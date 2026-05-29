<?php

declare(strict_types=1);

namespace Roulette\Tests\Model\Field;

use Roulette\Model\Field\Field;
use Roulette\Model\Field\Validation;
use Roulette\Tests\TestCase;
use Roulette\Validator\Email;
use Roulette\Validator\Minlength;

class ValidationTest extends TestCase
{
    public function testConstruct(): void
    {
        $field = new Field(['name' => 'email']);
        $v = new Validation($field);
        $this->assertInstanceOf(Validation::class, $v);
    }

    public function testGetField(): void
    {
        $field = new Field(['name' => 'email']);
        $v = new Validation($field);
        $this->assertSame($field, $v->getField());
    }

    public function testSetField(): void
    {
        $field1 = new Field(['name' => 'email']);
        $field2 = new Field(['name' => 'name']);
        $v = new Validation($field1);
        $v->setField($field2);
        $this->assertSame($field2, $v->getField());
    }

    public function testValidatePasses(): void
    {
        $field = new Field(['name' => 'email']);
        $v = new Validation($field);
        $v->addValidator(new Email());
        $messages = $v->validate('user@example.com');
        $this->assertEmpty($messages, 'valid email passes');
    }

    public function testValidateFails(): void
    {
        $field = new Field(['name' => 'email']);
        $v = new Validation($field);
        $v->addValidator(new Email());
        $messages = $v->validate('not-an-email');
        $this->assertCount(1, $messages, 'invalid email fails');
        $this->assertIsString($messages[0]);
    }

    public function testValidateMultipleValidators(): void
    {
        $field = new Field(['name' => 'password']);
        $v = new Validation($field);
        $v->addValidator(new Minlength(8));
        $v->addValidator(new Minlength(12));
        $messages = $v->validate('short');
        $this->assertCount(2, $messages, 'both validators fail');
    }

    public function testValidateEmptyValidators(): void
    {
        $field = new Field(['name' => 'notes']);
        $v = new Validation($field);
        $messages = $v->validate('anything');
        $this->assertEmpty($messages, 'no validators = no errors');
    }
}
