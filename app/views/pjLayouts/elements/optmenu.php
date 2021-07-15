<?php
$active = " ui-tabs-active ui-state-active";
?>
<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top<?php echo $_GET['controller'] != 'pjAdminOptions' || $_GET['action'] != 'pjActionIndex' ? NULL : $active; ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionIndex"><?php __('menuGeneral'); ?></a></li>
		<li class="ui-state-default ui-corner-top<?php echo ($_GET['controller'] == 'pjAdminOptions' && in_array($_GET['action'], array('pjActionBooking', 'pjActionBookingForm', 'pjActionConfirmation', 'pjActionTemplate', 'pjActionTerm', 'pjActionContent') )) || in_array($_GET['controller'], array('pjSms')) ? $active : NULL; ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionBooking"><?php __('menuBooking'); ?></a></li>
		<li class="ui-state-default ui-corner-top<?php echo $_GET['controller'] != 'pjLocale' ? NULL : $active; ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjLocale&amp;action=pjActionLocales&amp;tab=1"><?php __('menuLocales'); ?></a></li>
		<li class="ui-state-default ui-corner-top<?php echo $_GET['controller'] != 'pjBackup' ? NULL : $active; ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjBackup&amp;action=pjActionIndex"><?php __('menuBackup'); ?></a></li>
	</ul>
</div>
<?php
if(in_array($_GET['action'], array('pjActionBooking', 'pjActionBookingForm', 'pjActionConfirmation', 'pjActionTemplate', 'pjActionTerm', 'pjActionContent')) || in_array($_GET['controller'], array('pjSms')))
{
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/opt_submenu.php';
} 
?>