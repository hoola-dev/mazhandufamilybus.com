<?php
if(isset($_GET['pickup_id']))
{
	?>
	<select id="bsReturnId_<?php echo $_GET['index'];?>" name="return_id" class="form-control pjBsAutocomplete required" data-msg-required="<?php __('front_required_field', false, true);?>">
		<option value="">-- <?php __('front_choose'); ?>--</option>
		<?php
		foreach($tpl['location_arr'] as $k => $v)
		{
			?><option value="<?php echo $v['id'];?>"><?php echo stripslashes($v['name']);?></option><?php
		} 
		?>
	</select>
	<div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
	<?php
}
if(isset($_GET['return_id']))
{
	?>
	<select id="bsPickupId_<?php echo $_GET['index'];?>" name="pickup_id" class="form-control pjBsAutocomplete required" data-msg-required="<?php __('front_required_field', false, true);?>">
		<option value="">-- <?php __('front_choose'); ?>--</option>
		<?php
		foreach($tpl['location_arr'] as $k => $v)
		{
			?><option value="<?php echo $v['id'];?>"><?php echo stripslashes($v['name']);?></option><?php
		} 
		?>
	</select>
	<div class="help-block with-errors"><ul class="list-unstyled"></ul></div>
	<?php
}
?>