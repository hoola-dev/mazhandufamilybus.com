<?php
if($tpl['code'] == 200)
{ 
	?>
	<p>
		<label class="title"><?php __('lblSeatsMap'); ?></label>
		<span class="inline_block">
			<input type="file" name="seats_map" id="seats_map" class="pj-form-field" />
		</span>
	</p>
	<?php
}else{
	echo $tpl['code'];
} 
?>