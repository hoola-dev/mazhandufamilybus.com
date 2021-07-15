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
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	$statuses = __('booking_statuses', true, false);
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
	
	pjUtil::printNotice(__('infoBookingListTitle', true, false), __('infoBookingListDesc', true, false)); 
	?>
	
	<div class="b10 top--bottom--align d--flex">
        <?php if (!$controller->isInvestor()) { ?>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="add__book">
			<input type="hidden" name="controller" value="pjAdminBookings" />
			<input type="hidden" name="action" value="pjActionCreate" />
			<input type="submit" class="pj-button main__add__btn" value="+ <?php __('menuAddBooking'); ?>" />
		</form>
		<form action="" method="get" class="float_left pj-form frm-filter add__book__dropdown">
			<input type="text" name="q" class="pj-form-field pj-form-field-search w250 client-search" placeholder="<?php __('lblSearchBy', false, true); ?>" />
			<button type="button" class="pj-button pj-button-detailed client-dropdown"><span class="pj-button-detailed-arrow"></span></button>
		</form>
        <?php } ?>
		<div class="add__btn__grp">
			<a href="#" class="pj-button btn-all"><?php __('lblAll'); ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="confirmed"><?php echo $statuses['confirmed']; ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="pending"><?php echo $statuses['pending']; ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="cancelled"><?php echo $statuses['cancelled']; ?></a>
		</div>
		<br class="clear_both" />
	</div>
	
	<div class="pj-form-filter-advanced" style="display: none">
		<span class="pj-menu-list-arrow"></span>
		<form action="" method="get" class="form pj-form pj-form-search frm-filter-advanced">
			<div class="overflow float_left w350">
				<p>
					<label class="title"><?php __('lblFrom'); ?></label>
					<span class="pj-form-field-custom pj-form-field-custom-after">
						<input type="text" name="date_from" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>"/>
						<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
					</span>
				</p>
			</div>
			<div class="overflow float_left w350">
				<p>
					<label class="title"><?php __('lblTo'); ?></label>
					<span class="pj-form-field-custom pj-form-field-custom-after">
						<input type="text" name="date_to" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>"/>
						<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
					</span>
				</p>
				
			</div>
			<div class="overflow float_left">
				<p>
					<label class="title"><?php __('lblRoute'); ?></label>
					<span class="inline-block">
						<select name="route_id" id="route_id" class="pj-form-field w300">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach($tpl['route_arr'] as $k => $v)
							{
								?><option value="<?php echo $v['id'];?>"><?php echo pjSanitize::clean($v['route']);?></option><?php
							} 
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBus'); ?></label>
					<span class="inline-block">
						<select name="bus_id" id="filter_bus_id" class="pj-form-field w300">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach ($tpl['bus_arr'] as $k => $v)
							{
								?><option value="<?php echo $v['id']; ?>"><?php echo $v['route']; ?>, <?php echo $v['depart_arrive']; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSearch'); ?>" class="pj-button" />
					<input type="reset" value="<?php __('btnCancel'); ?>" class="pj-button" />
				</p>
			</div>
			<br class="clear_both" />
		</form>
	</div>
	
	<div id="grid"></div>
	<script type="text/javascript">
	var pjGrid = pjGrid || {};
	pjGrid.queryString = "";
	<?php
	if (isset($_GET['bus_id']) && (int) $_GET['bus_id'] > 0)
	{
		?>pjGrid.queryString += "&bus_id=<?php echo (int) $_GET['bus_id']; ?>";<?php
	}
	if (isset($_GET['route_id']) && (int) $_GET['route_id'] > 0)
	{
		?>pjGrid.queryString += "&route_id=<?php echo (int) $_GET['route_id']; ?>";<?php
	}
    ?>
    pjGrid.currentRoleId = <?php echo (int) $controller->getRoleId(); ?>;
	var myLabel = myLabel || {};
	myLabel.client = "<?php __('lblClient', false, true); ?>";
	myLabel.date_time = "<?php __('lblDateTime', false, true); ?>";
	myLabel.bus_route = "<?php __('lblBusRoute', false, true); ?>";
    myLabel.email = "<?php __('email', false, true); ?>";
    myLabel.agent = "Agent";
    myLabel.status = "<?php __('lblStatus'); ?>";
    <?php if (!$controller->isInvestor()) { ?>
	myLabel.exported = "<?php __('lblExport', false, true); ?>";
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
    myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
    <?php } ?>
	myLabel.pending = "<?php echo $statuses['pending']; ?>";
	myLabel.confirmed = "<?php echo $statuses['confirmed']; ?>";
	myLabel.cancelled = "<?php echo $statuses['cancelled']; ?>";
	</script>
	<?php
}
?>