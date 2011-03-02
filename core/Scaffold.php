<?
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

class Scaffold extends Ploof
{
    private static $textbox_types= array("varchar", "datetime", "int");
    private static $textarea_types= array("text");
    
    static function build($object)
    {
        $ro= new \ReflectionObject($object);
        
        $html= "<table style='border:1px solid black; padding:5px'>\n";
        foreach($object->get_fields() as $key=>$value)
        {
            $html.= "  <tr style='border:1px solid black;'>\n";
            $html.= "      <td  >\n";
            $html.= "          ".$key."\n";
            $html.= "      </td>\n";
            $html.= "      <td>\n";
            
            if ($key == PRIMARY_KEY)
                $html.= "          ".$object->$key.self::hidden($key, $object->$key)."\n";
            elseif (in_array($object->get_field_type($key), self::$textbox_types))
                $html.= "          ".self::textbox($key, $object->$key)."\n";
            elseif (in_array($object->get_field_type($key), self::$textarea_types))    
                $html.= "          ".self::textarea($key, $object->$key)."\n";
            $html.= "      </td>\n";
            $html.= "  </tr>\n";
        }
        $html.=     "<tr><td colspan=2 align='right'><input type='submit' value='save' /></td>";
        $html.= "</table>";
        return $html;
    }
    
    static function hidden($key, $val)
    {
        return "<input type='hidden' name='data[".$key."]' value='".$val."' />";
    }
    
    static function textbox($key, $val)
    {
        return "<input type='text' name='data[".$key."]' value='".$val."' />";
    }
    
    static function textarea($key, $val)
    {
        return "<textarea name='data[".$key."]'  cols=80 rows=50>".$val."</textarea>";
    }
}

?>