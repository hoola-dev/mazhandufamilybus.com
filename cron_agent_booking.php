<?php
if (!defined("ROOT_PATH"))
{
	define("ROOT_PATH", dirname(__FILE__) . '/');
}
require ROOT_PATH . 'app/config/config.inc.php';

$conn = new mysqli(PJ_HOST, PJ_USER, PJ_PASS,PJ_DB);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} else{
	$sql = "SELECT b.`id` FROM parezaonlinebus_schedule_bookings as b 
	join parezaonlinebus_schedule_users as u on b.`user_id` = u.`id` 
	join parezaonlinebus_schedule_roles r on u.`role_id` = r.`id` 
	WHERE b.`user_id` > 0
	AND u.`role_id` = 2
	AND b.`status` = 'pending'
	AND TIMEDIFF(NOW(),b.`created`) > 30";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$sqlUpdate = "Update `parezaonlinebus_schedule_bookings` set status = 'cancelled' where id = '" . $row['id']. "'";
			$UpdateResult = $conn->query($sqlUpdate);
		}
	}
}

?>