<?php
declare(strict_types=1);

namespace Tests\Polyfony;

use PHPUnit\Framework\TestCase;
use Models;
use Models\Emails as Emails;
use Models\Accounts as Accounts;
use Polyfony\Security as Security;

// those tests need to stricter and less dumb
final class CacheTest extends TestCase
{
	public function testSomething(): void {
		$this->assertTrue(
			true
		);
	}
}