<?php

namespace Polyfony\Response;
use Polyfony\Config as Config;
use Polyfony\Query;

class XLSX {

	// generate an XLS(X) file from an array
	public static function buildAndGetDocument(array $content) :string {
		// if !class_exists... a little help for the developer
		if(!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
			Throw new \Polyfony\Exception(
				'Responses of type XLS(X) depend on phpoffice/spreadsheet', 
				500
			);
		}
		// create a new spreadsheet 
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
		// to to the current sheet
		($spreadsheet->getActiveSheet())
			// and import our array
			->fromArray(Query\Convert::convertArrayOfObjectsToPlainArray($content), NULL, 'A1');
		// create a singleton to generate an actual file
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		// allow for some backward compatibility or not
		$writer->setOffice2003Compatibility(
			Config::get('phpoffice','office_2003_compatibility') ? true : false
		);
		// as dirty as it may be, we have to buffer it, since phpoffice 
		// doesn't even support gettint the document without saving or outputing it
		ob_start();
		// generate it
		$writer->save('php://output');
		// return the formatted document
		return ob_get_clean();
	}

}

?>
