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

class Form
{
    public static function start($action, $id=null, $html_option=null)
    {
        if ($_REQUEST['ajax'])
            $html= "<form method=POST class='ajax_form' action='$action'";
        else
            $html= "<form method=POST action='$action' enctype='multipart/form-data'";
        
        if ($html_option)
        {
            foreach($html_option as $k=>$v)
            {
                $html.= " ".$k."='".$v."'";
            }
        }
        
        $html.= ($id) ? " id='$id'>" : ">"; 
            
        $html.= "<input type='hidden' name='form_content' value='1'/>";

        if ($_REQUEST["parent"])
        {
            $html.= "<input type='hidden' name='".$_REQUEST['parent']::object()."_id' value='".$_REQUEST['parentid']."'/>";
        }
        
        return $html;
    }

    public static function end()
    {
        return "</form>";
    }
    
    static function view_id($object, $name)
    {
        if (!is_object($object))
            throw new \Exception('no object');

        return classname_only($object->classname())."_".$object->id."_$name";
    }
    
    
    // TODO: deprecated:
    public static function text_size($size_name)
    {
        $size = $size_name;
        switch (strtolower($size_name))
        {
            case 'x-small' :  $size =  4; break;
            case 'small'   :  $size = 10; break;
            case 'medium'  :  $size = 20; break;
            case 'large'   :  $size = 30; break;
            case 'x-large' :  $size = 45; break;
            case 'xx-large':  $size = 80; break;
        }
        return $size;
    }
    
    public static function password($object, $name, $attributes= array())
    {
        $cname = Meta::classname_only($object::classname());

        if ($object->get_repository()->is_date_datatype($object->get_field_type($name)))
            $value= Format::date($object->$name);
        else
            $value= htmlentities($object->$name);
                
        $html = "<input type='password' name='". Form::fname($object, $name) . "' id='" . $cname . "_" . $object->id . "_" . $name  . "' value='" . $value. "' "; 
        foreach ( $attributes as $name=>$value )
         {
            if ( $name == 'size')
            {
                $value = Form::text_size($value);
            }
            $html .= $name . "='" . $value . "' "; 
        }
        $html .= "/>";
        
        return $html;
    }

    public static function text($object, $name, $attributes= array())
    {
        $cname = Meta::classname_only($object::classname());

        if ($object->get_repository()->is_date_datatype($object->get_field_type($name)))
            $value= Format::date($object->$name);
        else
            $value= htmlentities($object->$name);
                
        $html = "<input type='text' name='". Form::fname($object, $name) . "' id='" . $cname . "_" . $object->id . "_" . $name  . "' value='" . \htmlspecialchars($value, ENT_QUOTES). "' "; 
        foreach ( $attributes as $name=>$value )
         {
            if ( $name == 'size')
            {
                $value = Form::text_size($value);
            }
            $html .= $name . "='" . $value . "' "; 
        }
        $html .= "/>";
        
        return $html; 
    }

    public static function textarea($object, $name, $attributes=array())
    {
        $cname= Meta::classname_only($object::classname());
        $html = "<textarea name='" . Form::fname($object, $name) . "' id='" . $cname . "_" . $object->id . "_" . $name . "' ";
        foreach ( $attributes as $name=>$value )
         {
            if ( $name = 'cols' || $name == 'rows')
            {
                $value = Form::text_size($value);
            }
            $html .= $name . "='" . $value . "' "; 
        }
        $html .= '>'. htmlentities($object->$name) . "</textarea>"; 
        return $html;
    }

    public static function hidden($object, $name)
    {
        return "<input type='hidden' class='input' name='" . Form::fname($object, $name) . "' value='" . $object->$name . "'/>";
    }

