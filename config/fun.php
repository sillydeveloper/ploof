<?

if (!function_exists("format_date"))
{
    function format_date($d)
    {
        if (!$d or strtotime($d) == 0) return "-";
        return date("m/d/Y", strtotime($d));
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
 * Autoload classes
 */
function __autoload($class_name) 
{
    require_once(str_replace("\\", "/", $class_name).".php");
}

/**
 * Return name if 'current url' matches 'url', or <a href='url'>name</a>.
 *  Useful for navigation systems.
 */
function match_or_link($url, $name)
{
    if (url_matches($url)) 
        return $name;
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

function form_start($action)
{
    if ($_REQUEST['ajax'])
        $html= "<form method=POST class='ajax_form' action=\"$action\">";
    else
        $html= "<form method=POST action=\"$action\">";
        
    $html.= "<input type='hidden' name='form_content' value='1'/>";

    if ($_REQUEST["parent"])
    {
        $html.= "<input type='hidden' name='".$_REQUEST['parent']::object()."_id' value='".$_REQUEST['parentid']."'/>";
    }
    
    return $html;
}

function form_end()
{
    return "</form>";
}

/**
 * Create a text input field.
 */
function form_text($object, $name, $title='', $class='input')
{
    $cname= classname_only($object::classname());
    return "<input id='".$cname."_".$object->id."_$name' type='text' class='$class' name='".fname($object, $name)."' value='".$object->$name."' title=\"$title\"/>";
}

function form_text_simple($name, $value, $title='', $class='input', $id=null)
{
    return "<input id='$id' type='text' class='$class' name='$name' value='$value' title=\"$title\"/>";
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
            if ($object->$name == $o->id)
                $html.= "selected='selected' ";
            $html.=">".$o->$display."</option>";
        }
        else
        {
            $html.= "<option value=\"$k\" ";
            if ($object->$name == $k)
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
        if ($select == $k)
            $html.= "selected='selected' ";
        $html.=">".$o."</option>";
    }
    $html.="</select>";
    return $html;
}

function form_checkbox($object, $name, $value='', $label=null, $class='input')
{
    $checked= false;
    if ($object->$name == $value)
        $checked= "checked='checked'";
    
    $cname= classname_only($object::classname());
    $id= $cname."_".$object->id."_$name";

    $html= "<label onClick='toggle_checkbox(\"$id\")'><input class='$class' id='$id' type='checkbox' name='_$name' value='".$value."' $checked />";

    if ($checked)
        $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".fname($object, $name)."' value='".$object->$name."'/>";
    else
        $html.= "<input type='hidden' id='hidden_checkbox_$id' name='".fname($object, $name)."' value=''/>";
    
    $html.= "</label>";

    return $html;
}

function form_checkbox_simple($name, $value, $checked=false, $label=null, $class='input', $id=null)
{
    if ($checked)
        $checked= "checked='checked'";
    
    $id= md5($name);
    
    $html= "<label onClick='toggle_checkbox(\"$id\")'><input class='$class' id='$id' type='checkbox' name='_$name' value='$value' $checked/>";
    
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

function form_radio($object, $name, $values, $class='input')
{
    $id= md5($name);
    $html= "<input type='hidden' id='hidden_radio_$id' name='".fname($object, $name)."' value='".$object->$name."'/>";
    
    foreach($values as $k=>$v)
    {
        $checked= ($object->$name == $k) ? "checked='checked'" : "";
        $html.= "<input class='$class' type='radio' name='$k_$id' $checked value='$k' onMousedown='$(\"#hidden_radio_$id\").val(\"$k\")'> $v";
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
