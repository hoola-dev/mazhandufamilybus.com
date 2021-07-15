<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFAQModel extends pjAppModel
{
	protected $primaryKey = 'fq_id';
	
	protected $table = 'faq';
	
	protected $schema = array(
		array('name' => 'fq_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'fq_title', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'fq_description', 'type' => 'text', 'default' => ':NULL'),
        array('name' => 'fq_is_active', 'type' => 'tinyint', 'default' => ':0'),
        array('name' => 'fq_added_by', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'fq_added_on', 'type' => 'datetime', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new pjFAQModel($attr);
	}
}
?>