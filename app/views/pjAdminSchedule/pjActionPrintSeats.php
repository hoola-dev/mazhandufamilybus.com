<?php
if($tpl['status'] == 200)
{ 
	?>
	<div style="font-weight: bold; margin-top: 10px; margin-bottom: 12px;"><?php __('lblBus')?>:&nbsp;<?php echo $tpl['bus_arr']['route']?>, <?php echo pjUtil::formatTime($tpl['bus_arr']['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?> - <?php echo pjUtil::formatTime($tpl['bus_arr']['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?> <?php __('lblOn');?> <?php echo date($tpl['option_arr']['o_date_format'], strtotime($_GET['date']));?></div>
	
	<table class="table" cellspacing="0" cellpadding="0" style="width: 100%;margin-bottom: 10px;">
		<thead>
			<tr>
				<th><?php __('lblSeats');?></th>
				<?php
				$total = 0;
				$switch = __('switch', true, false);
				$number_of_locations = count($tpl['location_arr']);
				foreach($tpl['location_arr'] as $k => $v)
				{
					$on_str = $off_str = 0;
					$_str = '<br/>';
					if($k < $number_of_locations - 1)
					{
						if(isset($tpl['on_arr'][$v['location_id']]))
						{
							$on_str = array_sum($tpl['on_arr'][$v['location_id']]);
							$total += $on_str;
							$_str .= ' ' . $switch['on']. ': ' . $on_str . ' /';
						}else{
							$_str .= ' ' . $switch['on']. ': 0' . ' /';
						}
					}
					if($k > 0)
					{	
						if(isset($tpl['off_arr'][$v['location_id']]))
						{
							$off_str = array_sum($tpl['off_arr'][$v['location_id']]);
							$total -= $off_str;
							$_str .= ' ' . $switch['off']. ': ' . $off_str . ' /';
						}else{
							$_str .= ' ' . $switch['off']. ': 0' . ' /';
						}
					}
					if($total > 0)
					{
						$_str .= ' ' . __('lblT', true, false) . ': ' . $total;
					}else{
						$_str .= ' ' . __('lblT', true, false) . ': 0';
					}
					?><th style="width: 120px;"><?php echo pjSanitize::clean($v['location']);?><br/><?php echo !empty($v['departure_time']) ? pjUtil::formatTime($v['departure_time'], "H:i:s", $tpl['option_arr']['o_time_format']) : '&nbsp;';?><?php echo $_str;?></th><?php
					if($k == 0)
					{
						$first_order = $v['order'];
					}
					if($k == count($tpl['location_arr']) - 1)
					{
						$last_order = $v['order'];
					}
				} 
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($tpl['seat_arr'] as $key => $seat)
			{
				?>
				<tr>
					<td><?php echo pjSanitize::clean($seat['name']);?></td>
					<?php
					if(isset($tpl['bs_arr'][$seat['id']]))
					{
						$bs_arr = $tpl['bs_arr'][$seat['id']];
						$first_col = 1;
						$person_titles = __('personal_titles', true, false);
						foreach($bs_arr as $k => $bs)
						{
							$colspan = $bs['return_order'] - $bs['pickup_order'];
							if($bs['return_order'] == $last_order)
							{
								$colspan++;
							}
							if($bs['pickup_order'] > $first_col)
							{
								$interval = $bs['pickup_order'] - $first_col;
								for($i = 1;$i <= $interval; $i++)
								{
									?><td>&nbsp;</td><?php
								}
							}
							
							$client_name_arr = array();
							if(!empty($bs['c_title']))
							{
								$client_name_arr[] = $person_titles[$bs['c_title']];
							}
							if(!empty($bs['c_fname']))
							{
								$client_name_arr[] = pjSanitize::clean($bs['c_fname']);
							}
							if(!empty($bs['c_lname']))
							{
								$client_name_arr[] = pjSanitize::clean($bs['c_lname']);
							}
	
							?><td colspan="<?php echo $colspan;?>" style="background-color: #ffcc99;font-weight: bold;"><?php echo join(" ", $client_name_arr);?></td><?php
							$first_col = $bs['return_order'];
							if($k == count($bs_arr) - 1 && $bs['return_order'] < $last_order)
							{
								$interval = $last_order - $bs['return_order'] + 1;
								for($i = 1;$i <= $interval; $i++)
								{
									?><td>&nbsp;</td><?php
								}
							}
						}
					}else{
						for($i = $first_order;$i <= $last_order; $i++)
						{
							?><td>&nbsp;</td><?php
						}
					}
					?>
				</tr>
				<?php
			} 
			?>
		</tbody>
	</table>
	<div style="float: left; overflow: hidden;line-height: 24px;">
		<?php __('lblSeatsLegends');?>
	</div>
	<?php
}else{
	$print_statuses = __('print_statuses', true, false);
	?><div style="margin-bottom: 12px; overflow: hidden;"><?php echo $print_statuses[$tpl['status']]?></div><?php
} 
?>