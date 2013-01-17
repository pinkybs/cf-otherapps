<?php

/**
 * file download
 * 
 * @package    MyLib
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2008/07/18     Hulj
 */
class MyLib_Download
{

	/**
	 * download a file
	 * @static 
	 * @param string $filename
	 * @param string $displayname
	 */
	public static function download($filename, $displayname)
	{
		if (!self::check($filename))
			return;
		
		if (empty($displayname)) {
			$displayname = basename($filename);
		}
		else {
			$displayname = self::encode($displayname);
		}
		
		ob_end_clean();
		ob_start();
		
		//require_once('MyLib/MineType.php');
		//$path_parts = pathinfo($filename);
		//$extension = $path_parts['extension'];
		//$type = $filetype[$extension];        
		

		$size = filesize($filename);
		header('Pragma: public');
		// set expiration time
		header('Expires: 0');
		header('Cache-Component: must-revalidate, post-check=0, pre-check=0');
		header('Content-type: application/octet-stream');
		
		//header('Content-type: application/force-download');
		//header('Content-type: ' . $type);
		//header('Accept-Ranges: bytes');
		header('Content-Length: ' . $size);
		header('Content-Disposition: attachment; filename="' . $displayname . '"');
		header('Content-Transfer-Encoding: binary');
		
		//@readfile($filename);
		$fp = fopen($filename, 'r');
		if ($fp) {
			fpassthru($fp);
		}
		
		exit(0);
	}

	/**
	 * check file
	 * exists or not and others
	 * @static 
	 * @param string $filename
	 * @return boolean
	 */
	public static function check($filename)
	{
		if (file_exists($filename)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * encode string
	 *
	 * @static 
	 * @param string $str
	 * @return string
	 */
	public static function encode($str)
	{
		//return iconv('utf-8', 'iso-8859-1', $str);    
		//return iconv('utf-8', 'GBK', $str);
		return iconv('utf-8', 'Shift-JIS', $str);
		//return mb_convert_encoding($str, 'GBK', 'utf-8');        
	}

}