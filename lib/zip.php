<?php
/** 
*
* @package phpBB3
* @version $Id: functions_compress.php,v 1.45 2007/03/06 11:30:11 acydburn Exp $ 
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/


function filelist($rootdir, $dir = '')
{
  $matches = array();

  #echo $rootdir;
  // Remove initial / if present
  #$rootdir = (substr($rootdir, 0, 1) == '/') ? substr($rootdir, 1) : $rootdir;
  // Add closing / if not present
  $rootdir = ($rootdir && substr($rootdir, -1) != '/') ? $rootdir . '/' : $rootdir;

  // Remove initial / if present
  #$dir = (substr($dir, 0, 1) == '/') ? substr($dir, 1) : $dir;
  // Add closing / if not present
  $dir = ($dir && substr($dir, -1) != '/') ? $dir . '/' : $dir;

  if (!is_dir($rootdir . $dir))
  {
    return array();
  }

  $dh = @opendir($rootdir . $dir);

  if (!$dh)
  {
    return array();
  }

  while (($fname = readdir($dh)) !== false)
  { 
    if (is_file("$rootdir$dir$fname"))
    {
      $matches[$dir][] = $fname;
    }
    else if ($fname[0] != '.' && is_dir("$rootdir$dir$fname"))
    {
      $matches += filelist($rootdir, "/$dir$fname");
    }
  }
  closedir($dh);

  return $matches;
}



/**
* Class for handling archives (compression/decompression)
* @package phpBB3
*/
class compress 
{
	var $fp = 0;

	/**
	* Add file to archive
	*/
	function add_file($src, $src_rm_prefix = '', $src_add_prefix = '', $skip_files = '')
	{

		$skip_files = explode(',', $skip_files);

		// Remove rm prefix from src path
		$src_path = ($src_rm_prefix) ? preg_replace('#^(' . preg_quote($src_rm_prefix, '#') . ')#', '', $src) : $src;
		// Add src prefix
		$src_path = ($src_add_prefix) ? ($src_add_prefix . ((substr($src_add_prefix, -1) != '/') ? '/' : '') . $src_path) : $src_path;
		// Remove initial "/" if present
		$src_path = (substr($src_path, 0, 1) == '/') ? substr($src_path, 1) : $src_path;

		if (is_file($src))
		{
			$this->data($src_path, file_get_contents("$src"), false, stat("$src"));
		}
		else if (is_dir($src))
		{
			// Clean up path, add closing / if not present
			$src_path = ($src_path && substr($src_path, -1) != '/') ? $src_path . '/' : $src_path;
      #echo "<br>Source: ".$src_path;

			$filelist = array();
			$filelist = filelist("$src", '');
			is_array($filelist) && krsort($filelist);
      #print_r($filelist);

			if ($src_path)
			{
				$this->data($src_path, '', true, stat("$src"));
			}

			foreach ($filelist as $path => $file_ary)
			{
				if ($path)
				{
					// Same as for src_path
					#$path = (substr($path, 0, 1) == '/') ? substr($path, 1) : $path;  for some reason having this uncommented breaks sub directories..
					$path = ($path && substr($path, -1) != '/') ? $path . '/' : $path;
					$this->data("$src_path$path", '', true, stat("$src$path"));
				}

				foreach ($file_ary as $file)
				{
					if (in_array($path . $file, $skip_files))
					{
						continue;
					}

					$this->data("$src_path$path$file", file_get_contents("$src$path$file"), false, stat("$src$path$file"));
				}
			}
		}

		return true;
	}

	/**
	* Add custom file (the filepath will not be adjusted)
	*/
	function add_custom_file($src, $filename)
	{
		$this->data($filename, file_get_contents($src), false, stat($src));
		return true;
	}

	/**
	* Add file data
	*/
	function add_data($src, $name)
	{
		$stat = array();
		$stat[2] = 436; //384
		$stat[4] = $stat[5] = 0;
		$stat[7] = strlen($src);
		$stat[9] = time();
		$this->data($name, $src, false, $stat);
		return true;
	}

	/**
	* Return available methods
	*/
	function methods()
	{
		$methods = array('.tar');
		$available_methods = array('.tar.gz' => 'zlib', '.tar.bz2' => 'bz2', '.zip' => 'zlib');

		foreach ($available_methods as $type => $module)
		{
			if (!@extension_loaded($module))
			{
				continue;
			}
			$methods[] = $type;
		}

		return $methods;
	}
}

