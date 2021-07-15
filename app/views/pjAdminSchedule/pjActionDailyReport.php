<?php
if($tpl['status'] == 200)
{
?>
    <h1>Daily Report - <?php echo date("F j, Y"); ?></h1>
	
	<table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
		<thead>
			<tr>
				<th style="width: 120px;">Customer</th>
				<th style="width: 100px;">Date / time</th>
				<th style="width: 100px;">Bus / Route</th>
                <th style="width: 100px;">Total</th>
				<th style="width: 250px;">Agent</th>
				<th style="width: 120px;">Status</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($tpl['booking_arr'] as $v) { ?>	
                <?php
                $date_time = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
				if(date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) == date($this->option_arr['o_date_format'], strtotime($v['stop_datetime'])))
				{
					$date_time = date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
				}
				
				$route_details .= $v['route_title'];
				$route_details .= ', ' . date($this->option_arr['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['arrival_time']));
				$route_details .= '<br/>'  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
				
				$v['route_details'] = $route_details;
                ?>
                <tr>
                    <td><?php echo pjSanitize::clean($v['c_fname'].' '.$v['c_lname']);?></td>
                    <td><?php echo pjSanitize::clean($date_time);?></td>
                    <td><?php echo pjSanitize::clean($route_details);?></td>
                    <td><?php echo pjSanitize::clean($v['total']);?></td>
                    <td><?php echo pjSanitize::clean($v['name']);?></td>
                    <td><?php echo pjSanitize::clean($v['status']);?></td>
                </tr>
            <?php } ?>							
		</tbody>
	</table>
	<?php
}else{
	$print_statuses = __('print_statuses', true, false);
	?><div style="margin-bottom: 12px; overflow: hidden;"><?php echo $print_statuses[$tpl['status']]?></div><?php
} 
?>