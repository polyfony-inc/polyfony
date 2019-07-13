<?php
declare(strict_types=1);

namespace Tests\Polyfony;

use PHPUnit\Framework\TestCase;
use Models\Emails as Emails;

// those tests need to stricter and less dumb
final class QueryTest extends TestCase
{

	const first_email 	= [
			'id'			=>'1',
			'creation_date'	=>1200000000,
			'sending_date'	=>null,
			'subject'		=>'clown dudule',
			'body'			=>'Mozinor president',
			'recipients_array'=>[
				'to'		=>['foo@bar.com'],
				'cc'		=>[],
				'bcc'		=>[],
				'reply_to'	=>[]
			],
			'files_array'	=>[],
			'charset'		=>'',
			'format'		=>'text',
			'from_name'		=>null,
			'from_email'	=>'foo@bar.com'
		];
	const second_email 	= [
			'id'			=>'2',
			'creation_date'	=>1300000000,
			'sending_date'	=>null,
			'subject'		=>'Le clown dudule',
			'body'			=>'Mozinor president à vie',
			'recipients_array'=>[
				'to'		=>['foo@bar.com'],
				'cc'		=>[],
				'bcc'		=>[],
				'reply_to'	=>[]
			],
			'files_array'	=>[],
			'charset'		=>'',
			'format'		=>'text',
			'from_name'		=>'',
			'from_email'	=>'foo@bar.com'
		];
	const third_email 	= [
			'id'			=>'3',
			'creation_date'	=>1400000000,
			'sending_date'	=>1400000001,
			'subject'		=>'Le clown dudule',
			'body'			=>'Mozinor president',
			'recipients_array'=>[
				'to'		=>['foo@bar.com'],
				'cc'		=>[],
				'bcc'		=>[],
				'reply_to'	=>[]
			],
			'files_array'	=>[],
			'charset'		=>'',
			'format'		=>'text',
			'from_name'		=>'Kasimir Putin',
			'from_email'	=>'foo@bar.com'
		];

	protected function setUp(): void {
		// purge the database
		$this->tearDown();

		// create fixtures
		Emails::create(self::first_email);
		Emails::create(self::second_email);
		Emails::create(self::third_email);
	}

	protected function tearDown() :void {

		Emails::_delete()
			->execute();

	}

	public function testGet(): void {
		$this->assertInstanceOf(
			Emails::class,
			Emails::_select()
				->get()
		);
	}

	public function testWhere(): void {
		$this->assertEquals(
			'Le clown dudule',
			Emails::_select(['subject'])
				->where(['id'=>3])
				->get()
				->get('subject')
		);
	}

	public function testWhereNot(): void {
		$this->assertEquals(
			[(new Emails(1)),(new Emails(2))],
			Emails::_select()
				->whereNot(['from_name'=>'Kasimir Putin'])
				->execute()
		);
	}

	public function testWhereContains() :void {
		$this->assertEquals(
			[(new Emails(2))],
			Emails::_select()
				->whereContains(['body'=>'vie'])
				->execute()
		);
	}

	public function testWhereEndsWith() :void {
		$this->assertEquals(
			(new Emails(2)),
			Emails::_select()
				->whereEndsWith(['body'=>'à vie'])
				->get()
		);
	}

	public function testWhereStartsWith() :void {
		$this->assertEquals(
			[(new Emails(2)), (new Emails(3))],
			Emails::_select()
				->whereStartsWith(['subject'=>'Le'])
				->execute()
		);
	}

	public function testWhereNotEmpty() :void {
		$this->assertEquals(
			(new Emails(3)),
			Emails::_select()
				->whereNotEmpty(['sending_date'])
				->get()
		);
	}

	public function testWhereEmpty() :void {
		$this->assertEquals(
			[(new Emails(1)), (new Emails(2))],
			Emails::_select()
				->whereEmpty(['sending_date'])
				->execute()
		);
	}

	// this test fails
	public function testWhereNull() :void {
		$this->assertCount(
			[(new Emails(1)), (new Emails(2))],
			Emails::_select()
				->whereNull('sending_date')
				->execute()
		);
	}

	public function testWhereNotNull() :void {
		$this->assertEquals(
			(new Emails(3)),
			Emails::_select()
				->whereNotNull('sending_date')
				->get()
		);
	}

	public function testWhereGreaterThan(): void {
		$this->assertEquals(
			(new Emails(3)),
			Emails::_select()
				->whereGreaterThan(['creation_date'=>1300000000])
				->get()
		);
	}

	public function testLessThan() :void {
		$this->assertEquals(
			(new Emails(1)),
			Emails::_select()
				->whereLessThan(['creation_date'=>1250000000])
				->get()
		);
	}

	public function testOrderBy() :void {
		$this->assertEquals(
			[(new Emails(3)),(new Emails(2)),(new Emails(1))],
			Emails::_select()
				->orderBy(['creation_date'=>'DESC'])
				->execute()
		);
	}

	public function testLimitTo() :void {
		$this->assertCount(
			2,
			Emails::_select()
				->limitTo(0,2)
				->execute()
		);
	}

	public function testGroupBy() :void {
		$this->assertCount(
			2,
			Emails::_select()
				->groupBy(['subject'])
				->execute()
		);
	}

	public function testSelectSum() :void {
		$this->assertEquals(
			6,
			Emails::_select(['sum'=>'id'])
				->get()
				->get('sum_id')
		);
	}

	public function testSelectAvg() :void {
		$this->assertEquals(
			2,
			Emails::_select(['avg'=>'id'])
				->get()
				->get('avg_id')
		);
	}

}

?>