/**
* Zip creation class from phpMyAdmin 2.3.0 (c) Tobias Ratschiller, Olivier Müller, Loïc Chapeaux, 
* Marc Delisle, http://www.phpmyadmin.net/
*
* Zip extraction function by Alexandre Tedeschi, alexandrebr at gmail dot com
*
* Modified extensively by psoTFX and DavidMJ, (c) phpBB Group, 2003
*
* Based on work by Eric Mueller and Denis125
* Official ZIP file format: http://www.pkware.com/appnote.txt
*
* @package phpBB3
*/
class compress_zip extends compress
{
	var $datasec = array();
	var $ctrl_dir = array();
	var $eof_cdh = "\x50\x4b\x05\x06\x00\x00\x00\x00";

	var $old_offset = 0;
	var $datasec_len = 0;

	/**
	* Constructor
	*/
	function compress_zip($mode, $file)
	{
		return $this->fp = @fopen($file, $mode . 'b');
	}

	/**
	* Convert unix to dos time
	*/
	function unix_to_dos_time($time)
	{
		$timearray = (!$time) ? getdate() : getdate($time);

		if ($timearray['year'] < 1980)
		{
			$timearray['year'] = 1980;
			$timearray['mon'] = $timearray['mday'] = 1;
			$timearray['hours'] = $timearray['minutes'] = $timearray['seconds'] = 0;
		}

		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	/**
	* Extract archive
	*/
	function extract($dst)
	{		
		// Loop the file, looking for files and folders
		$dd_try = false;
		rewind($this->fp);

    if (!is_dir($dst)) {
      trigger_error("compress::zip \$dst is not a dir: ".$dst);
      return false;
    }

		while (!feof($this->fp))
		{
			// Check if the signature is valid...
			$signature = fread($this->fp, 4);

			switch ($signature)
			{
				// 'Local File Header'
				case "\x50\x4b\x03\x04":
					// Lets get everything we need.
					// We don't store the version needed to extract, the general purpose bit flag or the date and time fields
					$data = unpack("@4/vc_method/@10/Vcrc/Vc_size/Vuc_size/vname_len/vextra_field", fread($this->fp, 26));
					$file_name = fread($this->fp, $data['name_len']); // filename

					if ($data['extra_field'])
					{
						fread($this->fp, $data['extra_field']); // extra field
					}

					$target_filename = $file_name;

					if (!$data['uc_size'] && !$data['crc'] && substr($file_name, -1, 1) == '/')
					{
						if (!is_dir($dst.$target_filename))
						{
							$str = '';
							$folders = explode('/', $target_filename);

							// Create and folders and subfolders if they do not exist
							foreach ($folders as $folder)
							{
								$str = (!empty($str)) ? $str . '/' . $folder : $folder;
								if (!is_dir($dst.$str))
								{
									if (!@mkdir($dst.$str, 0777))
									{
										trigger_error("Could not create directory \$dst\$str");
									}
									@chmod($dst.$str, 0777);
								}
							}
						}
						// This is a directory, we are not writting files
						continue;
					}
					else
					{
						// Some archivers are punks, they don't don't include folders in their archives!
						$str = '';
						$folders = explode('/', pathinfo($target_filename, PATHINFO_DIRNAME));

						// Create and folders and subfolders if they do not exist
						foreach ($folders as $folder)
						{
							$str = (!empty($str)) ? $str . '/' . $folder : $folder;
							if (!is_dir($dst.$str))
							{
								if (!@mkdir($dst.$str, 0777))
								{
									trigger_error("Could not create directory \$dst\$str");
								}
								@chmod($dst.$str, 0777);
							}
						}
					}

					if (!$data['uc_size'])
					{
						$content = '';
					}
					else
					{
						$content = fread($this->fp, $data['c_size']);
					}

					$fp = fopen($dst.$target_filename, "w");

					switch ($data['c_method'])
					{
						case 0:
							// Not compressed
							fwrite($fp, $content);
						break;
					
						case 8:
							// Deflate
							fwrite($fp, gzinflate($content, $data['uc_size']));
						break;

						case 12:
							// Bzip2
							fwrite($fp, bzdecompress($content));
						break;
					}
					
					fclose($fp);
				break;

				// We hit the 'Central Directory Header', we can stop because nothing else in here requires our attention
				// or we hit the end of the central directory record, we can safely end the loop as we are totally finished with looking for files and folders
				case "\x50\x4b\x01\x02":
				// This case should simply never happen.. but it does exist..
				case "\x50\x4b\x05\x06":
				break 2;
				
				// 'Packed to Removable Disk', ignore it and look for the next signature...
				case 'PK00':
				continue 2;
				
				// We have encountered a header that is weird. Lets look for better data...
				default:
					if (!$dd_try)
					{
						// Unexpected header. Trying to detect wrong placed 'Data Descriptor';
						$dd_try = true;
						fseek($this->fp, 8, SEEK_CUR); // Jump over 'crc-32'(4) 'compressed-size'(4), 'uncompressed-size'(4)
						continue 2;
					}
					trigger_error("Unexpected header, ending loop");
				break 2;
			}

			$dd_try = false;
		}
	}

	/**
	* Close archive
	*/
	function close()
	{
		// Write out central file directory and footer ... if it exists
		if (sizeof($this->ctrl_dir))
		{
			fwrite($this->fp, $this->file());
		}
		fclose($this->fp);
	}

	/**
	* Create the structures ... note we assume version made by is MSDOS
	*/
	function data($name, $data, $is_dir = false, $stat)
	{
		$name = str_replace('\\', '/', $name);

		$hexdtime = pack('V', $this->unix_to_dos_time($stat[9]));

		if ($is_dir)
		{
			$unc_len = $c_len = $crc = 0;
			$zdata = '';
			$var_ext = 10;
		}
		else
		{
			$unc_len = strlen($data);
			$crc = crc32($data);
			$zdata = gzdeflate($data);
			$c_len = strlen($zdata);
			$var_ext = 20;

			// Did we compress? No, then use data as is
			if ($c_len >= $unc_len)
			{
				$zdata = $data;
				$c_len = $unc_len;
				$var_ext = 10;
			}
		}
		unset($data);

		// If we didn't compress set method to store, else deflate
		$c_method = ($c_len == $unc_len) ? "\x00\x00" : "\x08\x00";

		// Are we a file or a directory? Set archive for file
		$attrib = ($is_dir) ? 16 : 32;

		// File Record Header
		$fr = "\x50\x4b\x03\x04";		// Local file header 4bytes
		$fr .= pack('v', $var_ext);		// ver needed to extract 2bytes
		$fr .= "\x00\x00";				// gen purpose bit flag 2bytes
		$fr .= $c_method;				// compression method 2bytes
		$fr .= $hexdtime;				// last mod time and date 2+2bytes
		$fr .= pack('V', $crc);			// crc32 4bytes
		$fr .= pack('V', $c_len);		// compressed filesize 4bytes
		$fr .= pack('V', $unc_len);		// uncompressed filesize 4bytes
		$fr .= pack('v', strlen($name));// length of filename 2bytes

		$fr .= pack('v', 0);			// extra field length 2bytes
		$fr .= $name;
		$fr .= $zdata;
		unset($zdata);

		$this->datasec_len += strlen($fr);

		// Add data to file ... by writing data out incrementally we save some memory
		fwrite($this->fp, $fr);
		unset($fr);

		// Central Directory Header
		$cdrec = "\x50\x4b\x01\x02";		// header 4bytes
		$cdrec .= "\x00\x00";				// version made by
		$cdrec .= pack('v', $var_ext);		// version needed to extract
		$cdrec .= "\x00\x00";				// gen purpose bit flag
		$cdrec .= $c_method;				// compression method
		$cdrec .= $hexdtime;				// last mod time & date
		$cdrec .= pack('V', $crc);			// crc32
		$cdrec .= pack('V', $c_len);		// compressed filesize
		$cdrec .= pack('V', $unc_len);		// uncompressed filesize
		$cdrec .= pack('v', strlen($name));	// length of filename
		$cdrec .= pack('v', 0);				// extra field length
		$cdrec .= pack('v', 0);				// file comment length
		$cdrec .= pack('v', 0);				// disk number start
		$cdrec .= pack('v', 0);				// internal file attributes
		$cdrec .= pack('V', $attrib);		// external file attributes
		$cdrec .= pack('V', $this->old_offset);	// relative offset of local header
		$cdrec .= $name;

		// Save to central directory
		$this->ctrl_dir[] = $cdrec;

		$this->old_offset = $this->datasec_len;
	}

	/**
	* file
	*/
	function file()
	{
		$ctrldir = implode('', $this->ctrl_dir);

		return $ctrldir . $this->eof_cdh .
			pack('v', sizeof($this->ctrl_dir)) .	// total # of entries "on this disk"
			pack('v', sizeof($this->ctrl_dir)) .	// total # of entries overall
			pack('V', strlen($ctrldir)) .			// size of central dir
			pack('V', $this->datasec_len) .			// offset to start of central dir
			"\x00\x00";								// .zip file comment length
	}

}

?>
