<?
// NOTE: This file will be DEPRECATED.

function rep($str,$c)
{
    for ($i=0; $i<$c; $i++) $ret=$ret.$str;
    return $ret;
}

function parse_content_type($file_name)
{
    return File::parse_content_type($file_name);
}

function render_file($os_file_name, $user_file_name)
{
    File::render($os_file_name, $user_file_name);
}

function sum_closure($elements, $closure_function)
{
    return Math::sum_closure($elements, $closure_function);
}

function sort_by_method(&$objects, $direction='A', $sortkey_function)
{
    return Sort::by_method($objects $direction, $sortkey_function);
}
            
if (!function_exists("format_date"))
{
    function format_date($d)
    {
        if (!$d or strtotime($d) == 0) return "";
        return date("m/d/Y", strtotime($d));
    }
}

if (!function_exists("format_date_sql"))
{
    function format_date_sql($d)
    {
        if (!$d or strtotime($d) == 0) return "";
        return date("Y-m-d H:i", strtotime($d));
    }
}

if (!function_exists("format_float"))
{
    function format_float($f)
    {
        if (!is_numeric($f)) return "";
        if (is_string($f) and strpos($f,'e')!==false)
        {
            $f = sprintf('%F', $f);
        }
        return $f;
    }
}

if (!function_exists("convert_controller_to_object_name"))
{
    function convert_controller_to_object_name($name)
    {
        return substr($name, 0, strlen($name)-1);
    }
}

/**
 * Return name if 'current url' matches 'url', or <a href='url'>name</a>.
 *  Useful for navigation systems.
 */
function match_or_link($url, $name)
{
    if (url_matches($url)) 
        return "<a href='$url' class='menumatch'>$name</a>";
    else 
        return "<a href='$url'>$name</a>";
}

/**
 * Render controller's action, using id and/or an array of assigns.
 */
function render($url, $assigns=null)
{
    $split= get_url_parts($url);
    if (count($split) > 3)
    {
        $_REQUEST['parent']= $split[0];
        $_REQUEST['parentid']= $split[1];
        $controller= $split[2];
        $action= $split[3];
        $id= $split[4];
    }
    else
    {
        $controller= $split[0];
        $action= $split[1];
        $id= $split[2];
    }
    
    if (!$id)
        $id= $_REQUEST["id"];
    
    $controller_object= new $controller();
    $controller_object->call($action, $assigns, $id);
}

/**
 * Should be called in your layout where you want the main content pulled in.
 */
function render_main()
{

    $controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;    
    $action = ($_REQUEST["action"]) ? $_REQUEST['action'] : DEFAULT_ACTION;
    $id = ($_REQUEST["id"]) ? $_REQUEST['id'] : null;
    $_REQUEST["main_controller"]= $controller;
    $_REQUEST["main_action"]= $action;
    render("/$controller/$action/$id");
}

/** 
 * Eliminate any namespacing, just return the classname.
 */
function classname_only($classname)
{
    return preg_replace("/([A-Za-z0-9]*\\\\)/", "", $classname);
}

/** 
 * Get the namespace
 */
function namespace_only($classname)
{
    return preg_replace("/(\\\\[A-Za-z0-9]*)$/", "", $classname);
}

/**
 * What is probably a horrible url matcher against the uri...
 */
function url_matches($url_to_match_against_uri)
{
    $url= $url_to_match_against_uri;
    $uri= $_SERVER['REQUEST_URI'];
    
    if ($url=="/")
        return ($uri=="/");
    
    list($url_con, $url_act, $url_id) = get_url_parts($url);
    list($uri_con, $uri_act, $uri_id) = get_url_parts($uri);
        
    if ($url_con and $url_act and $url_id)
        return  get_url_parts($url) == get_url_parts($uri);
    if ($url_con and $url_act)
        return ($url_con == $uri_con and $url_act == $uri_act);
    if ($url_con)
        return ($url_con == $uri_con);
}       


/**
 * If not obvious...
 */
