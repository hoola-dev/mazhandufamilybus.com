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
    $number_of_locations = count($tpl['location_arr']);  
    $location_arr = array();
    $first_id = $last_id = '';
    foreach($tpl['location_arr'] as $k => $v)
    {
        if($k == 0)
        {
            $first_id = $v['city_id'];
        }
        if($k == $number_of_locations - 1)
        {
            $last_id = $v['city_id'];
        }
        $location_arr[] = $v['name'];
    }
    if ($number_of_locations - 2 != 0)
    {
        $col_width = intval((986 - 150) / ($number_of_locations - 2));
    } else {
        $col_width = 986 - 150;
    }

    $total_full_arr = array();
    $total_partly_arr = array();
    $full_pair_id = $first_id . '-' . $last_id;
    ?>
    <p>
        <label class="bs-content"><?php __('lblBus')?>: <?php echo pjSanitize::clean($tpl['bus_arr']['route']);?></label>
    </p>
    <p>
        <label class="bs-content float_left w200"><?php __('lblDeparture')?>: <?php echo pjUtil::formatTime($tpl['bus_arr']['departure_time'], "H:i:s", $tpl['option_arr']['o_time_format']);?></label>
        
        <label class="bs-content float_left w200"><?php __('lblArrival')?>: <?php echo pjUtil::formatTime($tpl['bus_arr']['arrival_time'], "H:i:s", $tpl['option_arr']['o_time_format']);?></label>
    </p>
    <p class="b20">
        <label class="bs-content"><?php __('lblRoute')?>: <?php echo join(", ", $location_arr);?></label>
    </p>
    <p>
        <label class="bs-content"><?php __('lblTotalTravels')?>: <?php echo $tpl['total_travels'];?></label>
    </p>
    <p>
        <label class="bs-content"><?php __('lblTotalBookings')?>: <?php echo $tpl['total_bookings'];?></label>
    </p>
    <p>
        <label class="bs-content"><?php __('lblTotalTicketsSold')?>: <?php echo $tpl['total_tickets'];?></label>
    </p>
    <p class="b20">
        <label class="bs-content"><?php __('lblTotalIncome')?>: <?php echo pjUtil::formatCurrencySign(number_format($tpl['total_income'], 2), $tpl['option_arr']['o_currency']);?></label>
    </p>
    <p>
        <label class="bs-content bold"><?php __('lblRouteTrips')?></label>
    </p>
    <table cellpadding="0" cellspacing="0" border="0" class="table b20">
        <tbody>
            <tr>
                <td style="width: 150px;">&nbsp;</td>
                <?php
                $j = 1;
                foreach($tpl['location_arr'] as $v)
                {
                    if($j > 1)
                    {
                        ?>
                        <td style="width: <?php echo $col_width;?>px;">
                            <?php echo pjSanitize::clean($v['name'])?>
                        </td>
                        <?php
                    }
                    $j++;
                } 
                ?>
            </tr>
            <?php
            foreach($tpl['location_arr'] as $k => $row)
            {
                if($k <= ($number_of_locations - 2))
                {
                    ?>
                    <tr>
                        <td><?php echo pjSanitize::clean($row['name'])?></td>
                        <?php
                        $j = 1;
                        foreach($tpl['location_arr'] as $col)
                        {
                            if($j > 1)
                            {
                                $pair_id = $row['city_id'] . '-' . $col['city_id'];
                                ?>
                                <td class="center">
                                    <?php
                                    if($col['order'] > $row['order'])
                                    { 
                                        if(isset($tpl['route_trips']['tickets'][$pair_id]) && isset($tpl['route_trips']['total'][$pair_id]))
                                        {
                                            $tickets = array_sum($tpl['route_trips']['tickets'][$pair_id]);
                                            $actual_total = array_sum($tpl['route_trips']['total'][$pair_id]);
                                            $total = number_format($actual_total, 2);                                            
                                            if($full_pair_id == $pair_id)
                                            {
                                                isset($total_full_arr['tickets']) ? $total_full_arr['tickets'] += $tickets : $total_full_arr['tickets'] = $tickets;
                                                isset($total_full_arr['total']) ? $total_full_arr['total'] += $actual_total : $total_full_arr['total'] = $actual_total;
                                            }else{                                                
                                                isset($total_partly_arr['tickets']) ? $total_partly_arr['tickets'] += $tickets : $total_partly_arr['tickets'] = $tickets;
                                                isset($total_partly_arr['total']) ? $total_partly_arr['total'] += $actual_total : $total_partly_arr['total'] = $actual_total;
                                            }
                                        }else{
                                            echo '--';
                                        }
                                    }else{
                                        echo '&nbsp;';
                                    } 
                                    ?>
                                </td>
                                <?php
                            }
                            $j++;
                        } 
                        ?>
                    </tr>
                    <?php
                }
            } 
            ?>
        </tbody>
    </table>
    <p>
        <label class="bs-content"><?php __('lblTotalFull')?>: <?php echo !empty($total_full_arr) ? $total_full_arr['tickets'] . ' / ' . pjUtil::formatCurrencySign(number_format($total_full_arr['total'], 2), $tpl['option_arr']['o_currency']) : '--';?></label>
    </p>
    <p class="b20">
        <label class="bs-content"><?php __('lblTotalPartly')?>: <?php echo !empty($total_partly_arr) ? $total_partly_arr['tickets'] . ' / ' . pjUtil::formatCurrencySign(number_format($total_partly_arr['total'], 2), $tpl['option_arr']['o_currency']) : '--';?></label>
    </p>

    <p>
        <label class="bs-content bold"><?php __('lblTimetable')?></label>
    </p>
    <table cellpadding="0" cellspacing="0" border="0" class="table b20">
        <tbody>
            <tr>
                <td style="width: 150px;">&nbsp;</td>
                <td style="width: 150px;"><?php __('lblTravels')?></td>
                <td style="width: 150px;"><?php __('lblNumberBookings')?></td>
                <td style="width: 150px;"><?php __('lblNumberTickets')?></td>
                <td style="width: 150px;">Total with Discount</td>
            </tr>
            <?php
            foreach($tpl['days'] as $k => $v)
            {
                ?>
                <tr>
                    <td style="width: 150px;"><?php echo $v;?></td>
                    <td class="center" style="width: 150px;"><?php echo isset($tpl['timetable_arr'][$k]['travels']) ? $tpl['timetable_arr'][$k]['travels'] : '--'; ?></td>
                    <td class="center" style="width: 150px;"><?php echo isset($tpl['timetable_arr'][$k]['bookings']) ? $tpl['timetable_arr'][$k]['bookings'] : '--'; ?></td>
                    <td class="center" style="width: 150px;"><?php echo isset($tpl['timetable_arr'][$k]['tickets']) ? $tpl['timetable_arr'][$k]['tickets'] : '--'; ?></td>
                    <td class="center" style="width: 150px;"><?php echo isset($tpl['timetable_arr'][$k]['total']) ? $tpl['timetable_arr'][$k]['total'] : '--'; ?></td>
                </tr>
                <?php
            } 
            ?>
            <tr>
                <td class="no_border"style="text-align: right;"><?php __('lblTotal')?>:</td>
                <td class="center no_border"><?php echo $tpl['total_travels'];?></td>
                <td class="center no_border"><?php echo $tpl['total_bookings'];?></td>
                <td class="center no_border"><?php echo $tpl['total_tickets'];?></td>
                <td class="center no_border"><?php echo pjUtil::formatCurrencySign(number_format($tpl['total_income'], 2), $tpl['option_arr']['o_currency']);?></td>
            </tr>
        </tbody>
    </table>

    <p>
        <label class="bs-content bold"><?php __('lblTicketTypes')?></label>
    </p>

    <table cellpadding="0" cellspacing="0" border="0" class="table b20">
        <tbody>
            <tr>
                <td style="width: 150px;">&nbsp;</td>
                <td style="width: 150px;"><?php __('lblNumberTickets')?></td>
                <td style="width: 150px;">Total without Discount</td>
            </tr>
            <?php
            $total_tickets = $total_amount = 0;
            foreach($tpl['ticket_arr'] as $k => $v)
            {
                $total_tickets += $v['total_tickets'];
                $total_amount += $v['total_amount'];
                ?>
                <tr>
                    <td style="width: 150px;"><?php echo $v['title'];?></td>
                    <td class="center" style="width: 150px;"><?php echo $v['total_tickets']; ?></td>
                    <td class="center" style="width: 150px;"><?php echo pjUtil::formatCurrencySign(number_format($v['total_amount'], 2), $tpl['option_arr']['o_currency']); ?></td>
                </tr>
                <?php
            } 
            ?>
            <tr>
                <td class="no_border"style="text-align: right;"><?php __('lblTotal')?>:</td>
                <td class="center no_border"><?php echo $total_tickets;?></td>
                <td class="center no_border"><?php echo pjUtil::formatCurrencySign(number_format($total_amount, 2), $tpl['option_arr']['o_currency']);?></td>
            </tr>
        </tbody>
    </table>
    <a href="#" class="pj-button bs-print-report"><?php __('btnPrint'); ?></a>
    <br/><br/><br/>
<?php } ?>