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
    pjUtil::printNotice('Agent Daily Report - '.date("F j, Y",strtotime($tpl['report_date'])),'This is the list of total bookings created and total commission earned by the agents.');
    $week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
?>
    <div style="clear:both;height:auto;overflow:hidden;">
        <form id="frm_daily_report" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminDailyReport&amp;action=pjActionIndex" method="post" id="frmUpdateTime" class="pj-form form">
            <span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
                <input type="text" name="report_date" id="report_date" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['report_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" onchange="javascript:submit_form();"/>
                <span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
            </span>
        </form>
    </div>
    <div style="clear:both;margin-top:30px;">
        <h2 style="margin-top:50px;">Bookings Created</h1>
        <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 5%">Sl</th>
                    <th style="width: 15%">Created</th>
                    <th style="width: 15%">Agent</th>
                    <th style="width: 15%;">Customer</th>            
                    <th style="width: 15%">Departure / Arrival</th>
                    <th style="width: 15%">Bus / Route</th>
                    <th style="width: 10%">Total</th>            
                    <th style="width: 10%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tpl['booking_arr']) { ?>
                    <?php $i = 1; ?>
                    <?php foreach($tpl['booking_arr'] as $v) { ?>	
                        <?php
                        $created = date($tpl['option_arr']['o_date_format'] . ', ' . $tpl['option_arr']['o_time_format'], strtotime($v['created']));
                        $date_time = date($tpl['option_arr']['o_date_format'] . ', ' . $tpl['option_arr']['o_time_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($tpl['option_arr']['o_date_format'] . ', ' . $tpl['option_arr']['o_time_format'], strtotime($v['stop_datetime']));
                        if(date($tpl['option_arr']['o_date_format'], strtotime($v['booking_datetime'])) == date($tpl['option_arr']['o_date_format'], strtotime($v['stop_datetime'])))
                        {
                            $date_time = date($tpl['option_arr']['o_date_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($tpl['option_arr']['o_time_format'], strtotime($v['booking_datetime'])) . ' - ' . date($tpl['option_arr']['o_time_format'], strtotime($v['stop_datetime']));
                        }
                        
                        $route_details = $v['route_title'];
                        $route_details .= ', ' . date($tpl['option_arr']['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($tpl['option_arr']['o_time_format'], strtotime($v['arrival_time']));
                        $route_details .= '<br/>'  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
                        
                        $v['route_details'] = $route_details;
                        ?>
                        <tr>
                            <td><?php echo $i;?></td>
                            <td><?php echo $created;?></td>
                            <td><?php echo $v['name'];?></td>
                            <td><?php echo $v['c_fname'].' '.$v['c_lname'];?></td>
                            <td><?php echo $date_time;?></td>
                            <td><?php echo $route_details;?></td>
                            <td><?php echo pjUtil::formatCurrencySign( number_format($v['total'], 2), $tpl['option_arr']['o_currency']);?></td>                
                            <td><?php echo $v['status'];?></td>
                        </tr>
                        <?php $i++; ?>
                    <?php } ?>		
                <?php } else { ?>
                    <tr><td colspan="8">No Booking</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div>
        <h2 style="margin-top:50px;">Agent Transaction</h1>
        <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 5%;">Sl</th>
                    <th style="width: 25%">Agent</th>
                    <th style="width: 27%;">Contact</th>          
                    <th style="width: 20%;">Total Booking</th>
                    <th style="width: 22%;">Total Commission</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tpl['transactions']) { ?>
                    <?php $i = 1; ?>
                    <?php foreach($tpl['transactions'] as $v) { ?>	
                        <tr>
                            <td><?php echo $i;?></td>
                            <td><?php echo $v['name'];?></td>
                            <td><?php echo $v['phone'].'<br>'.$v['email'];?></td>
                            <td><?php echo pjUtil::formatCurrencySign( number_format($v['booking_total'], 2), $tpl['option_arr']['o_currency']);?></td>
                            <td><?php echo pjUtil::formatCurrencySign( number_format($v['total_commission'], 2), $tpl['option_arr']['o_currency']);?></td>
                        </tr>
                        <?php $i++; ?>
                    <?php } ?>		
                <?php } else { ?>
                    <tr><td colspan="6">No Transaction</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
