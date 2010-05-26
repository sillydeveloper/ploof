<?
ob_start();

include_once "../config/config.php";

core\Session::start();

$controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;

// get the layout
if (!$_REQUEST["ajax"])
{
    $layout= ($controller::$layout) ? $controller::$layout : DEFAULT_LAYOUT;
    include "../view/layout/".$layout.VIEW_EXTENSION;
}
else
{
    $action= ($_REQUEST["action"]) ? $_REQUEST["action"] : DEFAULT_ACTION;
    render("/$controller/$action/".$_REQUEST["id"]);
}


ob_end_flush();
?>