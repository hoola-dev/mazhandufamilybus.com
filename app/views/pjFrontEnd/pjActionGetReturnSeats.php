<?php
$STORE = @$_SESSION[$controller->defaultStore];

if(!empty($tpl['return_bus_type_arr']))
{
	$map = PJ_INSTALL_PATH . $tpl['return_bus_type_arr']['seats_map'];
	
	if (is_file($map))
	{
		$size = getimagesize($map);
		?>
		<div class="bsReturnMapHolder pjBsSeatsContainer" style="height: <?php echo $size[1] + 20;?>px;">
			<img id="return_map" src="<?php echo PJ_INSTALL_URL . $tpl['return_bus_type_arr']['seats_map']; ?>" alt="" style="margin: 0; border: none; position: absolute; top: 0; left: 0; z-index: 500" />
			<?php
			foreach ($tpl['return_seat_arr'] as $seat)
			{
				?><span rel="hi_<?php echo $seat['id']; ?>" class="rect empty<?php echo in_array($seat['id'], $tpl['booked_return_seat_arr']) ? ' bs-return-booked' : ' bs-return-available';?><?php echo isset($selected_seats_arr) ? ( empty($intersect) ? ( in_array($seat['id'], $selected_seats_arr) ? ' bs-return-selected' : null ) : null ) : null;?>" data-id="<?php echo $seat['id']; ?>" data-name="<?php echo $seat['name']; ?>" style="width: <?php echo $seat['width']; ?>px; height: <?php echo $seat['height']; ?>px; left: <?php echo $seat['left']; ?>px; top: <?php echo $seat['top']; ?>px; line-height: <?php echo $seat['height']; ?>px"><span class="bsInnerRect" data-name="hi_<?php echo $seat['id']; ?>"><?php echo stripslashes($seat['name']); ?></span></span><?php
			}
			?>
		</div>
		<?php
	} 
}else{
	?>
	<div class="bsSystemMessage">
		<?php
		$front_messages = __('front_messages', true, false);
		$system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[5]);
		$system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
		echo $system_msg; 
		?>
	</div>
	<?php
}
?>