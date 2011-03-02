<?php
// Copyright (c) 2010, ploof development team
// All rights reserved.
// 
// Redistribution and use in source and binary forms, with or without modification, are permitted provided 
// that the following conditions are met:
// 
// Redistributions of source code must retain the above copyright notice, this list of conditions and the 
// following disclaimer. 
// Redistributions in binary form must reproduce the above copyright notice, this list of 
// conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
// The names of its contributors may not be used to endorse or promote products derived from this software without 
// specific prior written permission.
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
// INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
// OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

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
    	    throw new exception\PloofException("File $os_file_name Not found");
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
    
    static function list_files($directory,$exempt = array('.','..','.ds_store','.git'),$files = array()) 
    { 
        $handle = opendir($directory); 
        while(false !== ($resource = readdir($handle))) { 
            if(!in_array(strtolower($resource),$exempt)) { 
                if(is_dir($directory.$resource.'/')) 
                    array_merge($files, 
                        self::list_files($directory.$resource.'/',$exempt,$files)); 
                else 
                    $files[] = $directory.$resource; 
            } 
        } 
        closedir($handle); 
        return $files; 
    }

}
