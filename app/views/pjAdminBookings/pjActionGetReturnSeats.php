<?php
if (isset($_POST['return_bus_id']) && (int)$_POST['return_bus_id'] > 0) {
	$map = $tpl['bus_type_arr']['seats_map'];
	if (is_file($map))
	{
		$size = getimagesize($map);
		?>
		<div class="bs-hint b10"><?php __('lblSelectSeatsHint')?></div>
		<div class="bs-seats-legend b10">
			<label><span class="bs-available-seats"></span><?php __('lblAvailableSeats');?></label>
			<label><span class="bs-selected-seats"></span><?php __('lblSelectedSeats');?></label>
			<label><span class="bs-booked-seats"></span><?php __('lblBookedSeats');?></label>
		</div>
		<div id="boxReturnMap">
			<div id="mapReturnHolder" style="position: relative; overflow: hidden; width: <?php echo $size[0]; ?>px; height: <?php echo $size[1]; ?>px; margin: 0 auto;">
				<img id="return_map" src="<?php echo $map; ?>" alt="" style="margin: 0; border: none; position: absolute; top: 0; left: 0; z-index: 500" />
				<?php
				foreach ($tpl['seat_arr'] as $seat)
				{
					?><span rel="hi_<?php echo $seat['id']; ?>" class="rect empty<?php echo in_array($seat['id'], $tpl['booked_seat_arr']) ? ' bs-return-booked' : ' bs-return-available';?><?php echo isset($_POST['booking_update']) ? (in_array($seat['id'], $tpl['seat_pair_arr']) ? ' bs-return-selected' : null) : null;?>" data-id="<?php echo $seat['id']; ?>" data-name="<?php echo $seat['name']; ?>" style="width: <?php echo $seat['width']; ?>px; height: <?php echo $seat['height']; ?>px; left: <?php echo $seat['left']; ?>px; top: <?php echo $seat['top']; ?>px; line-height: <?php echo $seat['height']; ?>px"><span class="bsInnerRect" data-name="hi_<?php echo $seat['id']; ?>"><?php echo stripslashes($seat['name']); ?></span></span><?php
				}
				?>
			</div>
		</div>
		<?php
	}else{
		?>
		<p>
			<label class="title"><?php __('lblReturnSeats'); ?>:</label>
			<span class="inline-block">
				<span class="block b5">
					<select name="assigned_return_seats[]" id="assigned_return_seats" class="pj-form-field required" multiple="multiple" size="5">
						<?php
						foreach ($tpl['seat_arr'] as $seat)
						{
							if(!in_array($seat['id'], $tpl['booked_seat_arr']))
							{
								?><option value="<?php echo $seat['id']; ?>"><?php echo stripslashes($seat['name']); ?></option><?php
							}
						}
						?>
					</select>
				</span>
				<a class="block" target="_blank" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?php echo $_POST['bus_id'];?>&amp;date=<?php echo $_POST['booking_date'];?>"><?php __('lblViewSeatsList');?></a>
			</span>
		</p>
		<?php
	}
} 
?>