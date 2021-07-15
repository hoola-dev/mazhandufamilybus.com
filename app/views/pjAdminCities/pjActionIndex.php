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
	?>
	<?php 
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/busmenu.php';
	pjUtil::printNotice(__('infoCitiesTitle', true, false), __('infoCitiesDesc', true, false)); 
	?>
    <?php if ($controller->isAdmin() || $controller->isBusAdmin()) { ?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
		<input type="hidden" name="controller" value="pjAdminCities" />
		<input type="hidden" name="action" value="pjActionCreate" />
		<input type="submit" class="pj-button" value="<?php __('btnAdd'); ?>" />
		<p>&nbsp;</p>
	</form>
    <?php } ?>
	
	<div id="grid"></div>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.city = "<?php __('lblCity'); ?>";
	myLabel.active = "<?php __('lblActive', false, true); ?>";
	myLabel.inactive = "<?php __('lblInactive', false, true); ?>";
    myLabel.status = "<?php __('lblStatus', false, true); ?>";
    <?php if ($controller->isAdmin() || $controller->isBusAdmin()) { ?>
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
    myLabel.delete_confirmation = "<?php __('delete_confirmation'); ?>";
    <?php } ?>
	</script>
	<?php
}
?>