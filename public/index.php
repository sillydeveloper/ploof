<?
include_once "../config/config.php";

$controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;
$controller_object= new $controller();

// get the layout
$layout= ($controller_object->layout) ? $controller_object->layout : DEFAULT_LAYOUT;

//ob_start();
include("../view/layout/".$layout.VIEW_EXTENSION);

function render_controller_action($controller, $action)
{
    $controller_object= new $controller();
    $controller_object->call($action);
}

function render()
{
    $controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;    
    $action = ($_REQUEST["action"]) ? $_REQUEST['action'] : DEFAULT_ACTION;
    render_controller_action($controller, $action);
}

?>