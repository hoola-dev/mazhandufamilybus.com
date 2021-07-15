<?php
if (pjObject::getPlugin('pjOneAdmin') !== NULL)
{
	$controller->requestAction(array('controller' => 'pjOneAdmin', 'action' => 'pjActionMenu'));
}
?>

<?php
$my_text = ($controller->isEditor()) ? 'My ' : '';
?>

<!-- <div class="leftmenu-top"></div> -->
<div class="leftmenu-middle">    
	<ul class="menu">
        <li style="text-align:center;">Welcome <?php echo $controller->getName(); ?></li>
        <li style="text-align:center;margin-bottom:20px;"><?php echo $controller->getRoleName(); ?></li>
        <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor() || $controller->isEditor()) { ?>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdmin' && $_GET['action'] == 'pjActionIndex' ? 'menu-focus' : NULL; ?>"><span class="menu-dashboard">&nbsp;</span><?php __('menuDashboard'); ?></a></li>
        <?php } ?>
        <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor() || $controller->isSuperAgent()) { ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminDailyReport&amp;action=pjActionIndex" class="<?php echo ($_GET['controller'] == 'pjAdminDailyReport' && $_GET['action'] == 'pjActionIndex') ? 'menu-focus' : NULL; ?>"><span class="menu-reservations">&nbsp;</span>Agent Daily Report</a></li>
        <?php } ?>
        <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor()) { ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminDailyReport&amp;action=pjActionAgent" class="<?php echo ($_GET['controller'] == 'pjAdminDailyReport' && $_GET['action'] == 'pjActionAgent') ? 'menu-focus' : NULL; ?>"><span class="menu-reservations">&nbsp;</span>Agent Report</a></li>
        <?php } ?>
        <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor()) { ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminDailyReport&amp;action=pjActionRoute" class="<?php echo ($_GET['controller'] == 'pjAdminDailyReport' && $_GET['action'] == 'pjActionRoute') ? 'menu-focus' : NULL; ?>"><span class="menu-reservations">&nbsp;</span>Route Report</a></li>
        <?php } ?>
        <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor()) { ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminDailyReport&amp;action=pjActionPaymentMethod" class="<?php echo ($_GET['controller'] == 'pjAdminDailyReport' && $_GET['action'] == 'pjActionPaymentMethod') ? 'menu-focus' : NULL; ?>"><span class="menu-reservations">&nbsp;</span>Payment Method Report</a></li>
        <?php } ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex" class="<?php echo ($_GET['controller'] == 'pjAdminSchedule') ? 'menu-focus' : NULL; ?>"><span class="menu-schedule">&nbsp;</span><?php __('menuSchedule'); ?></a></li>
        <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor() || $controller->isEditor()) { ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionIndex" class="<?php echo ($_GET['controller'] == 'pjAdminBookings' && $_GET['action'] == 'pjActionIndex') ? 'menu-focus' : NULL; ?>"><span class="menu-reservations">&nbsp;</span><?php echo $my_text; ?><?php __('menuBookings'); ?></a></li>
        <?php } ?>
        <?php if ($controller->isEditor()) { ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionOutsideBooking" class="<?php echo ($_GET['controller'] == 'pjAdminBookings' && $_GET['action'] == 'pjActionOutsideBooking') ? 'menu-focus' : NULL; ?>"><span class="menu-reservations">&nbsp;</span>Outside Bookings</a></li>
        <?php } ?>
        <?php
		if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor() || $controller->isEditor()) {
		?>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminBuses' ? 'menu-focus' : NULL; ?>"><span class="menu-buses">&nbsp;</span><?php __('menuBuses'); ?></a></li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminRoutes&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminRoutes' || $_GET['controller'] == 'pjAdminCities' ? 'menu-focus' : NULL; ?>"><span class="menu-routes">&nbsp;</span><?php __('menuRoutes'); ?></a></li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBusTypes&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminBusTypes' ? 'menu-focus' : NULL; ?>"><span class="menu-bus-types">&nbsp;</span><?php __('menuBusTypes'); ?></a></li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminReports&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminReports' ? 'menu-focus' : NULL; ?>"><span class="menu-reports">&nbsp;</span><?php __('menuReports'); ?></a></li>			
		<?php
        }
        ?>
         <?php
		if ($controller->isAdmin()) {
		?>
            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionIndex" class="<?php echo ($_GET['controller'] == 'pjAdminOptions' && in_array($_GET['action'], array('pjActionIndex', 'pjActionBooking', 'pjActionBookingForm', 'pjActionConfirmation', 'pjActionTemplate', 'pjActionTerm', 'pjActionContent'))) || in_array($_GET['controller'], array('pjAdminLocales', 'pjBackup', 'pjLocale', 'pjSms')) ? 'menu-focus' : NULL; ?>"><span class="menu-options">&nbsp;</span><?php __('menuOptions'); ?></a></li>
        <?php 
        }
        ?>
        <?php
        if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor() || $controller->isSuperAgent()) {
        ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminUsers&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminUsers' ? 'menu-focus' : NULL; ?>"><span class="menu-users">&nbsp;</span><?php __('menuUsers'); ?></a></li>
        <?php
        }
        ?>
        <?php
        if ($controller->isAdmin() ||$controller->isBusAdmin() || $controller->isInvestor() || $controller->isSuperAgent()) {
        ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminAgents&amp;action=pjActionIndex" class="<?php echo ($_GET['controller'] == 'pjAdminAgents' && ($_GET['action'] == 'pjActionIndex' || $_GET['action'] == 'pjActionCommission')) ? 'menu-focus' : NULL; ?>"><span class="menu-revenue">&nbsp;</span>Agents Revenue</a></li>
        <?php
        }
        ?>
        <?php
        if ($controller->isSuperAgent()) {
        ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminAgents&amp;action=pjActionCreditAllocation" class="<?php echo ($_GET['controller'] == 'pjAdminAgents' && $_GET['action'] == 'pjActionCreditAllocation') ? 'menu-focus' : NULL; ?>"><span class="menu-users">&nbsp;</span>Credit Allocation</a></li>
        <?php
        }
        ?>
        <?php
        if ($controller->isAdmin() ||$controller->isBusAdmin() || $controller->isSuperAgent()) {
        ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionGetCreditPassword" class="<?php echo $_GET['controller'] == 'pjAdmin' && $_GET['action'] == 'pjActionGetCreditPassword' ? 'menu-focus' : NULL; ?>"><span class="menu-getcredit">&nbsp;</span>Get Credit Password</a></li>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionCreditPassword" class="<?php echo $_GET['controller'] == 'pjAdmin' && $_GET['action'] == 'pjActionCreditPassword' ? 'menu-focus' : NULL; ?>"><span class="menu-change-password">&nbsp;</span>Change Credit Password</a></li>
        <?php
        }
        ?>
        <?php
        if ($controller->isEditor()) {
        ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminAgents&amp;action=pjActionCommission&id=<?php echo $controller->getUserId(); ?>" class="<?php echo ($_GET['controller'] == 'pjAdminAgents' && $_GET['action'] == 'pjActionCommission') ? 'menu-focus' : NULL; ?>"><span class="menu-users">&nbsp;</span>My Revenue</a></li>
        <?php
        }
        ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionProfile" class="<?php echo $_GET['controller'] == 'pjAdmin' && $_GET['action'] == 'pjActionProfile' ? 'menu-focus' : NULL; ?>"><span class="menu-profile">&nbsp;</span><?php __('menuProfile'); ?></a></li>
        <?php
        if ($controller->isAdmin() ||$controller->isBusAdmin()) {
        ?>
        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminNews&amp;action=pjActionIndex" class="<?php echo $_GET['controller'] == 'pjAdminNews' ? 'menu-focus' : NULL; ?>"><span class="menu-news">&nbsp;</span>News</a></li>
        <?php
        }
        ?>
        <?php if ($controller->isAdmin()) { ?>
            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionPreview" class="<?php echo $_GET['controller'] == 'pjAdminOptions' && $_GET['action'] == 'pjActionPreview' ? 'menu-focus' : NULL; ?>"><span class="menu-preview">&nbsp;</span><?php __('menuInstallPreview'); ?></a></li>
		<?php } ?>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionLogout"><span class="menu-logout">&nbsp;</span><?php __('menuLogout'); ?></a></li>
	</ul>
</div>
<div class="leftmenu-bottom"></div>