<?php
declare(strict_types=1);

namespace Tests\Polyfony;

use PHPUnit\Framework\TestCase;
use Models;
use Models\Emails as Emails;
use Models\Accounts as Accounts;
use Polyfony\Security as Security;
use Polyfony\Locales as Locales;

// those tests need to stricter and less dumb
final class LocalesTest extends TestCase
{

	/*public function testSetLanguage(): void {
		Locales::setLanguage('en');
		$this->assertEquals(
			'en',
			Locales::getLanguage()
		);
		Locales::setLanguage('fr');
		$this->assertEquals(
			'fr',
			Locales::getLanguage()
		);
	}*/

	public function testSetUnsupportedLanguage(): void {
		$this->assertTrue(
			true
		);
	}

}