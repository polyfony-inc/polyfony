<?php
declare(strict_types=1);

namespace Tests\Polyfony;

use PHPUnit\Framework\TestCase;
use Polyfony\Element as Element;

// those tests need to stricter and less dumb
final class ElementTest extends TestCase
{

	public function testLinkSafe(): void {
		$this->assertEquals(
			'<a href="http://www.domain.com" class="btn btn-primary">My &amp;button</a>',
			(new Element('a', [
				'href'	=>'http://www.domain.com',
				'class'	=>'btn btn-primary',
				'text'	=>'My &button'
			]))->__toString()
		);
	}

	public function testLinkHtml(): void {
		$this->assertEquals(
			'<a href="http://www.domain.com" class="btn btn-primary">My &button</a>',
			(new Element('a', [
				'href'	=>'http://www.domain.com',
				'class'	=>'btn btn-primary',
				'html'	=>'My &button'
			]))->__toString()
		);
	}

}
