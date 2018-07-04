<?php

namespace Polyfony\Profiler\HTML;

class Emails {

	private static function getRecipients($debug_data) :array {

		$recipients = [];
		$types_of_recipients = ['to'=>'dark','cc'=>'secondary','bcc'=>'secondary'];

		foreach($types_of_recipients as $recipient_type => $recipient_class) {

			foreach($debug_data['recipients'][$recipient_type] as $recipient_email => $recipient_name) {
				$recipient_container 	= new \Polyfony\Element('span', 	['class'=>'badge badge-'.$recipient_class]);
				$type 					= new \Polyfony\Element('strong', 	['text'=>$recipient_type.': ']);
				$email 					= new \Polyfony\Element('span', 	['text'=>$recipient_email]);
				$recipients[] 			= $recipient_container
					->adopt($type)
					->adopt($email);
			}
		}

		return $recipients;

	}

	private static function getEmails($stack) :array {

		$emails = [];
		$emails_count = 0;

		foreach($stack as $element) {

			if($element['user'] == 'email') {
				++$emails_count;
				$debug_data = $element['informations']['Email']->getDebugData();
				$recipients = self::getRecipients($debug_data);
				$card 					= new \Polyfony\Element('div', 		['style'=>'border-bottom:solid 1px #efefef;padding-bottom:12px;margin-bottom:12px;overflow:hidden;']);
				$title_container 		= new \Polyfony\Element('span', 	['class'=>'text-dark','style'=>'padding-right:20px;']);
				$title_prefix			= new \Polyfony\Element('strong', 	['text'=>"Email #{$emails_count} ",'class'=>'text-danger']);
				$title 					= new \Polyfony\Element('span', 	['text'=>$debug_data['subject']]);
				$parameters_container 	= new \Polyfony\Element('div', 		['style'=>'padding-top:0px']);

				$emails[$emails_count] = $card
					->adopt($title_container
						->adopt($title_prefix)
						->adopt($title))
					->adopt($parameters_container
						->adopt(implode(' ', $recipients)));
			}

		}
		return $emails;

	}

	public static function getComponent(array $data) :\Bootstrap\Modal {

		$emails = self::getEmails($data['stack']);
		$emails_modal = new \Bootstrap\Modal();
		$emails_modal
			->setTrigger([
				'html'	=>' Emails <span class="badge badge-light">'.count($emails).'</span>',
				'class'	=>'btn btn-warning',
				'style'	=>'margin-left:10px'
			], 'fa fa-envelope')
			->setTitle(	['html'=>' &nbsp;Emails'], 'fa fa-envelope')
			->setBody(	['html'=>implode(' ', $emails)]);

		return $emails_modal;

	}

}

?>
