<?
include_once "../config/config.php";

$controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;
$controller_object= new $controller();

// get the layout
$layout= ($controller_object->layout) ? $controller_object->layout : DEFAULT_LAYOUT;

//ob_start();
include("../view/layout/".$layout.VIEW_EXTENSION);

function render()
{
    $controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;    
    $controller_object= new $controller();
    $action = ($_REQUEST["action"]) ? $_REQUEST['action'] : DEFAULT_ACTION;
    $controller_object->call($action);
}

?>