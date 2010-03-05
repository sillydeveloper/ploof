<?
function format_date($d)
{
    if (!$d or strtotime($d) == 0) return "-";
    return date("m/d/Y", strtotime($d));
}

/**
 * Autoload classes
 */
function __autoload($class_name)
{
    if (file_exists(str_replace("\\", "/", $class_name).".php"))
        require_once(str_replace("\\", "/", $class_name).".php");
    elseif (IN_UNIT_TESTING and file_exists("./test/temp/".$class_name.".php"))
        require_once("test/temp/".$class_name.".php");
    else
        throw new Exception("Hey, $class_name doesn't seem to exist...");
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
function render($controller, $action, $id=null, $assigns=null)
{
    $controller_object= new $controller();
    if ($id) $controller_object->id= $id;
    $controller_object->call($action, $assigns);
}

/**
 * Should be called in your layout where you want the main content pulled in.
 */
function render_main()
{
    $controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;    
    $action = ($_REQUEST["action"]) ? $_REQUEST['action'] : DEFAULT_ACTION;
    $id = ($_REQUEST["id"]) ? $_REQUEST['id'] : null;        
    render($controller, $action, $id);
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
function url_matches($url)
{
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
 * If not obvious, consider a career change.
 */
function get_url_parts($url)
{
    $split= explode("/",substr($url, 1)); // trim the front slash and split
    return array($split[0], $split[1], $split[2]);       
}

//--------------------------------------------------
//          FORM HELPERS
//--------------------------------------------------

/**
 * Create a text input field.
 */
function form_text($object, $name, $title='')
{
    return "<input class='input' type='text'  name='data[".classname_only($object::classname())."][$name][]' value='".$object->$name."' title=\"$title\"/>";
}

function form_select($object, $name, $options, $class='input')
{
    $html="<select class='$class' id='$name' name='data[".classname_only($object::classname())."][$name][]'>";
    foreach($options as $k=>$o)
    {
        // assume it's an array of $object objects
        if (is_object($o))
            $o= $o->$name;
            
        $html.= "<option value=\"$k\" ";
        if ($object->$name == $o)
            $html.= "selected='selected' ";
        $html.=">".$o."</option>";
    }
    $html.="</select>";
    return $html;
}

function form_select_simple($name, $options, $select=null, $class='input')
{
    $html="<select class='$class' id='$name' name='data[$name][]'>";
    foreach($options as $k=>$o)
    {
        $html.= "<option value=\"$k\"";
        if ($select == $o)
            $html.= "selected='selected' ";
        $html.=">".$o."</option>";
    }
    $html.="</select>";
    return $html;
}

function form_checkbox($object, $name, $class='input')
{
    if ($object->$name)
        $checked= "checked='checked'"; 

    $html= "<input class='$class' id='$name' type='checkbox' name='_$name' $checked onClick='toggle_hidden_checkbox(\"#hidden_checkbox_$name\")'/>";

    $html.="<input type='hidden' id='hidden_checkbox_$name' name='data[".classname_only($object::classname())."][$name][]' value='".$object->$name."'/>";

    return $html;
}

function form_checkbox_simple($name, $value, $checked=false, $class='input')
{
    if ($checked)
        $checked= "checked='checked'";

    $html= "<input class='$class' id='$name' type='checkbox' name='_$name' $checked onClick='toggle_hidden_checkbox(\"#hidden_checkbox_$name\")'/>";

    $html.="<input type='hidden' id='hidden_checkbox_$name' name='data[$name][]' value='".$value."'/>";

    return $html;
}

function form_radio()
{
    
}

function form_radio_simple($name, $value, $checked, $class='input')
{
    if ($checked)
        $checked= "checked='checked'";
        
    $html= "<input class='$class' id='$name' type='radio' name='data[$name][]' $checked value='$value'/>";
    
    return $html;
}
?>
