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

namespace core;

class URL {

    public static function get_url_parts($url)
    {
	$query_str= explode("?", $url);
	return explode("/",substr($query_str[0], 1)); // trim the front slash and split
    }

    public function get_query_string($url)
    {
	$query_str= explode("?", $url);
	return $query_str[1]; // trim the front slash and split
    }

   /**
    * Return name if 'current url' matches 'url', or <a href='url'>name</a>.
    *  Useful for navigation systems.
    */
    public function match_or_link($url, $name)
    {
        if ($this->url_matches($url)) 
            return "<a href='$url' class='menumatch'>$name</a>";
        else 
            return "<a href='$url'>$name</a>";
    }

    public function parent_url()
    {
	if ($_REQUEST['parent'] and $_REQUEST['parentid'])
	    return "/".$_REQUEST['parent']."/".$_REQUEST['parentid'];
	
	return "";
    }

    /**
    * What is probably a horrible url matcher against the uri...
    */
    public function url_matches($url_to_match_against_uri)
    {
        $url= $url_to_match_against_uri;
        $uri= $_SERVER['REQUEST_URI'];
        
        if ($url=="/")
            return ($uri=="/");
        
        list($url_con, $url_act, $url_id) = $this->get_url_parts($url);
        list($uri_con, $uri_act, $uri_id) = $this->get_url_parts($uri);
            
        if ($url_con and $url_act and $url_id)
            return $this->get_url_parts($url) == $this->get_url_parts($uri);
        if ($url_con and $url_act)
            return ($url_con == $uri_con and $url_act == $uri_act);
        if ($url_con)
            return ($url_con == $uri_con);
    }       

}

?>
