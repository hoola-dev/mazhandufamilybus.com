<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjNewsModel extends pjAppModel
{
	protected $primaryKey = 'nw_id';
	
	protected $table = 'news';
	
	protected $schema = array(
		array('name' => 'nw_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'nw_title', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'nw_date', 'type' => 'date', 'default' => ':NULL'),
        array('name' => 'nw_link', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'nw_description', 'type' => 'text', 'default' => ':NULL'),
        array('name' => 'nw_image', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'nw_is_active', 'type' => 'tinyint', 'default' => ':0'),
        array('name' => 'nw_added_by', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'nw_added_on', 'type' => 'datetime', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjNewsModel($attr);
	}
}
?>