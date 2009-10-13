<?
ob_start();

include_once "../config/config.php";

core\Session::start();

$controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;

// get the layout
$layout= ($controller::$layout) ? $controller::$layout : DEFAULT_LAYOUT;

include "../view/layout/".$layout.VIEW_EXTENSION;

function render_controller_action($controller, $action, $id=null)
{
        $controller_object= new $controller();
        if ($id) $controller_object->id= $id;
        $controller_object->call($action);
}

function render()
{
    $controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;    
    $action = ($_REQUEST["action"]) ? $_REQUEST['action'] : DEFAULT_ACTION;
    $id = ($_REQUEST["id"]) ? $_REQUEST['id'] : null;
        
    render_controller_action($controller, $action, $id);
}

ob_end_flush();
?>