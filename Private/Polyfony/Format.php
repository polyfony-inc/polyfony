<?php

namespace Polyfony;

class Format {

	// file or folder name that is safe for the filesystem
	public static function fsSafe($string) :string {
		// remove any symbol that does not belong in a file name of folder name
		return(str_replace(array(
				'..','/','\\',':','@','$','?','*','<','>','&','(',')','{','}',',','%','`','\'','#'), 
				'-', 
				$string
			)
		);
	}

	// string that is safe for javascript variable
	public static function jsSafe($string) :string {
		// escape all single quotes
		return(json_encode($string));
	}
	
	// screen safe
	public static function htmlSafe($string) :string {
		// just remove html entities
		return(filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	}

	// human size
	public static function size($integer, $precision=1) :string {
		// declare units
		$unit = array('b','Ko','Mo','Go','To','Po');
		// make human readable
		return(round($integer/pow(1024, ($i=floor(log($integer, 1024)))), $precision) . ' ' . $unit[$i]);
	}
	
	// relative date
	public static function date($timestamp) :string {

		// if no timestamp is provided
		if(!$timestamp) {
			// there is no conversion to do at all
			return('');
		}
		// compute the delta between now and then
		$delta 		= intval($timestamp - time());
		// get the relative duration
		$duration	= Format::duration(abs($delta));
		// use the right keyword for a future or past date
		$keyword 	= $delta > 0 ? Locales::get('dans _delta_') : Locales::get('il y\'a _delta_');
		// replace from the locale placeholder
		return(str_replace('_delta_', $duration, $keyword));

	}
	
	// neutral duration
	public static function duration($seconds, $precision = 0) :string {

		// if no timestamp is provided
		if(!$seconds) {
			// there is no conversion to do at all
			return('');
		}
		// list of deltas by period
		$deltas[1] = array(60, Locales::get('minute'));
		$deltas[2] = array(3600, Locales::get('heure'));
		$deltas[3] = array(86400, Locales::get('jour'));
		$deltas[4] = array(604800, Locales::get('semaine'));
		$deltas[5] = array(2592000, Locales::get('mois'));
		$deltas[6] = array(31104000, Locales::get('an'));
		// for very short periods
		$seconds = $seconds < 60 ? Locales::get('quelques instants') : $seconds;
		// for each time period available
		for($i = 6; $i > 0; $i--) {
			// if if we is the largest period available
			if($seconds >= $deltas[$i][0]) {
				// compute the remaining time in the current period's unit
				$duration = round($seconds/$deltas[$i][0], $precision);
				// format the relative date
				$duration = $duration . ' ' . Locales::get(
					// add a trailing plural "s"
					intval($duration) == 1 ? $deltas[$i][1] : rtrim($deltas[$i][1], 's') . 's'
				);
				// break out of the loop
				break;
			}
		}
		// return the duration
		return isset($duration) ? $duration : $seconds;

	}

	// phone number
	public static function phone($phone) {
		// remove all spaces from the number
		$phone = str_replace(' ', '', $phone);
		// remove all symbols except + and ()
		$phone = preg_replace('/[^0-9\+()]/', '', $phone);
		// reformat special symbols
		$phone = str_replace('(', ' (', $phone);
		// if the phone is of normal local format
		if(
			strlen($phone) === 10 && strpos($phone, '+') === false && 
			strpos($phone, '(') === false && strpos($phone, ')') === false
		) {
			// format the phone number by blocks of two numbers
			return(
				substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . ' ' .
				substr($phone, 4, 2) . ' ' . substr($phone, 6, 2) . ' ' . substr($phone, 8, 2));
		}
		// the phone is in international format
		else {
			// return the phone number, truncate at 31 chars (the world longest possible phone number)
			return(self::truncate($phone, 31));
		}
	}
	
	// human amount
	public static function amount($integer, $precision=1) {
		// declare units
		$unit = array('','K','M','Md','Bn','Bd');
		// make human readable
		return($integer ? trim(round($integer/pow(1000, ($i=floor(log(abs($integer), 1000)))), $precision) . ' ' . $unit[$i]) : 0);
	}

	// classic slug
	public static function slug($string) {
		// accentuated characters
		$with = preg_split("//u", "àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ _'", -1, PREG_SPLIT_NO_EMPTY);
		// equivalent characters without accents
		$without = preg_split("//u", "aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY---", -1, PREG_SPLIT_NO_EMPTY);
		// replace accents and lowercase the string
		$string 	= strtolower(str_replace($with, $without, $string));
		// replace all but 0-9 a-z remove triples/doubles and trim the edges
		return(
			trim(
				str_replace(
					array('---','--'), 
					'-', 
					preg_replace('#[^a-z0-9\-]#', '-', $string)
				), 
				'-'
			)
		);
	}
	
	// create a link
	public static function link(string $string, string $url='#', array $attributes=[]) :Element {
		// build the actual link
		return(new Element('a', array_merge(array('href'=>$url, 'text'=>$string), $attributes)));
	}
	
	// truncate to a certain length
	public static function truncate($string, $length=16) {
		// if string is longer than authorized truncate, else do nothing
		return(
			strlen($string) > $length ? 
			trim(mb_substr(strip_tags($string), 0, $length - 2, Response::getCharset())).'…' : 
			$string
		);
	}
	
	// will wrap a portion of text in another
	public static function wrap($text, $phrase, $wrapper = '<strong class="highlight">\\1</strong>') {
		if(empty($text)) {
			return '';
		}
		if(empty($phrase)) {
			return $text;
		}
		if(is_array($phrase)) {
			foreach ($phrase as $word) {
				$pattern[] = '/(' . preg_quote($word, '/') . ')/i';
				$replacement[] = $wrapper;
			}
		}
		else {
			$pattern = '/('.preg_quote($phrase, '/').')/i';
			$replacement = $wrapper;
		}
		return preg_replace($pattern, $replacement, $text);
	}
	
	// remove all formatting and invisible symbols to get the shortest possible string
	public static function minify($string) {
		// remove all formatting symbols and double spaces	
		return(str_replace('  ', ' ', str_replace(array("\t", "\n", "\r"), '', $string)));
	}

	// will clean the value of anything but 0-9 and minus preserve sign return integer
	public static function integer($value) {
		if(strrpos($value, '.')) {
			$value = str_replace(',', '' , $value);
		}
		elseif(strrpos($value, ',')) {
			$value = str_replace('.', '' , $value);
		} 
		return(intval(preg_replace('/[^0-9.\-]/', '', str_replace(',', '.' , $value))));
	}
	
	// will clean the value of anything but 0-9\.- preserve sign return float
	public static function float($value, $precision = 2) :float {
		if(strrpos($value, '.')) {
			$value = str_replace(',', '' , $value);
		}
		elseif(strrpos($value, ',')) {
			$value = str_replace('.', '' , $value);
		} 
		return(floatval(
			round(preg_replace('/[^0-9.\-]/', '', str_replace(',', '.' , $value)), $precision)
		));
	}

	// convert an array to a csv
	public static function csv(array $array, string $separator = "\t", string $encapsulate_cells = '"') :string {

		// declare our csv
		$csv = '';
		// for each line of the array
		foreach($array as $cells) {
			// for each cell of that line
			foreach($cells as $cell) {
				// protect the cell's value, encapsulate it, and separate it,
				$csv .= $encapsulate_cells . str_replace($encapsulate_cells, '\\'.$encapsulate_cells, $cell) . $encapsulate_cells . $separator;
			}
			// skip to the next line
			$csv .= "\n";
		}
		// return the formatted csv
		return $csv;

	}
	
}

?>
