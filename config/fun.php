<?
function format_date($d)
{
    if (!$d or strtotime($d) == 0) return "-";
    return date("m/d/Y", strtotime($d));
}

// namespace autoloader
function __autoload($class_name)
{
    require_once(str_replace("\\", "/", $class_name).".php");
}

function match_or_link($url, $name)
{
    if (url_matches($url)) 
        return $name;
    else 
        return "<a href='$url'>$name</a>";
}

function render($controller, $action, $id=null, $assigns=null)
{
    $controller_object= new $controller();
    if ($id) $controller_object->id= $id;
    $controller_object->call($action, $assigns);
}

function render_main()
{
    $controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;    
    $action = ($_REQUEST["action"]) ? $_REQUEST['action'] : DEFAULT_ACTION;
    $id = ($_REQUEST["id"]) ? $_REQUEST['id'] : null;
        
    render($controller, $action, $id);
}


function classname_only($classname)
{
    return preg_replace("/([A-Za-z0-9]*\\\\)/", "", $classname);
}

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

function get_url_parts($url)
{
    $split= explode("/",substr($url, 1)); // trim the front slash and split
    return array($split[0], $split[1], $split[2]);
        
}
?>