function get_url_parts($url)
{
    $query_str= explode("?", $url);
    return explode("/",substr($query_str[0], 1)); // trim the front slash and split
}

function get_query_string($url)
{
    $query_str= explode("?", $url);
    return $query_str[1]; // trim the front slash and split
}

//--------------------------------------------------
//          FORM HELPERS
//--------------------------------------------------

function form_start($action, $id=null)
{
    return Form::start($action, $id);
}

function form_end()
{
    return Form::end();
}

/**
 * Create a text input field.
 */
function form_text_size($size_name)
{
    return Form::text_size($size_name);
}

function form_text($object, $name, $title=null, $class=null, $size=null)
{
    return Form::text($object, $name, array('title'=>$title, 'class'=>$class, 'size'=>$size));
}

function form_textarea($object, $name, $title=null, $class=null, $rows=null, $cols=null)
{
    return Form::textarea($object, $name, array('title'=>$title, 'class'=>$class, 'rows'=>$rows, 'cols'=>$cols));
    /*
    if ($title === null) $title = '';
    if ($class === null) $class = 'input';
    if ($rows === null) $rows = 4;
    if ($cols === null) $cols = 19;
    
    $cname= classname_only($object::classname());
    return "<textarea id='".$cname."_".$object->id."_$name' class='$class' name='".fname($object, $name)."' title=\"$title\" rows=\"$rows\" cols=\"$cols\">" . htmlentities($object->$name) . "</textarea>";*/
}

function form_text_simple($name, $value, $title=null, $class=null, $id=null, $size=null)
{
    $title= ($title === null) ? '' : 'title="'.$title.'"';
    if ($class === null) $class = 'input';
    if ($id === null) $class = '';
    if ($size === null) $size = '';
    $size = form_text_size($size);
    
    return "<input id='$id' type='text' class='$class' name='$name' value=\"".htmlentities($value)."\" $title size=\"$size\" />";
}

function form_hidden($object, $name)
{
    return "<input class='input' type='hidden'  name='".fname($object, $name)."' value='".$object->$name."'/>";
}

function form_hidden_simple($name, $value, $id=null)
{
    return "<input class='input' type='hidden'  name='$name' value='$value' id='$id'/>";
}

function form_select($object, $name, $options, $display=null, $class='input')
{
    $cname= classname_only($object::classname());
    $html= "<select  id='".$cname."_".$object->id."_$name' class='$class' name='".fname($object, $name)."'>";
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

function form_select_simple($name, $options, $select=null, $class='input', $id=null)
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


function form_checkbox($object, $name, $value=null, $label=null, $class='input')
{
    $checked= false;

    if ($object->$name === $value)
        $checked= "checked='checked'";
    
    $cname= classname_only($object::classname());
    $id= $cname."_".$object->id."_$name";

    $html= "<input onchange='toggle_checkbox(\"$id\", \"$value\")' class='$class' id='$id' type='checkbox' name='$id' value='".$value."' $checked />";

    if ($checked)
        $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".fname($object, $name)."' value='".$object->$name."'/>";
    else
        $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".fname($object, $name)."' value=''/>";
    


    return $html;
}

function form_checkbox_simple($name, $value, $checked=false, $label=null, $class='input', $id=null)
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

function form_radio($object, $name, $values, $class='input', $list_vertically=false)
{
    $id= md5($name."_".$object->id);
    $id_ploof= classname_only($object->classname())."_".$object->id."_$name";
    $hidden_val = ($object->$name == null) ? 0 : $object->$name;
    $html= "<input type='hidden' id='hidden_radio_$id' name='".fname($object, $name)."' value='". $hidden_val  ."'/>";
    
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

function form_radio_simple($name, $value, $checked, $class='input', $id=null)
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
function fname($object, $name)
{
	if (!$object) throw new Exception("No object for fname");
    $cname= classname_only($object::classname());
    return "data[$cname][$name][]";
}

function parent_url()
{
    if ($_REQUEST['parent'] and $_REQUEST['parentid'])
        return "/".$_REQUEST['parent']."/".$_REQUEST['parentid'];
    
    return "";
}
?>
