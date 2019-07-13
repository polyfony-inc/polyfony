<?php
declare(strict_types=1);

namespace Tests\Polyfony;

use PHPUnit\Framework\TestCase;
use Polyfony\Form as Form;

// those tests need to stricter and less dumb
final class FormTest extends TestCase
{

	public function testInput(): void {
		$this->assertEquals(
			'<input type="input" name="anInput" value="" class="form-control" />',
			(Form::input('anInput', null, ['class'=>'form-control']))->__toString()
		);
	}

	public function testSelect(): void {
		$this->assertEquals(
			'<select name="aSelect"><option value="foo">bar</option><option value="bar">foo</option></select>',
			(Form::select(
				'aSelect',
				['foo'=>'bar','bar'=>'foo']
			))->__toString()
		);
	}

	public function testSelectedSelect(): void {
		$this->assertEquals(
			'<select name="aSelect"><option value="foo">bar</option><option value="bar" selected="selected">foo</option></select>',
			(Form::select(
				'aSelect',
				['foo'=>'bar','bar'=>'foo'],
				'bar'
			))->__toString()
		);
	}

	public function testCheckedCheckbox(): void {
		$this->assertEquals(
			'<input type="checkbox" name="aCheckbox" checked="checked" />',
			(Form::checkbox('aCheckbox', true))->__toString()
		);
	}
	public function testImplicitUncheckedCheckbox(): void {
		$this->assertEquals(
			'<input type="checkbox" name="aCheckbox" />',
			(Form::checkbox('aCheckbox'))->__toString()
		);
	}

	public function testExplicitUncheckedCheckbox(): void {
		$this->assertEquals(
			'<input type="checkbox" name="aCheckbox" class="form-control" />',
			(Form::checkbox('aCheckbox', false, ['class'=>'form-control']))->__toString()
		);
	}

	public function testTextarea(): void {
		$this->assertEquals(
			'<textarea type="textarea" name="aTextarea" class="form-control">foobar</textarea>',
			(Form::textarea(
				'aTextarea', 
				'foobar', 
				['class'=>'form-control']
			))->__toString()
		);
	}

}
