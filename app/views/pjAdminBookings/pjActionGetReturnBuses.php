<p>
	<label class="title"><?php __('lblReturnBus'); ?>:</label>
	<span class="inline-block">
		<select name="return_bus_id" id="return_bus_id" class="pj-form-field w300">
			<option value="">-- <?php echo count($tpl['bus_arr']) > 0 ? __('lblChoose', true, false) : __('lblNoBusBetween', true, false); ?>--</option>
			<?php
			foreach ($tpl['bus_arr'] as $k => $v)
			{
				?><option value="<?php echo $v['id']; ?>"<?php echo isset($tpl['arr']) ? ($v['id'] == $tpl['arr']['bus_id'] ? ' selected="selected"' : null) : null; ?> data-set="<?php echo !empty($v['seats_map']) ? 'T' : 'F';?>"><?php echo $v['route']; ?>, <?php echo $v['depart_arrive']; ?></option><?php
			}
			?>
		</select>
	</span>
</p>