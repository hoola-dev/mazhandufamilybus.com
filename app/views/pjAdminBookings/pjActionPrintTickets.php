<?php
if(isset($tpl['template_arr']))
{ 
	?>
	<table class="table" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td><?php echo $tpl['template_arr'];?></td>
			</tr>
		</tbody>
	</table>
	<?php
}else{
	?>
	<table class="table" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td><?php !isset($tpl['pending_booking']) ? __('lblBookingNotConfirmed') : __('lblPendingBookingCannotPrint');?></td>
			</tr>
		</tbody>
	</table>
	<?php
} 
?>