    public static function select($object, $name, $options, $display=null, $html_option=array(), $preamble=null)
    {
        $cname= Meta::classname_only($object::classname());
        $html= "<select id='".$cname."_".$object->id."_$name' name='".Form::fname($object, $name)."'";
        if (is_array($html_option))
        {
            foreach($html_option as $k=>$v)
            {
                $html.= " ".$k.'="'.str_replace('"', '\"', $v).'"';
            }
        }
        $html.= ">";
        if ($preamble)
        {
            $html.= "<option value='null'>".$preamble."</option>";
        }
        foreach($options as $k=>$o)
        {
            if (is_object($o))
            {
                $html.= "<option value=\"$o->id\" ";
                if (strcmp($object->$name, $o->id) == 0)
                    $html.= "selected='selected' ";
                $html.=">".$o->$display."</option>";
            }
            else
            {
                $html.= "<option value=\"$k\" ";
                    
                if (strcmp($object->$name, $k) == 0)
                    $html.= "selected='selected' ";
                $html.=">".$o."</option>";
            }
        }
        $html.="</select>";
        return $html;
    }

    public static function select_simple($name, $options, $select=null, $class='input', $id=null)
    {
        $html="<select id='$id' class='$class' name='$name'>";
    
        foreach($options as $k=>$o)
        {
            $html.= "<option value=\"$k\"";
            if (strcmp($select, $k) == 0)
                $html.= "selected='selected' ";
            $html.=">".$o."</option>";
        }
        $html.="</select>";
        return $html;
    }

    public static function checkbox($object, $name, $value="1", $attributes= array())
    {
        $checked= false;

        if ($object->$name === $value)
            $checked= "checked='checked'";
        
        $cname= Meta::classname_only($object::classname());
        $id= $cname."_".$object->id."_$name";

        $html= "<input onchange='toggle_checkbox(\"$id\", \"$value\")' class='$class' id='$id' type='checkbox' name='$id' value='".$value."' $checked ";
        
        foreach ( $attributes as $name=>$value )
         {
            if ( $name = 'cols' || $name == 'rows')
            {
                $value = Form::text_size($value);
            }
            $html .= $name . "='" . $value . "' "; 
        }
        $html.= "/>";

        if ($checked)
            $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".Form::fname($object, $name)."' value='".$object->$name."'/>";
        else
            $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".Form::fname($object, $name)."' value=''/>";

        return $html;
    }

    public static function checkbox_simple($name, $value, $checked=false, $label=null, $class='input', $id=null)
    {
        if ($checked)
            $checked= "checked='checked'";
        
        $id= md5($name."_".$value);
        
        $html= "<label style='cursor: default;' onClick='toggle_checkbox(\"$id\")'><input class='$class' id='$id' type='checkbox' name='$id' value='$value' $checked/>";
        
        if ($checked)
            $html.="<input type='hidden' id='hidden_checkbox_$id' name='$name' value='".$value."'/>";
        else
            $html.="<input type='hidden' id='hidden_checkbox_$id' name='$name' value=''/>";
        
        if (!$label)
            $html.= " $value";
        else
            $html.= " $label";
        
        $html.="</label>";
        
        return $html;
    }

    public static function radio($object, $name, $values, $class='input', $list_vertically=false)
    {
        $id= md5($name."_".$object->id);
        $id_ploof= Meta::classname_only($object->classname())."_".$object->id."_$name";
        $hidden_val = ($object->$name == null) ? 0 : $object->$name;
        $html= "<input type='hidden' id='hidden_radio_$id' name='".Form::fname($object, $name)."' value='". $hidden_val  ."'/>";
        
        foreach($values as $k=>$v)
        {
            $checked= ($object->$name == $k) ? "checked='checked'" : "";
            $option = "<input class='$class' type='radio' name='$k_$id' id='".$id_ploof."_$k' $checked value='$k' onMousedown='$(\"#hidden_radio_$id\").val(\"$k\")'> $v";
            if ($list_vertically)
                $option = "<span style='display:block;'>".$option."</span>";
            $html.= $option;
        }
        return $html;
    }

    public static function radio_simple($name, $value, $checked, $class='input', $id=null)
    {
        if ($checked)
            $checked= "checked='checked'";

        if (!$id)
            $id= $name."_".$value;
            
        $html= "<input class='$class' id='$id' type='radio' name='$name' $checked value='$value'/>";
        
        return $html;
    }

    /**
    * Get the form name for an object / value.
    */
    public static function fname($object, $name)
    {
        if (!$object) throw new \Exception("No object for fname");
        $cname= Meta::classname_only($object::classname());
        return "data[$cname][$name][]";
    }

}

?>
