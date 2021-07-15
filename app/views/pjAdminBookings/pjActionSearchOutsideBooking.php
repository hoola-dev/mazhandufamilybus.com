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
	
	$description = "Search outside bookings to update";
	pjUtil::printNotice('Search Outside Bookings', $description); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionSearchOutsideBooking" method="post" class="form pj-form" id="frmSearchOutSideBooking">
		<input type="hidden" name="search_outside_booking" value="1" />	
		
		<div>
            <div id="fromToBox">
                <p>
                    <label class="title">Unique Booking ID:</label>
                    <span class="pj-form-field-custom pj-form-field-custom-before">                        
                        <input type="text" id="unique_id" name="unique_id" class="pj-form-field number w150 required" value=""/>
                    </span>
                </p>
                <p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="Get Booking" class="pj-button" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBookings&action=pjActionOutsideBooking';" />
				</p>
            </div>
        </div>
	</form>
<?php
}
?>