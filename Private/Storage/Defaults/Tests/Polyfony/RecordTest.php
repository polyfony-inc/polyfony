<?php
declare(strict_types=1);

namespace Tests\Polyfony;

use PHPUnit\Framework\TestCase;
use Models;

// those tests need to stricter and less dumb
final class RecordTest extends TestCase
{
	protected function setUp(): void {
		// purge the database

		// create fixtures

	}

	public function testSetString(): void {
		
	}

	public function testSetInteger(): void {
		
	}

	public function testSetFloat(): void {
		
	}

	// public function testPush(): void {
	// 	$this->assertEquals(
 //            ['create-this','delete-that',['fuck-it']],
 //            (new Models\Accounts(1))
 //            	->push('modules_array', ['fuck-it'])
 //            	->get('modules_array')
 //        );
	// }

	public function testSetArray(): void {
		$this->assertEquals(
            ['mod_1','mod_2'],
            (new Models\Accounts(1))
            	->set(['modules_array'=>['mod_1','mod_2']])
            	->get('modules_array')
        );
	}

	public function testSetSize(): void {
		
	}

	public function testSetDate(): void {
		$this->assertEquals(
            'XX/XX/XXXX',
            (new Models\Emails(1))
            	->set(['creation_date'=>1400000])
            	->get('creation_date')
        );
	}

	public function testGetHtmlSafe(): void {
		
	}

	public function testClass(): void {
		
	}

	public function testIncrement(): void {
		$this->assertEquals(
            (new Models\Accounts(1))
            	->get('creation_date',true)+1,
            (new Models\Accounts(1))
            	->increment('creation_date')
            	->get('creation_date',true)
        );
	}

	public function testDecrement(): void {
		$this->assertEquals(
            0,
            (new Models\Accounts(1))
            	->decrement('id_level')
            	->get('id_level')
        );
	}

	public function testEmailValidator(): void {
		// FILTER_VALIDATE_EMAIL
		$this->expectException(\Polyfony\Exception::class);
        (new Models\Accounts)->set(['login'=>'root']);
        $this->assertEquals(
            (new Models\Accounts(['login'=>'julien.arnaud@sib-retail.com']))
            	->get('login', true),
            (new Models\Accounts(1))->get('login', true)
        );
	}

	public function testIpValidator(): void {
		// FILTER_VALIDATE_IP
		$this->assertEquals(
            '192.168.0.1',
            (new Models\Accounts(1))
            	->set(['last_login_origin'=>'192.168.0.1'])
            	->get('last_login_origin')
        );
	}

	public function testIntegerValidator(): void {
		// FILTER_VALIDATE_INT
	}

	public function testArrayValidator() :void {
		$this->expectException(\Polyfony\Exception::class);
        (new Models\Accounts)
        	->set(['id_level'=>76]);
	}

	public function testCaplockFilter(): void {
		
	}

	public function testPhoneFilter(): void {
		
	}

	public function testSlugFilter(): void {
		
	}

	public function testNumericFilter(): void {
		
	}

	public function testIntegerFilter(): void {
		
	}

}

?>
