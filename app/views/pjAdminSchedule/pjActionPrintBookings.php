<?php
if($tpl['status'] == 200)
{
	if(isset($tpl['location_arr']))
	{
		?>
		<div style="font-weight: bold; margin-bottom: 10px; overflow: hidden;">
			<div style="float: left;"><?php __('lblLocation')?>:&nbsp;<?php echo $tpl['location_arr']['location'];?></div>
		</div>
		<?php
	} 
	?>
	<div style="font-weight: bold; margin-bottom: 10px; overflow: hidden;">
		<div style="float: left;"><?php __('lblTotalPassengers')?>:&nbsp;<?php echo $tpl['total_passengers'];?></div>
	</div>
	<?php
	foreach($tpl['ticket_arr'] as $v)
	{
		?>
		<div style="font-weight: bold; margin-bottom: 10px; overflow: hidden;">
			<div style="float: left;"><?php echo $v['title']; ?>:&nbsp;<?php echo $v['total_tickets']; ?></div>
		</div>
		<?php
	} 
	?>
	<div style="font-weight: bold; margin-bottom: 10px; overflow: hidden;">
		<div style="float: left;"><?php __('lblTotalBookings')?>:&nbsp;<?php echo $tpl['total_bookings'];?></div>
	</div>
	<div style="font-weight: bold; margin-bottom: 10px; overflow: hidden;">
		<div style="float: left;"><?php __('lblBus')?>:&nbsp;<?php echo $tpl['bus_arr']['route']?>, <?php echo pjUtil::formatTime($tpl['bus_arr']['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?> - <?php echo pjUtil::formatTime($tpl['bus_arr']['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?> <?php __('lblOn');?> <?php echo date($tpl['option_arr']['o_date_format'], strtotime($_GET['date']));?></div>
		<div style="float: right;"><?php __('lblCurrentDateTime')?>:&nbsp;<?php echo pjUtil::formatDate(date('Y-m-d'), "Y-m-d", $tpl['option_arr']['o_date_format']);?> <?php echo pjUtil::formatTime(date('H:i:s'), "H:i:s", $tpl['option_arr']['o_time_format']);?></div>
	</div>
	
	<table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
		<thead>
			<tr>
				<th><?php __('lblClient');?></th>
				<th style="width: 120px;"><?php __('lblPhone');?></th>
				<th style="width: 100px;"><?php __('lblFrom');?></th>
				<th style="width: 100px;"><?php __('lblTo');?></th>
				<th style="width: 250px;"><?php __('lblNotes');?></th>
				<th style="width: 120px;"><?php __('lblTicket');?></th>
				<th style="width: 80px;"><?php __('lblSeats');?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if(count($tpl['booking_arr']) > 0)
			{
				$person_titles = __('personal_titles', true, false);
				foreach($tpl['booking_arr'] as $v)
				{
					$tickets = $v['tickets'];
					$cnt_tickets = count($tickets);
					$seats = join(", ", $v['seats']);
					$client_name_arr = array();
					if(!empty($v['c_title']))
					{
						$client_name_arr[] = $person_titles[$v['c_title']];
					}
					if(!empty($v['c_fname']))
					{
						$client_name_arr[] = pjSanitize::clean($v['c_fname']);
					}
					if(!empty($v['c_lname']))
					{
						$client_name_arr[] = pjSanitize::clean($v['c_lname']);
					}
					if($cnt_tickets > 1)
					{
						foreach($tickets as $k => $t)
						{
							if($k == 0)
							{
								?>
								<tr>
									<td rowspan="<?php echo $cnt_tickets;?>"><?php echo join(" ", $client_name_arr);?></td>
									<td rowspan="<?php echo $cnt_tickets;?>"><?php echo pjSanitize::clean($v['c_phone']);?></td>
									<td rowspan="<?php echo $cnt_tickets;?>"><?php echo pjSanitize::clean($v['from_location']);?></td>
									<td rowspan="<?php echo $cnt_tickets;?>"><?php echo pjSanitize::clean($v['to_location']);?></td>
									<td rowspan="<?php echo $cnt_tickets;?>"><?php echo nl2br(pjSanitize::clean($v['c_notes']));?></td>
									<td><?php echo $t;?></td>
									<td rowspan="<?php echo $cnt_tickets;?>"><?php echo $seats;?></td>
								</tr>
								<?php
							}else{
								?>
								<tr>
									<td><?php echo $t;?></td>
								</tr>
								<?php
							}
						}
					}else{
						?>
						<tr>
							<td><?php echo join(" ", $client_name_arr);?></td>
							<td><?php echo pjSanitize::clean($v['c_phone']);?></td>
							<td><?php echo pjSanitize::clean($v['from_location']);?></td>
							<td><?php echo pjSanitize::clean($v['to_location']);?></td>
							<td><?php echo nl2br(pjSanitize::clean($v['c_notes']));?></td>
							<td><?php echo $tickets[0];?></td>
							<td><?php echo $seats;?></td>
						</tr>
						<?php
					}
				}
			} else {
				?>
				<tr>
					<td colspan="7"><?php __('gridEmptyResult');?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<?php
}else{
	$print_statuses = __('print_statuses', true, false);
	?><div style="margin-bottom: 12px; overflow: hidden;"><?php echo $print_statuses[$tpl['status']]?></div><?php
} 
?>