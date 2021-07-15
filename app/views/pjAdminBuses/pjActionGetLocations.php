<?php
if(count($tpl['location_arr']) > 0)
{
	$time_format = $tpl['option_arr']['o_time_format'];
	if((strpos($tpl['option_arr']['o_time_format'], 'a') > -1 || strpos($tpl['option_arr']['o_time_format'], 'A') > -1))
	{
		$time_format = "h:i A";
	}
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
    $jqTimeFormat = pjUtil::jqTimeFormat($tpl['option_arr']['o_time_format']);

	foreach($tpl['location_arr'] as $k => $v)
	{
		$arrival_hour = $arrival_minute = null;
		$departure_hour = $departure_minute = null;
		$arrival_time = null;
        $departure_time = null;
        $day_num = null;
        $delay_date = null;
        $delay_minutes = null;
		if(isset($tpl['sl_arr']))
		{
			if(isset($tpl['sl_arr'][$v['city_id']]))
			{
				if(!empty($tpl['sl_arr'][$v['city_id']]['departure_time']))
				{
					list($departure_hour, $departure_minute,) = explode(":", $tpl['sl_arr'][$v['city_id']]['departure_time']);
					$departure_time = date($time_format, strtotime(date('Y-m-d'). ' '. $tpl['sl_arr'][$v['city_id']]['departure_time']));
				}
				if(!empty($tpl['sl_arr'][$v['city_id']]['arrival_time']))
				{
					list($arrival_hour, $arrival_minute,) = explode(":", $tpl['sl_arr'][$v['city_id']]['arrival_time']);
					$arrival_time = date($time_format, strtotime(date('Y-m-d'). ' '. $tpl['sl_arr'][$v['city_id']]['arrival_time']));
                }
                $day_num = $tpl['sl_arr'][$v['city_id']]['day_num'];
                $delay_date = $tpl['sl_arr'][$v['city_id']]['delay_date'];
                $delay_minutes = $tpl['sl_arr'][$v['city_id']]['delay_minutes'];
			}
		}
		
		?>
			<p>
				<label class="title"><?php echo pjSanitize::clean($v['name']); ?>:</label>
				<span class="inline_block">
					<?php 
					if($k > 0)
					{
						?>
						<span class="block b3">
							<label class="block float_left t5 w200"><?php __('lblArrivalTime'); ?>: </label>
							<input type="text" name="arrival_time_<?php echo $v['city_id']?>" value="<?php echo $arrival_time;?>" class="pj-form-field w80 timepick" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" lang="<?php echo $jqTimeFormat; ?>" <?php if ($controller->isEditor()) { ?> disabled <?php } ?>/>
						</span>  
                        <span class="block b3">
                            <label class="block float_left t5 w200">Arrival Day: </label>
                            <select name="day_num_<?php echo $v['city_id']?>" id="day_num_<?php echo $v['city_id']?>" class="pj-form-field w50 required" <?php if ($controller->isEditor()) { ?> disabled <?php } ?>>
                                <?php for ($i=0;$i<=5;$i++) { ?>
                                <option value="<?php echo $i; ?>"<?php echo $i == $day_num ? ' selected="selected"' : null;?>><?php echo $i; ?></option>
                                <?php } ?>
                            </select>
                        </span>  
						<?php
					}
					if($k < count($tpl['location_arr']) - 1)
					{
						?>
						<span class="block b3">
							<label class="block float_left t5 w200"><?php __('lblDepartureTime'); ?>: </label>
							<input type="text" name="departure_time_<?php echo $v['city_id']?>" value="<?php echo $departure_time;?>" class="pj-form-field w80 timepick" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" lang="<?php echo $jqTimeFormat; ?>" <?php if ($controller->isEditor()) { ?> disabled <?php } ?>/>
						</span>
                        <?php if ($tpl['is_edit'] == 1) { ?>
                            <!-- <span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
                                <label class="block float_left t5 w200">Delay Date: </label>
                                <input type="text" name="delay_date_<?php echo $v['city_id']?>" id="delay_date_<?php echo $v['city_id']?>" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($delay_date, "Y-m-d", $tpl['option_arr']['o_date_format']); ?>"/>
                                <span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
                            </span>
                            <span class="block b3">
                                <label class="block float_left t5 w200">Delay in Minutes: </label>
                                <input type="text" name="delay_minutes_<?php echo $v['city_id']?>" value="<?php echo $delay_minutes;?>" class="pj-form-field w80"/>
                            </span> -->
                        <?php } ?>
						<?php
					} 
					?>                    
				</span>
			</p>
		<?php
	}
}
?>