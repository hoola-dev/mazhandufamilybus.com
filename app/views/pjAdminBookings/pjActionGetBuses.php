<p>
	<label class="title"><?php __('lblBus'); ?>:</label>
	<span class="inline-block">
		<select name="bus_id" id="bus_id" class="pj-form-field w300 required">
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