<?php
if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			pjUtil::printNotice(NULL, $status[2]);
			break;
	}
} else { 
    pjUtil::printNotice('Agent Report of '.$tpl['name'].' : '.date("F j, Y",strtotime($tpl['start_date'])).' - '.date("F j, Y",strtotime($tpl['end_date'])),'This is the list of bookings created by the agent.');
    $week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
?>
    <div style="clear:both;height:auto;overflow:hidden;">
        <form id="frm_daily_report" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminDailyReport&amp;action=pjActionAgent" method="post" id="frmUpdateTime" class="pj-form form">
            <div style="clear:both;overflow:hidden;">Agent</div>
            <div style="clear:both;overflow:hidden;">
                <select name="agent_id" id="agent_id">
                    <?php if ($tpl['agents']) { ?>
                        <?php foreach ($tpl['agents'] as $agent) { ?>
                            <option value="<?php echo $agent['id']; ?>" <?php if ($agent['id'] == $tpl['agent_id']) { ?> selected <?php } ?>><?php echo $agent['name']; ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
            <div style="clear:both;overflow:hidden;margin-top:20px;">Start Date</div>
            <div style="clear:both;overflow:hidden;">
                <span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
                    <input type="text" name="start_date" id="start_date" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['start_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>"/>
                    <span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
                </span>
            </div>
            <div style="clear:both;overflow:hidden;margin-top:20px;">End Date</div>
            <div style="clear:both;overflow:hidden;">
                <span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
                    <input type="text" name="end_date" id="end_date" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['end_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>"/>
                    <span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
                </span>
            </div>
            <div style="clear:both;overflow:hidden;">
                <input type="submit" value="Show Report" style="cursor:pointer;"/>
            </div>
        </form>
    </div>
    <div style="clear:both;margin-top:30px;">
        <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 5%">Sl</th>
                    <th style="width: 10%">Channel</th>
                    <th style="width: 12%">Created</th>
                    <th style="width: 12%;">Date</th>                    
                    <th style="width: 14%;">Booking ID</th>            
                    <th style="width: 20%;">Route</th>                                 
                    <th style="width: 10%;">Status</th>
                    <th style="width: 20%;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tpl['booking_arr']) { ?>
                    <?php $i = 1; ?>
                    <?php $total = 0; ?>
                    <?php $total_confirmed = 0; ?>
                    <?php $total_app = 0; ?>
                    <?php $total_web = 0; ?>
                    <?php foreach($tpl['booking_arr'] as $v) { ?>	                        
                        <?php
                        if ($v['is_combined_api'] == 1) {
                            $channel = 'App';
                            $total_app++;
                        } else {
                            $channel = 'Web';
                            $total_web++;
                        }
                        $total += round($v['total'], 2);
                        if ($v['status'] == 'confirmed') {
                            $total_confirmed += round($v['total'], 2);
                        }
                        $created = date($tpl['option_arr']['o_date_format'] . ', ' . $tpl['option_arr']['o_time_format'], strtotime($v['created']));
                        $date_time = date($tpl['option_arr']['o_date_format'] . ', ' . $tpl['option_arr']['o_time_format'], strtotime($v['booking_datetime']));
                                                
                        $route_details = $v['route_title'];
                        $route_details .= ', ' . date($tpl['option_arr']['o_time_format'], strtotime($v['departure_time']));
                        $route_details .= '<br/>'  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
                        
                        $v['route_details'] = $route_details;
                        ?>
                        <tr>
                            <td><?php echo $i;?></td>
                            <td><?php echo $channel;?></td>
                            <td><?php echo $created;?></td>
                            <td><?php echo $date_time;?></td>                            
                            <td><?php echo $v['uuid'];?></td>
                            <td><?php echo $route_details;?></td>  
                            <td><?php echo $v['status'];?></td>                          
                            <td style="text-align:right;"><?php echo pjUtil::formatCurrencySign( number_format($v['total'], 2), $tpl['option_arr']['o_currency']);?></td>
                        </tr>                        
                        <?php $i++; ?>
                    <?php } ?>
                        <tr>
                            <td colspan="3">
                                Total App Bookings: <?php echo $total_app; ?><br><br>
                                Total Web Bookings: <?php echo $total_web; ?>
                            </td>
                            <td colspan="5" style="text-align:right;">
                                Total: <?php echo pjUtil::formatCurrencySign( number_format($total, 2), $tpl['option_arr']['o_currency']);?>                               
                            </td>
                        </tr>		
                <?php } else { ?>
                    <tr><td colspan="8">No Booking</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>    
<?php } ?>
