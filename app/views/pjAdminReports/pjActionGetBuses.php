<?php
if($tpl['bus_arr'] != null)
{
	?>
	<select name="bus_id" id="bus_id" class="pj-form-field w300 required">
		<option value="">-- <?php __('lblChoose'); ?>--</option>
		<?php
		foreach ($tpl['bus_arr'] as $k => $v)
		{
			?><option value="<?php echo $v['id']; ?>"><?php echo $v['route']; ?>, <?php echo $v['depart_arrive']; ?></option><?php
		}
		?>
	</select>
	<?php
}else{
	?>
	<select name="bus_id" id="bus_id" class="pj-form-field w300 required">
		<option value="">-- <?php __('lblChoose'); ?>--</option>
	</select>
	<?php
}
?>