<?
ob_start();

include_once "../config/config.php";

core\Session::start();

$controller= ($_REQUEST["controller"]) ? $_REQUEST["controller"] : DEFAULT_CONTROLLER;

// get the layout
$layout= ($controller::$layout) ? $controller::$layout : DEFAULT_LAYOUT;

include "../view/layout/".$layout.VIEW_EXTENSION;

ob_end_flush();
?>