<?php
if (!defined("ROOT_PATH"))
{
	define("ROOT_PATH", dirname(__FILE__) . '/');
}
require ROOT_PATH . 'app/config/options.inc.php';

echo file_get_contents(PJ_INSTALL_URL."index.php?controller=pjCron&action=pjActionIndex");
?>