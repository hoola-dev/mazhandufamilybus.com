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
		$bodies_text = str_replace("{SIZE}", ini_get('post_max_size'), @$bodies[$_GET['err']]);
		pjUtil::printNotice(@$titles[$_GET['err']], $bodies_text);
	}
	
	pjUtil::printNotice(__('infoBusTypesTitle', true, false), __('infoBusTypesDesc', true, false)); 
	?>
	<div class="b10">
    <?php if ($controller->isAdmin() || $controller->isBusAdmin()) { ?>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left r5">
			<input type="hidden" name="controller" value="pjAdminBusTypes" />
			<input type="hidden" name="action" value="pjActionCreate" />
			<input type="submit" class="pj-button" value="+ <?php __('lblAddBusType'); ?>" />
		</form>
    <?php } ?>
		<form action="" method="get" class="float_left pj-form frm-filter">
			<input type="text" name="q" class="pj-form-field pj-form-field-search w150" placeholder="<?php __('btnSearch', false, true); ?>" />
		</form>
		<?php
		$filter = __('filter', true);
		?>
		<div class="float_right t5">
			<a href="#" class="pj-button btn-all"><?php __('lblAll'); ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="T"><?php echo $filter['active']; ?></a>
			<a href="#" class="pj-button btn-filter btn-status" data-column="status" data-value="F"><?php echo $filter['inactive']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	<script type="text/javascript">
	<?php
	$_yesno_arr = __('_yesno', true, false); 
	?>
	var pjGrid = pjGrid || {};
    pjGrid.currentRoleId = <?php echo (int) $controller->getRoleId(); ?>;
	var myLabel = myLabel || {};
	myLabel.name = "<?php __('lblName', false, true); ?>";
	myLabel.map = "<?php __('lblMap', false, true); ?>";
    myLabel.seats = "<?php __('lblSeats', false, true); ?>";
    myLabel.layout = "Layout";
	myLabel.yes = "<?php echo $_yesno_arr['T']; ?>";
    myLabel.no = "<?php echo $_yesno_arr['F']; ?>";
    <?php if ($controller->isAdmin() || $controller->isBusAdmin()) { ?>
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
    myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
    <?php } ?>
	myLabel.active = "<?php __('lblActive', false, true); ?>";
	myLabel.inactive = "<?php __('lblInactive', false, true); ?>";
	myLabel.status = "<?php __('lblStatus', false, true); ?>";
	</script>
	<?php
}
?>