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
	$u_statarr = __('u_statarr', true);
	
	pjUtil::printNotice('Credit Allocation History',"Below is a list of Sub Agents with Credit allocated by the Super Agent, the Commission percentage at that time and the Total Commission earned by the Sub Agent till that time"); 
	?>
	<div class="b10" style="margin-top:20px;">
		<form action="" method="get" class="float_left pj-form frm-filter">
			<input type="text" name="q" class="pj-form-field pj-form-field-search w150" placeholder="<?php __('btnSearch', false, true); ?>" />
		</form>
		<?php
		$filter = __('filter', true);
		?>
        <div class="float_right t5">
			<a href="#" class="pj-button btn-all"><?php __('lblAll'); ?></a>			
		</div>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
    <script type="text/javascript">
	var pjGrid = pjGrid || {};
	pjGrid.jsDateFormat = "<?php echo pjUtil::jsDateFormat($tpl['option_arr']['o_date_format']); ?>";
	pjGrid.currentUserId = <?php echo (int) $_SESSION[$controller->defaultUser]['id']; ?>;
	var myLabel = myLabel || {};
    myLabel.cm_credit_added = "Credit";
    myLabel.cm_commission_percent = "Commission%";
    myLabel.cm_commission = "Commission";
    myLabel.cm_total_commission = "Total Commission";
    myLabel.name = "Name";
    myLabel.cm_added_on = "Added On";
	</script>
	<?php
}
?>