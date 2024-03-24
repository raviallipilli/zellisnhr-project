<?php
class Uploader
{
	public $Filename;
	public $Filename_temp;
	public $Filename_cleaned;
	public $Directory;
	public $Extension;
	public $Allowed_extensions_bespoke;
	private $__Allowed_extensions_array = array('jpg', 'png', 'gif', 'doc','txt', 'docx', 'pdf', 'rtf', 'csv');

	public function __construct()
	{
		$this->Filename = null;
		$this->Filename_temp = null;
		$this->Filename_cleaned = null;
		$this->Directory = null;
		$this->Extension = null;
		$this->Allowed_extensions_bespoke = array();
	}

	public function Upload($keep_unique = true)
	{
		//get information about this file
		$file_info_array = pathinfo($this->Filename);
		$this->Extension = strtolower($file_info_array['extension']);

		//extension has been found in database, so its not a dodgy file
		if($this->Is_extension_allowed($this->Extension))
		{
			if($this->Filename != '')
			{
				//add the filename to the start of it
				//remove the extension and clean the filename
				$this->Filename_cleaned = $file_info_array['filename'];
				$this->Filename_cleaned = strtolower(str_replace(' ', '-', $this->Filename_cleaned));
				$this->Filename_cleaned = strtolower(str_replace('_', '-', $this->Filename_cleaned));
				$this->Filename_cleaned = strtolower(str_replace('\'', '-', $this->Filename_cleaned));

				// Create a unique file name
				if($keep_unique)
				{
					$this->Filename_cleaned = $this->Filename_cleaned.'-'.date('s').'.'.$this->Extension;
				}
				else
				{
					$this->Filename_cleaned = $this->Filename_cleaned.'.'.$this->Extension;
				}

				if (!copy($this->Filename_temp, $this->Directory.'/'.$this->Filename_cleaned)) 
				{
					return array('status' => 500, 'error_message' => 'Failed to copy file: '. $this->Filename_temp.' - '.$this->Directory.'/'.$this->Filename_cleaned);
				}
				unlink($this->Filename_temp);
				
				//send out an array with the relevent details
				$file_array = array('directory' => $this->Directory, 'filename' => $this->Filename_cleaned, 'ext' => $this->Extension, 'filename_without_extension' => $this->Get_file_without_extension($this->Filename_cleaned), 'status' => 200);
				
				return $file_array;
			}
			else
			{
				return array('status' => 500, 'error_message' => 'File was empty');
			}
		}
		else
		{
			return array('status' => 500, 'error_message' => 'Invalid extension');
		}
	}

	public function Rename($input = null, $output = null)
	{
		if(!is_null($input))
		{
			//does the file exist before rename
			if(file_exists($this->Directory.'/'.$input))
			{
				return rename($this->Directory.'/'.$input, $this->Directory.'/'.$output);
			}
			return false;
		}
		return false;
	}

	public function Get_file_extension($filename = null) 
	{
		//get information about this file
		$file_info_array = pathinfo($filename);
		$this->Extension = $file_info_array['extension'];
		return strtolower($this->Extension);
	}

	public function Get_file_without_extension($filename = null) 
	{
		//get information about this file
		$file_info_array = pathinfo($filename);
		return $file_info_array['filename'];
	}

	public function Is_extension_allowed($ext = null)
	{
		//if a bespoke list of extensions was passed in which was seperate from the already defined list above, check them extensions instead
		if(count($this->Allowed_extensions_bespoke) > 0)
		{
			$check_extension_array = $this->Allowed_extensions_bespoke;
		}
		else
		{
			$check_extension_array = $this->__Allowed_extensions_array;
		}

		//is the given extension allowed
		if(in_array($ext, $check_extension_array))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function Get_readable_filesize($size = 0)
	{
		/*
		Returns a human readable size
		*/
		  $i=0;
		  $iec = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		  while (($size/1024)>1) {
		   $size=$size/1024;
		   $i++;
		  }
		  return substr($size, 0, strpos($size,'.')+4).$iec[$i];
	}

	public function Resize_image($width = 0, $height = 0, $quality = 90, $filename_in = null, $filename_out = null)
	{
		$this->Filename = $filename_in;
		$this->Extension = strtolower($this->Get_file_extension($this->Filename));

		$size = getimagesize($this->Filename);
		$ratio = $size[0] / $size[1];
		if ($ratio >= 1){
			$scale = $width / $size[0];
		} else {
			$scale = $height / $size[1];
		}
		// make sure its not smaller to begin with!
		if ($width >= $size[0] && $height >= $size[1]){
			$scale = 1;
		}

		//height is not required and can be based by width only
		if($height == 0)
		{
			$scale = $width / $size[0];
		}

		// echo $fileext;
		switch ($this->Extension)
		{
			case 'jpg':
				$im_in = imagecreatefromjpeg($this->Filename);
				$im_out = imagecreatetruecolor($size[0] * $scale, $size[1] * $scale);
				imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $size[0] * $scale, $size[1] * $scale, $size[0], $size[1]);
				imagejpeg($im_out, $filename_out, $quality);
			break;
			case 'gif':
				$im_in = imagecreatefromgif($this->Filename);
				$im_out = imagecreatetruecolor($size[0] * $scale, $size[1] * $scale);
				imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $size[0] * $scale, $size[1] * $scale, $size[0], $size[1]);
				imagegif($im_out, $filename_out, $quality);
			break;
			case 'png':
				$im_in = imagecreatefrompng($this->Filename);
				$im_out = imagecreatetruecolor($size[0] * $scale, $size[1] * $scale);
				imagealphablending($im_in, true); // setting alpha blending on
				imagesavealpha($im_in, true); // save alphablending setting (important)
				imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $size[0] * $scale, $size[1] * $scale, $size[0], $size[1]);
				imagepng($im_out, $filename_out, 9);
			break;
		}
		imagedestroy($im_out);
		imagedestroy($im_in);
	}
}