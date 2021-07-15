<?php
if($tpl['status'] == 200)
{ 
	?>
	<div style="font-weight: bold; margin-bottom: 12px; overflow: hidden;">
		<div style="float: left;"><?php __('lblBusesOn')?>:&nbsp;<?php echo date($tpl['option_arr']['o_date_format'], strtotime($_GET['date']));?></div>
		<div style="float: right;"><?php __('lblCurrentDateTime')?>:&nbsp;<?php echo pjUtil::formatDate(date('Y-m-d'), "Y-m-d", $tpl['option_arr']['o_date_format']);?> <?php echo pjUtil::formatTime(date('H:i:s'), "H:i:s", $tpl['option_arr']['o_time_format']);?></div>
	</div>
	
	<table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
		<thead>
			<tr>
				<th>
					<?php __('lblBus');?>
				</th>
				<th style="width: 100px;">
					<?php __('lblDeparture');?>
				</th>
				<th style="width: 100px;">
					<?php __('lblArrival');?>
				</th>
				<th style="width: 60px;">
					<?php __('lblTickets');?>
				</th>
				<th style="width: 80px;">
					<?php __('lblTotalTickets');?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if(count($tpl['bus_arr']) > 0)
			{
				$date = date($tpl['option_arr']['o_date_format']);
				if(isset($_GET['date']))
				{
					$date = $_GET['date'];
				}
				foreach($tpl['bus_arr'] as $v)
				{
					$tickets = 0;
					if(!empty($v['tickets']) && $v['tickets'] > 0)
					{
						$tickets = $v['tickets'];
					}
					$total_tickets = 0;
					if(!empty($v['total_tickets']) && $v['total_tickets'] > 0)
					{
						$total_tickets = $v['total_tickets'];
					}
					?>
					<tr>
						<td><?php echo pjSanitize::clean($v['route']);?></td>
						<td><?php echo pjUtil::formatTime($v['departure_time'], "H:i:s", $tpl['option_arr']['o_time_format']);?></td>
						<td><?php echo pjUtil::formatTime($v['arrival_time'], "H:i:s", $tpl['option_arr']['o_time_format']);?></td>
						<td><?php echo $tickets;?></td>
						<td><?php echo $total_tickets;?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="5"><?php __('gridEmptyResult');?></td>
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