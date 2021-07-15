<?php
$column = 'departure';
$direction = 'ASC';
if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
{
	$column = $_GET['column'];
	$direction = strtoupper($_GET['direction']);
}
$date = date($tpl['option_arr']['o_date_format']);
if(isset($_GET['date']))
{
	$date = $_GET['date'];
}
$sql_date = pjUtil::formatDate($date, $tpl['option_arr']['o_date_format']);
?>
<table class="pj-table" cellspacing="0" cellpadding="0" style="width: 100%;">
	<thead>
		<tr>
			<th>
				<div class="pj-table-sort-label"><?php __('lblBus');?></div>
				<div class="pj-table-sort">
					<a href="#" class="pj-table-sort-up<?php echo $column == 'route' && $direction == 'ASC' ? ' pj-table-sort-up-active' : null;?>" data-column="route"></a>
					<a href="#" class="pj-table-sort-down<?php echo $column == 'route' && $direction == 'DESC' ? ' pj-table-sort-down-active' : null;?>" data-column="route"></a>
				</div>
			</th>
			<th style="width: 180px;">
				<div class="pj-table-sort-label"><?php __('lblDeparture');?></div>
				<div class="pj-table-sort">
					<a href="#" class="pj-table-sort-up<?php echo $column == 'departure' && $direction == 'ASC' ? ' pj-table-sort-up-active' : null;?>" data-column="departure"></a>
					<a href="#" class="pj-table-sort-down<?php echo $column == 'departure' && $direction == 'DESc' ? ' pj-table-sort-down-active' : null;?>" data-column="departure"></a>
				</div>
			</th>
			<th style="width: 180px;">
				<div class="pj-table-sort-label"><?php __('lblArrival');?></div>
				<div class="pj-table-sort">
					<a href="#" class="pj-table-sort-up<?php echo $column == 'arrive' && $direction == 'ASC' ? ' pj-table-sort-up-active' : null;?>" data-column="arrive"></a>
					<a href="#" class="pj-table-sort-down<?php echo $column == 'arrive' && $direction == 'DESC' ? ' pj-table-sort-down-active' : null;?>" data-column="arrive"></a>
				</div>
			</th>
			<th style="width: 180px;">
				<div class="pj-table-sort-label"><?php __('lblFTTickets');?></div>
				<div class="pj-table-sort">
					<a href="#" class="pj-table-sort-up<?php echo $column == 'tickets' && $direction == 'ASC' ? ' pj-table-sort-up-active' : null;?>" data-column="tickets"></a>
					<a href="#" class="pj-table-sort-down<?php echo $column == 'tickets' && $direction == 'DESC' ? ' pj-table-sort-down-active' : null;?>" data-column="tickets"></a>
				</div>
			</th>
			<th style="width: 180px;">
				<div class="pj-table-sort-label"><?php __('lblTotalTickets');?></div>
				<div class="pj-table-sort">
					<a href="#" class="pj-table-sort-up<?php echo $column == 'total_tickets' && $direction == 'ASC' ? ' pj-table-sort-up-active' : null;?>" data-column="total_tickets"></a>
					<a href="#" class="pj-table-sort-down<?php echo $column == 'total_tickets' && $direction == 'DESC' ? ' pj-table-sort-down-active' : null;?>" data-column="total_tickets"></a>
				</div>
			</th>
			<th style="width: 220px;">&nbsp;</th>
			<th style="width: 180px;">More</th>
		</tr>
	</thead>
	<tbody>
		<?php
		if(count($tpl['bus_arr']) > 0)
		{
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
				list($departure_time, $departure_id) = explode("~:~", $v['departure']);
				list($arrival_time, $arrival_id) = explode("~:~", $v['arrive']);
				?>
				<tr>
					<td class="<?php echo $total_tickets > 0 ? 'pj-table-row-clickable' : null;?>" <?php echo $total_tickets > 0 ? 'data-href="' .$_SERVER['PHP_SELF'] . '?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=' . $v['id'] . '&amp;date='.$date.'"' : null;?>><?php echo pjSanitize::clean($v['route']);?></td>
					<td class="<?php echo $total_tickets > 0 ? 'pj-table-row-clickable' : null;?>" <?php echo $total_tickets > 0 ? 'data-href="' .$_SERVER['PHP_SELF'] . '?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=' . $v['id'] . '&amp;date='.$date.'"' : null;?>><?php echo pjUtil::formatTime($departure_time, "H:i:s", $tpl['option_arr']['o_time_format']);?></td>
					<td class="<?php echo $total_tickets > 0 ? 'pj-table-row-clickable' : null;?>" <?php echo $total_tickets > 0 ? 'data-href="' .$_SERVER['PHP_SELF'] . '?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=' . $v['id'] . '&amp;date='.$date.'"' : null;?>><?php echo pjUtil::formatTime($arrival_time, "H:i:s", $tpl['option_arr']['o_time_format']);?></td>
					<td class="<?php echo $total_tickets > 0 ? 'pj-table-row-clickable' : null;?>" <?php echo $total_tickets > 0 ? 'data-href="' .$_SERVER['PHP_SELF'] . '?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=' . $v['id'] . '&amp;date='.$date.'"' : null;?>><?php echo $tickets;?></td>
					<td class="<?php echo $total_tickets > 0 ? 'pj-table-row-clickable' : null;?>" <?php echo $total_tickets > 0 ? 'data-href="' .$_SERVER['PHP_SELF'] . '?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=' . $v['id'] . '&amp;date='.$date.'"' : null;?>><?php echo $total_tickets;?></td>
					<td>
                    <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isEditor()) { ?>
						<a class="add-booking" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionCreate&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo $date;?>&amp;pickup_id=<?php echo $departure_id;?>&amp;return_id=<?php echo $arrival_id;?>" class="pj-button pj-link"><?php __('menuAddBooking'); ?></a>
					<?php } ?>
					</td>
					<td>
						<!-- <a href="#" class="pj-table-icon-menu pj-table-button"><span class="pj-button-arrow-down"></span></a> -->
						<a href="#" class="pj-table-icon-menu pj-table-button more--btn">
						   <img src="<?php echo PJ_INSTALL_URL; ?>app/web/img/backend/More.svg" alt="more">
						</a>
						<span style="display: none;" class="pj-menu-list-wrap">
							<span class="pj-menu-list-arrow"></span>
							<ul class="pj-menu-list">
                            <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isEditor()) { ?>
								<li><a href="index.php?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo $date;?>"><?php __('lblPassengersList'); ?></a></li>
                            <?php } ?>
                                <li><a href="index.php?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo $date;?>"><?php __('lblSeatsList'); ?></a></li>
								<li><a href="index.php?controller=pjAdminSchedule&amp;action=pjActionPrintBookings&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo $sql_date;?>" target="_blank"><?php __('lblPrintPassengersList'); ?></a></li>
								<li><a href="index.php?controller=pjAdminSchedule&amp;action=pjActionPrintSeats&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo $sql_date;?>" target="_blank"><?php __('lblPrintSeatsList'); ?></a></li>
								<li><a href="index.php?controller=pjAdminBookings&amp;action=pjActionIndex&amp;bus_id=<?php echo $v['id']?>"><?php __('lblViewTripBookings'); ?></a></li>
                            <?php if ($controller->isAdmin() || $controller->isBusAdmin()) { ?>
                                <li><a href="index.php?controller=pjAdminBuses&amp;action=pjActionTime&amp;id=<?php echo $v['id']?>"><?php __('lblEditBus'); ?></a></li>
								<li><a href="index.php?controller=pjAdminBuses&amp;action=pjActionNotOperating&amp;id=<?php echo $v['id']?>&amp;date=<?php echo $date;?>"><?php __('lblCancelBus'); ?></a></li>
                            <?php } ?>
							</ul>
						</span>
					</td>
				</tr>
				<?php
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
<input type="hidden" id="bs_schedule_date" name="bs_schedule_date" value="<?php echo $sql_date;?>"/>