<?php

namespace Models;

use Polyfony\Exception as Exception;
use Polyfony\Database as Database;
use Polyfony\Security as Security;
use Polyfony\Locales as Locales;
use Polyfony\Element as Element;
use Polyfony\Format as Format;
use Polyfony\Config as Config;
use Polyfony\Record as Record;
use Polyfony\Router as Router;
use Polyfony\Cache as Cache;
use Polyfony\Keys as Keys;

class Emails extends \Polyfony\Emails {

	const RECIPIENTS_CLASSES = [
		'to'		=>'primary',
		'cc'		=>'secondary',
		'bcc'		=>'dark',
		'reply_to'	=>'warning'
	];

	const DEFAULT_LIMIT_ON_SEARCH = 20;

	// hard validator
	const VALIDATORS = [
		'from_email'=>FILTER_VALIDATE_EMAIL
	];

	// cleanup filters
	const FILTERS = [
	];

	 /////////////////////////////////////
	 //  ___ _____ _ _____ ___ ___       
	 // / __|_   _/_\_   _|_ _/ __|      
	 // \__ \ | |/ _ \| |  | | (__       
	 // |___/ |_/_/_\_\_| |___\___| _    
	 //  _ __  ___| |_| |_  ___  __| |___
	 // | '  \/ -_)  _| ' \/ _ \/ _` (_-<
	 // |_|_|_\___|\__|_||_\___/\__,_/__/
                                  

	// search in all records
	public static function search(
		array $matching=[],
		?int $show_only_this_many = self::DEFAULT_LIMIT_ON_SEARCH
	) :array {

		return self::_select()
				->select()
				->whereContains($matching)
				->limitTo(0, $show_only_this_many)
				->execute();

	}

	public static function getPending(
		?int $exclude_older_than = null
	) :array {

		// select all email that have not been send yet
		$pending = self::_select()
			->whereEmpty(['sending_date'])
			->orderBy(['creation_date'=>'ASC']);

		// if we asked to exclude old emails
		return $exclude_older_than ? 
			// add a greater than condition
			$pending->whereHigherThan()->execute() : 
			// execute as is
			$pending->execute();

	}

	// send all pending emails
	public static function sendAllPending(
		?int $exclude_older_than = null
	) :int {

		// we haven't sent anything yet
		$total_sent = 0;
		// for each pending email
		foreach(self::getPending($exclude_older_than) as $email) {
			// send it and increment the counter if successful
			$total_sent += (int) $email->send()->isSent();
		}
		// return the number of successfuly sent email
		return $total_sent;

	}

	 /////////////////////////////////////
	 //   ___  ___    _ ___ ___ _____    
	 //  / _ \| _ )_ | | __/ __|_   _|   
	 // | (_) | _ \ || | _| (__  | |     
	 //  \___/|___/\__/|___\___| |_|_    
	 //  _ __  ___| |_| |_  ___  __| |___
	 // | '  \/ -_)  _| ' \/ _ \/ _` (_-<
	 // |_|_|_\___|\__|_||_\___/\__,_/__/
                                  

	public function getRecipientsLinks(
		?int $truncate_to = null
	) :string {

		$links = [];

		// for each type of recipients
		foreach(
			self::RECIPIENTS_CLASSES as 
			$recipient_type => $class
		) {

			foreach(
				$this->getRecipients($recipient_type) as 
				$email => $name
			) {

				// create a new clickable label
				$links[] = new Element('a', [
					'href'	=> 'mailto:'.$email,
					'class'	=> 'badge badge-'.$class,
					'text'	=> $truncate_to ? 
						Format::truncate($email, $truncate_to) : 
						$email
				]);

			}

		}

		// glue the links together and convert them to plaintext
		return implode(' ', $links);

	}

	public function getSendingDateBadge() :?Element {

		return $this->get('sending_date') ? 
			new Element('span', [
				'html'	=> 
					'<span class="fa fa-check"></span> ' . 
					$this->get('sending_date'),
				'class'	=> 'text-success'
			]) : 
			new Element('span', [
				'html'	=> 
					'<span class="fa fa-stopwatch"></span> ' . 
					'Not sent',
				'class'	=> 'text-warning'
			]);

	}

	// get the url for that object, depending on the user level
	public function getUrl(
		?string $action = 'edit',
		?int $id_level = 1
	) :string {

		return Router::reverse(
			'emails', 
			[
				'id'		=>$this->get('id'),
				'action'	=>$action 
			]
		);

	}

}

?>

