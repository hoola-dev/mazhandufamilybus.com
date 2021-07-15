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
	
	pjUtil::printNotice(__('infoBusesTitle', true, false), __('infoBusesDesc', true, false)); 
	?>
	<div class="b10">
    <?php if ($controller->isAdmin() || $controller->isBusAdmin()) { ?>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left r5">
			<input type="hidden" name="controller" value="pjAdminBuses" />
			<input type="hidden" name="action" value="pjActionCreate" />
			<input type="submit" class="pj-button" value="+ <?php __('lblAddBus'); ?>" />
		</form>
    <?php } ?>
		<form action="" method="get" class="pj-form frm-filter">
			<input type="text" name="q" class="pj-form-field pj-form-field-search w150 float_left" placeholder="<?php __('btnSearch', false, true); ?>" />
			<span class="block float_right">
				<label><?php __('lblFilterByRoute');?>:</label>
				<select name="route_id" id="filter_route_id" class="pj-form-field w200">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach($tpl['route_arr'] as $k => $v)
					{
						?><option value="<?php echo $v['id'];?>"><?php echo pjSanitize::clean($v['route']);?></option><?php
					} 
					?>
				</select>
			</span>
		</form>
		<?php
		$filter = __('filter', true);
		?>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	<script type="text/javascript">
	var pjGrid = pjGrid || {};
	pjGrid.jqDateFormat = "<?php echo pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']); ?>";
    pjGrid.jsDateFormat = "<?php echo pjUtil::jsDateFormat($tpl['option_arr']['o_date_format']); ?>";
    pjGrid.currentRoleId = <?php echo (int) $controller->getRoleId(); ?>;
	var myLabel = myLabel || {};
	myLabel.route = "<?php __('lblRoute', false, true); ?>";
	myLabel.from_to = "<?php __('lblFromTo', false, true); ?>";
    myLabel.depart_arrive = "<?php __('lblDepartArrive', false, true); ?>";
    <?php if ($controller->isAdmin() || $controller->isBusAdmin()) { ?>
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
    myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";    
    <?php } ?>
    var is_editor = <?php echo ($controller->isEditor()) ? 1 : 0; ?>;
	</script>
	<?php
}
?>