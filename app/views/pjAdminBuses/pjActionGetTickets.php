<?php
if(count($tpl['ticket_arr']) > 0)
{
	?>
	<p>
		<label class="title w100"><?php __('lblTicket'); ?>:</label>
		<span class="inline_block">
			<select id="source_ticket_id" name="source_ticket_id" class="pj-form-field w250">
				<?php
				foreach ($tpl['ticket_arr'] as $v)
				{
					?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['title']); ?></option><?php
				}
				?>
			</select>
		</span>
	</p>
	<?php
}
?>