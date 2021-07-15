<div class="overflow pj-form form">
	<p>
		<label class="title"><?php __('lblTotalPassengers'); ?>:</label>
		<span class="inline_block" >
			<label class="content"><?php echo $tpl['total_passengers']; ?></label>
		</span>
	</p>
	<?php
	foreach($tpl['ticket_arr'] as $v)
	{
		?>
		<p>
			<label class="title"><?php echo $v['title']; ?>:</label>
			<span class="inline_block" >
				<label class="content"><?php echo $v['total_tickets']; ?></label>
			</span>
		</p>
		<?php
	} 
	?>
	<p>
		<label class="title"><?php __('lblTotalBookings'); ?>:</label>
		<span class="inline_block" >
			<label class="content"><?php echo $tpl['total_bookings']; ?></label>
		</span>
	</p>
</div>
<table class="pj-table" cellspacing="0" cellpadding="0" style="width: 100%;">
	<thead>
		<tr>
			<th><?php __('lblClient');?></th>
			<th style="width: 120px;"><?php __('lblPhone');?></th>
			<th style="width: 120px;"><?php __('lblFrom');?></th>
			<th style="width: 120px;"><?php __('lblTo');?></th>
			<th style="width: 100px;"><?php __('lblTicket');?></th>
			<th style="width: 100px;"><?php __('lblSeats');?></th>
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
								<td rowspan="<?php echo $cnt_tickets;?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate&amp;id=<?php echo $v['id']?>"><?php echo join(" ", $client_name_arr);?></a></td>
								<td rowspan="<?php echo $cnt_tickets;?>"><?php echo $v['c_phone'];?></td>
								<td rowspan="<?php echo $cnt_tickets;?>"><?php echo pjSanitize::clean($v['from_location']);?></td>
								<td rowspan="<?php echo $cnt_tickets;?>"><?php echo pjSanitize::clean($v['to_location']);?></td>
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
						<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate&amp;id=<?php echo $v['id']?>"><?php echo join(" ", $client_name_arr);?></a></td>
						<td><?php echo $v['c_phone'];?></td>
						<td><?php echo pjSanitize::clean($v['from_location']);?></td>
						<td><?php echo pjSanitize::clean($v['to_location']);?></td>
						<td><?php echo $tickets[0];?></td>
						<td><?php echo $seats;?></td>
					</tr>
					<?php
				}
			}
		} else {
			?>
			<tr>
				<td colspan="6"><?php __('gridEmptyResult');?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>