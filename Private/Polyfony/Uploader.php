<?php

 //  ___                           _          _ 
 // |   \ ___ _ __ _ _ ___ __ __ _| |_ ___ __| |
 // | |) / -_) '_ \ '_/ -_) _/ _` |  _/ -_) _` |
 // |___/\___| .__/_| \___\__\__,_|\__\___\__,_|
 //          |_|                                

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
	
	// constructor
	public function __construct() {
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Uploader is deprecated, require gargron/fileupload instead', 
			E_USER_DEPRECATED
		);
		// set the default constraints
		$this->Limits = new \stdClass();
		$this->Limits->Size = null;
		$this->Limits->Types = array();
	}

	// set the source file path, disable chroot before of the tmp folder
	public function source($path, $override_chroot = true) {
		// if chroot is enabled, restrict the path to the chroot
		$this->Source = Filesystem::chroot($path, $override_chroot);
		// return self
		return($this);
	}
	
	// set the destination
	public function destination($path, $override_chroot = false) {
		// set the path
		$this->Destination = Filesystem::chroot($path, $override_chroot);
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
		// get the type of the uploaded document
		$this->Type = Filesystem::type($this->Source, true);
		// if failed to get a type
		if(!$this->Type) {
			// add an error
			$this->Error = 'Failed to get mimetype';
			// return an error
			return(false);		
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
	
	// retrieve only the file name
	public function name() {
		// return the name
		return($this->Name);
	}

	// retrieve only the error
	public function error() {
		// return the error
		return(\Polyfony\Locales::get($this->Error));
	}
	
}

?>
