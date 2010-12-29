<?php

class File
{

   /**
    * Determines the Content-Type for a given file
    * 
    * @param string $file_name
    */
    public function parse_content_type($file_name)
    {
	$ext = array_pop(explode(".", $file_name));

	$supported_types = array('doc', 'pdf', 'ppt', 'png', 'jpg', 'xls');
	if (!in_array($ext, $supported_types))
	{
	    throw new Exception('Unsupported extension: ' . $ext . ' from file name ' . $file_name);
	}
	return 'application/'.strtolower($ext);
    }
	
   /**
    * Outputs a single file in response to HTTP get
    *     
    * @param string $os_file_name Full path file name for file
    * @param string $user_file_name File name displayed to the user
    */
    public function render($os_file_name, $user_file_name)
    {
	if(!file_exists($os_file_name))
        {
	    throw new ApplicationException("File $os_file_name Not found");
        }

	// header("Cache-control: none");
	header("Pragma: private");
	header("Cache-control: private, must-revalidate");
	header("Content-Type: ". $this->parse_content_type($os_file_name));
	header('Content-Disposition: attachment; filename="'.$user_file_name.'"');
	$content = file_get_contents ($os_file_name);
	print($content);
	exit;
    }

}
