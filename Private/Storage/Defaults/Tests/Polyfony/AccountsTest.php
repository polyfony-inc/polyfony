<?php
declare(strict_types=1);

namespace Tests\Polyfony;

use PHPUnit\Framework\TestCase;
use Models\Accounts as Accounts;
use Polyfony\Security as Security;

// those tests need to stricter and less dumb
final class AccountsTest extends TestCase
{
	const default_login 		= 'root@local.domain';
	const default_password 		= 'toor';
	const default_modules 		= ['create-this','delete-that'];
	const incorrect_id_level 	= 7;
	const incorrect_login 		= 'foobar';

	protected function setUp(): void {
		// purge the database
		Accounts::_delete()->execute();
		// create fixtures
		Accounts::create([
			'id'			=>1,
			'login'			=>self::default_login,
			'password'		=>Security::getPassword(self::default_password),
			'modules_array'	=>[],
			'is_enabled'	=>1,
			'id_level'		=>1,
			'creation_date'	=>time()
		]);
	}

	protected function tearDown(): void {


	}

	public function testSetPassword(): void {
		$this->assertEquals(
			Security::getPassword(self::default_password),
			(new Accounts(1))
				->get('password')
		);
	}

	public function testSetIdLevel(): void {
		$this->assertEquals(
			5,
			(new Accounts(1))
				->set(['id_level'=>5])
				->get('id_level')
		);
	}

	public function testSetWrongIdLevel(): void {
		$this->expectException(\Polyfony\Exception::class);
		(new Accounts(1))
				->set(['id_level'=>self::incorrect_id_level]);
	}

	public function testSetModules(): void {
		$this->assertEquals(
			self::default_modules,
			(new Accounts(1))
				->set(['modules_array'=>self::default_modules])
				->get('modules_array')
		);
	}

	public function testSetLogin(): void {
		$this->assertEquals(
			self::default_login,
			(new Accounts(1))
				->set(['login'=>self::default_login])
				->get('login', true)
		);
	}

	public function testSetWrongLogin(): void {
		$this->expectException(\Polyfony\Exception::class);
		(new Accounts(1))
				->set(['login'=>self::incorrect_login]);
	}

	public function testNonNullableColumnsLogin(): void {
		$this->expectException(\Polyfony\Exception::class);
		Accounts::create([
			'id'			=>1,
			'login'			=>'',
			'password'		=>Security::getPassword(self::default_password),
			'modules_array'	=>[],
			'is_enabled'	=>1,
			'id_level'		=>1,
			'creation_date'	=>time()
		]);
	}

	public function testNonNullableColumnsPassword(): void {
		$this->expectException(\Polyfony\Exception::class);
		Accounts::create([
			'id'			=>1,
			'login'			=>self::default_login,
			'password'		=>'',
			'modules_array'	=>[],
			'is_enabled'	=>1,
			'id_level'		=>1,
			'creation_date'	=>time()
		]);
	}

	public function testNonNullableColumnsIdLevel(): void {
		$this->expectException(\Polyfony\Exception::class);
		Accounts::create([
			'id'			=>1,
			'login'			=>self::default_login,
			'password'		=>Security::getPassword(self::default_password),
			'modules_array'	=>[],
			'is_enabled'	=>1,
			'id_level'		=>'',
			'creation_date'	=>time()
		]);
	}

	public function testNonNullableColumnsIsEnabled(): void {
		$this->expectException(\Polyfony\Exception::class);
		Accounts::create([
			'id'			=>1,
			'login'			=>self::default_login,
			'password'		=>Security::getPassword(self::default_password),
			'modules_array'	=>[],
			'is_enabled'	=>'',
			'id_level'		=>1,
			'creation_date'	=>time()
		]);
	}

	public function testHasItsValidityExpired(): void {
		$this->assertFalse(
			(new Accounts(1))
				->hasItsValidityExpired()
		);
	}

	public function testHasThisPassword(): void {
		$this->assertTrue(
			(new Accounts(1))
				->hasThisPassword(self::default_password)
		);

		
	}

}

?>
