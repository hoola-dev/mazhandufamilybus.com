<?php
if(isset($_GET['pickup_id']))
{
	?>
	<select name="return_id" id="return_id" class="pj-form-field w200 required">
		<option value="">-- <?php __('lblChoose'); ?>--</option>
		<?php
		foreach($tpl['location_arr'] as $k => $v)
		{
			?><option value="<?php echo $v['id'];?>"><?php echo pjSanitize::clean($v['name']);?></option><?php
		} 
		?>
	</select>
	<?php
}
if(isset($_GET['return_id']))
{
	?>
	<select name="pickup_id" id="pickup_id" class="pj-form-field w200 required">
		<option value="">-- <?php __('lblChoose'); ?>--</option>
		<?php
		foreach($tpl['location_arr'] as $k => $v)
		{
			?><option value="<?php echo $v['id'];?>"><?php echo pjSanitize::clean($v['name']);?></option><?php
		} 
		?>
	</select>
	<?php
}
?>