<?php

class Form
{
    public static start($action, $id=null)
    {
        if ($_REQUEST['ajax'])
            $html= "<form method=POST class='ajax_form' action='$action'";
        else
            $html= "<form method=POST action='$action'";
            
        $html.= ($id) ? " id='$id'>" : ">"; 
            
        $html.= "<input type='hidden' name='form_content' value='1'/>";

        if ($_REQUEST["parent"])
        {
            $html.= "<input type='hidden' name='".$_REQUEST['parent']::object()."_id' value='".$_REQUEST['parentid']."'/>";
        }
        
        return $html;
    }

    public static end()
    {
        return "</form>";
    }

    static function text_size($size_name)
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

    public static text($object, $name, $attributes=array())
    {
        $cname = Meta::classname_only($object::classname());
        $html = "<input type='text' name='". Form::fname($object, $name) . "' id='" . $cname . "_" . $object->id . "_" . $name  . "' value='" . htmlentities($object->$name). "' "; 
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

    public static textarea($object, $name, $attributes=array())
    {
        $cname= Meta::classname_only($object::classname());
        $html = "<textarea name='" . Form::fname($object, $name) . "' id='" . $cname . "_" . $object->id . "_" . $name . " ";
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

    public static hidden($object, $name)
    {
        return "<input type='hidden' class='input' name='" . Form::fname($object, $name) . "' value='" . $object->$name . "'/>";
    }

    public static select($object, $name, $options, $display=null, $class='input')
    {
        $cname= Meta::classname_only($object::classname());
        $html= "<select id='".$cname."_".$object->id."_$name' class='$class' name='".Form::fname($object, $name)."'>";
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

    public static form_select_simple($name, $options, $select=null, $class='input', $id=null)
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


    public static form_checkbox($object, $name, $value=null, $label=null, $class='input')
    {
        $checked= false;

        if ($object->$name === $value)
            $checked= "checked='checked'";
        
        $cname= Meta::classname_only($object::classname());
        $id= $cname."_".$object->id."_$name";

        $html= "<input onchange='toggle_checkbox(\"$id\", \"$value\")' class='$class' id='$id' type='checkbox' name='$id' value='".$value."' $checked />";

        if ($checked)
            $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".Form::fname($object, $name)."' value='".$object->$name."'/>";
        else
            $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".Form::fname($object, $name)."' value=''/>";

        return $html;
    }

    public static form_checkbox_simple($name, $value, $checked=false, $label=null, $class='input', $id=null)
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

    public static form_radio($object, $name, $values, $class='input', $list_vertically=false)
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

    public static form_radio_simple($name, $value, $checked, $class='input', $id=null)
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
    public static fname($object, $name)
    {
        if (!$object) throw new Exception("No object for fname");
        $cname= Meta::classname_only($object::classname());
        return "data[$cname][$name][]";
    }

}

?>
