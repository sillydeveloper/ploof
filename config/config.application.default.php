<?
### put your application specific configuration stuff here!
$db= new \plugins\DB\MysqliConnector('my_username', 'my_password', 'my_database', 'my_server');
$cache= new \plugins\cache\SessionCache();
$repository= new \core\Repository($db, $cache);
ApplicationModel::set_repository($repository);
?>