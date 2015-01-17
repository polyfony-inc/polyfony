<?php
/**
 * PHP Version 5
 * File upload helper
 * @package Polyfony
 * @link https://github.com/SIB-FRANCE/Polyfony
 * @license http://www.gnu.org/licenses/lgpl.txt GNU General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Polyfony;

class Uploader {

	// source file
	protected	$Source;
	// destination of uploaded file
	protected	$Destination;
	// name of the destination file
	protected	$Name;
	// type of the file being uploaded
	protected	$Type;
	// size of the file being uploaded
	protected	$Size;
	// limitations on the size and mimetypes allowed
	protected	$Limits;
	// list of errors
	protected	$Error;
	// file info object
	protected	$Info;
	
	// constructor
	public function __construct() {
		// set the default constraints
		$this->Limits = new stdClass();
		$this->Limits->Size = null;
		$this->Limits->Types = array();
	}

	// set the source file path
	public function source($path) {
		// set the source
		$this->Source = $path;
		// return self
		return($this);
	}
	
	// set the destination
	public function destination($path) {
		// set the path
		$this->Destination = $path;
		// return self
		return($this);
	}
	
	// limit size to $max bytes
	public function limitSize($max) {
		// if the maximum is not numeric
		if(is_numeric($max)) {
			// set maximum
			$this->Limits->Size = intval($max);
		}
		// return self
		return($this);
	}
	
	// limit types to array or mimetypes
	public function limitTypes($allowed) {
		// if allowed is an array
		if(is_array($allowed)) {
			// push the whole table
			$this->Limits->Types = $allowed;
		}
		// allowed is a string
		else {
			// push it
			$this->Limits->Types[] = $allowed;
		}
		// return self
		return($this);
	}
	
	// actually move the file
	public function execute() {
		// if source is not specified
		if(!$this->Source) {
			// add an error
			$this->Error = 'No source provided';	
			// return false already
			return(false);
		}
		// check if the source file exists
		if(!file_exists($this->Source)) {
			// add an error
			$this->Error = 'Source file does not exist';	
			// return false already
			return(false);
		}
		// if no destination
		if(!$this->Destination) {
			// add an error
			$this->Error = 'No destination provided';	
			// return false already
			return(false);
		}
		// if the destination is not a folder and already exists
		if(!is_dir($this->Destination) && file_exists($this->Destination)) {
			// throw an exception
			$this->Error = 'Destination file already exists';	
			// return false
			return(false);
		}
		// if the destination is not a directory
		elseif(!is_dir($this->Destination)) {
			// try to explode by slash to find the file name
			$exploded = explode('/',$this->Destination);
			// get the last element wich is the file name
			$this->Name = $exploded[count($exploded)-1];
			// set the directory as being the path minus the name
			$this->Destination = str_replace($this->Name,'',$this->Destination);
		}
		// the destination if a folder without name
		elseif(is_dir($this->Destination)) {
			// if the trailing slash is ommited
			if(substr($this->Destination,-1) != '/') {
				// add it to the path
				$this->Destination .= '/';	
			}
			// generate a name being sha1(file) +sha1(microtime)
			$this->Name = substr(sha1_file($this->Source),0,30) . substr(sha1(microtime()),0,10);
		}
		// destination unknown !
		else {
			// add an error
			$this->Error = 'Destination is unkown or incorrect';
			// return an error
			return(false);
		}
		// check if the destination is writable
		if(!is_writable($this->Destination)) {
			// add an error
			$this->Error = 'Destination is not writable';
			// return an error
			return(false);
		}
		// compute filesize
		$this->Size = filesize($this->Source);
		// if a maximum size if set and we exceed it
		if($this->Limits->Size && $this->Size > $this->Limits->Size) {
			// add an error
			$this->Error = 'File is larger than maximum of '.\Polyfony\Format::size($this->Limits->Size);
			// return an error
			return(false);
		}
		// get a new fileinfo object
		$this->Info = new finfo(FILEINFO_MIME);
		// if the fileinfo failed to instanciate
		if(!$this->Info) {
			// add an error
			$this->Error = 'Failed to instanciate fileinfo object';
			// return an error
			return(false);	
		}
		// get the mimetype
		$this->Type = $this->Info->file($this->Source);
		// if failed to get a type
		if(!$this->Type) {
			// add an error
			$this->Error = 'Failed to get mimetype';
			// return an error
			return(false);		
		}
		// we got a mimetype
		else {
			// if it has a ; in it
			if(strstr($this->Type,';')) {
				// only keep the first part
				list($this->Type) = explode(';',$this->Type);
			}
		}
		// if type limitations are set and current type is not allowed
		if(count($this->Limits->Types) && !in_array($this->Type,$this->Limits->Types)) {
			// add an error
			$this->Error = 'This file is not in the list of allowed types : ' . implode(',',$this->Limits->Types);
			// return an error
			return(false);
		}
		// actually move the uploaded file
		$status = move_uploaded_file($this->Source,$this->Destination.$this->Name);
		// if it failed
		if(!$status) {
			// try to rename the file
			$status = rename($this->Source,$this->Destination.$this->Name);
			// if it failed
			if(!$status) {
				// add an error
				$this->Error = 'Moving the file to destination failed';	
				// return false
				return(false);
			}
			// it went well
			else {
				// return true
				return(true);
			}
		}
		// all went well
		else {
			// return success
			return(true);	
		}
	}
	
	// retieve informations about all that hapened
	public function infos() {
		// return an array
		return(array(
			'error'			=>\Polyfony\Locales::get($this->Error),
			'source'		=>$this->Source,
			'destination'	=>$this->Destination,
			'name'			=>$this->Name,
			'size'			=>$this->Size,
			'type'			=>$this->Type
		));
	}
	
	// retrieve only the error
	public function error() {
		// return the error
		return(\Polyfony\Locales::get($this->Error));
	}
	
}

?>
