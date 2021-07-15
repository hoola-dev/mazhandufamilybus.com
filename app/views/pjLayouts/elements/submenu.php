<?php
$active = " ui-tabs-active ui-state-active";
?>
<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top<?php echo $_GET['action'] == 'pjActionTime' ? $active : null;?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionTime&amp;id=<?php echo $tpl['arr']['id']?>"><?php __('lblTimes'); ?></a></li>
		<li class="ui-state-default ui-corner-top<?php echo $_GET['action'] == 'pjActionNotOperating' ? $active : null;?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionNotOperating&amp;id=<?php echo $tpl['arr']['id']?>"><?php __('lblNotOperating'); ?></a></li>
		<li class="ui-state-default ui-corner-top<?php echo $_GET['action'] == 'pjActionTicket' ? $active : null;?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionTicket&amp;id=<?php echo $tpl['arr']['id']?>"><?php __('lblTickets'); ?></a></li>
		<li class="ui-state-default ui-corner-top<?php echo $_GET['action'] == 'pjActionPrice' ? $active : null;?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionPrice&amp;id=<?php echo $tpl['arr']['id']?>"><?php __('lblPrices'); ?></a></li>
	</ul>
</div>
