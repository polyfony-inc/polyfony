<?php

 //  ___                           _          _ 
 // |   \ ___ _ __ _ _ ___ __ __ _| |_ ___ __| |
 // | |) / -_) '_ \ '_/ -_) _/ _` |  _/ -_) _` |
 // |___/\___| .__/_| \___\__\__,_|\__\___\__,_|
 //          |_|                                

namespace Polyfony;

class Thumbnail {

	// handler to the source image
	protected	$Image;
	// handler to the resized image
	protected	$Sized;
	// source image dimension
	protected	$OriginalWidth;
	protected	$OriginalHeight;
	// source image dimension
	protected	$Width;
	protected	$Height;
	// source image
	protected	$Source;
	// destination of thumbnail
	protected	$Destination;
	// type of the image
	protected	$Type;
	// size of the image
	protected	$Size;
	// quality of the generated thumbnail
	protected	$Quality;
	// limitations on the size of the image
	protected	$Maximum;
	// list of types allowed
	protected	$Allowed;
	// last error that happened
	protected	$Error;

	public function __construct() {
		// this is now deprecated, will probably be removed in a future release
		trigger_error(
			'Usage of Polyfony\Thumbnail is deprecated, require intervention/image instead', 
			E_USER_DEPRECATED
		);
		// set the default maximum size
		$this->Maximum = 1024;
		// set default quality
		$this->Quality = 100;
		// set allowed types
		$this->Allowed = array('image/jpeg', 'image/png');
		// default output type
		$this->Output = 'image/jpeg';
	}
	
	// set the source image path
	public function source($path, $override_chroot = false) {
		// if chroot is enabled, restrict the path to the chroot
		$this->Source = Filesystem::chroot($path, $override_chroot);
		// return self
		return($this);
	}
	
	// set the destination of the thumbnail
	public function destination($path, $override_chroot = false) {
		// if chroot is enabled, restrict the path to the chroot
		$this->Destination = Filesystem::chroot($path, $override_chroot);
		// return self
		return($this);
	}
	
	// set the output type
	public function type($mimetype) {
		// if the type is correct
		if(in_array($mimetype, $this->Allowed)) {
			// set the type
			$this->Output = $mimetype;
		}
		// return self
		return($this);
	}
	
	// set the size
	public function size($pixels) {
		// if the value is acceptable
		if(is_numeric($pixels) && $pixels >= 16 && $pixels <= 4096) {
			// set the limit
			$this->Maximum = intval($pixels);
		}
		// return self
		return($this);
	}
	
	// set the quality
	public function quality($quality) {
		// if the value is acceptable
		if(is_numeric($quality) && $quality >= 10 && $quality <= 100) {
			// set the quality
			$this->Quality = intval($quality);
		}
		// return self
		return($this);
	}

	// retieve informations about all that hapened
	public function infos() {
		// return an array
		return(array(
			'error'			=>\Polyfony\Locales::get($this->Error),
			'source'		=>$this->Source,
			'destination'	=>$this->Destination,
			'size'			=>$this->Size,
			'name'			=>$this->Name,
			'width'			=>$this->Width,
			'height'		=>$this->Height
		));
	}
	
	// retrieve only the error
	public function error() {
		// return the error
		return(\Polyfony\Locales::get($this->Error));
	}
	
	// actually generated the thumbnail
	public function execute() {
		// if source is not specified
		if(!$this->Source) {
			// add an error
			$this->Error = 'No source image provided';	
			// return false already
			return(false);
		}
		// check if the source file exists
		if(!file_exists($this->Source)) {
			// add an error
			$this->Error = 'Source image does not exist';	
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
			$this->Error = 'Destination image already exists';	
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
			$this->Name = sha1_file($this->Source);
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
		// get the type of the source image
		$this->Type = Filesystem::type($this->Source, true);
		// if failed to get a type
		if(!$this->Type) {
			// add an error
			$this->Error = 'Failed to get mimetype';
			// return an error
			return(false);		
		}
		// if the source type is not allowed
		if(!in_array($this->Type, $this->Allowed)) {
			// add an error
			$this->Error = 'This image is not in the list of allowed formats : ' . implode(',',$this->Allowed);
			// return an error
			return(false);
		}
		// if we have a png
		if($this->Type == 'image/png') {
			// create image
			$this->Image = imagecreatefrompng($this->Source);
		}
		// if we have a jpg
		elseif($this->Type == 'image/jpeg') {
			// create image
			$this->Image = imagecreatefromjpeg($this->Source);
		}		// get the width
		$this->OriginalWidth = imageSX($this->Image);
		// get the height
		$this->OriginalHeight = imageSY($this->Image);
		// if the picture is horizontal
		if($this->OriginalHeight < $this->OriginalWidth) {
			// set the height
			$this->Height 	= round($this->Maximum * $this->OriginalHeight / $this->OriginalWidth , 0);
			// set the width
			$this->Width 	= $this->Maximum;
		}
		// else the picture is vertical or cubic
		else {
			// set the width
			$this->Width = round($this->Maximum * $this->OriginalWidth / $this->OriginalHeight , 0);
			// set the height
			$this->Height = $this->Maximum;
		}
		// create a canvas for the resized image
		$this->Sized = ImageCreateTrueColor($this->Width,$this->Height);
		// resize and inject into the canvas
		imagecopyresampled(
			$this->Sized, $this->Image,
			0, 0, 0, 0,
			$this->Width, $this->Height,
			$this->OriginalWidth, $this->OriginalHeight
		);
		// destroy the source handler
		imagedestroy($this->Image);
		// if output is png
		if($this->Output == 'image/png') {
			// roof
			$this->Quality = $this->Quality > 90 ? '90' : $this->Quality;
			// convert the jpeg quality index to a png quality index
			$this->Quality = round(abs(-0.1 * $this->Quality + 10));
			// create png image
			$status = imagepng($this->Sized,$this->Destination.$this->Name,$this->Quality);
		}
		elseif($this->Output == 'image/jpeg') {
			// create jpeg image
			$status = imagejpeg($this->Sized,$this->Destination.$this->Name,$this->Quality);
		}
		// destroy the sized handler
		imagedestroy($this->Sized);
		
		// if everything went well
		if($status) {
			// get the size of the generated thumbnail
			$this->Size = filesize($this->Destination . $this->Name);
			// creation succeeded
			return(true);
		}
		// failed somewhere
		else {
			// add an error
			$this->Error = 'Failed to write thumbnail to the disk';
			// and return false
			return(false);	
		}
	}

}

?>
