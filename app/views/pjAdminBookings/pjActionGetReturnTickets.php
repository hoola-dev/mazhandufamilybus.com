<?php
ob_start();
if(isset($tpl['ticket_arr']))
{ 
	?>
	<p>
		<label class="title"><?php __('lblTickets');?>:</label>
		<span class="block overflow">
			<?php
			$seats_avail = $tpl['seats_available'];
			foreach($tpl['ticket_arr'] as $v)
			{
				if($v['price'] != '')
				{
					if($tpl['arr']['set_seats_count'] == 'T')
					{
						$seats_avail = $v['seats_count'] - $v['cnt_booked'];
					}
					$price = $v['price'] - ($v['price'] * $v['discount'] / 100);
					?>
					<span class="block b5 overflow">
						<label class="block float_left r5 t5 w150"><?php echo $v['ticket'];?></label>
						<select name="return_ticket_cnt_<?php echo $v['ticket_id'];?>" class="pj-form-field w60 r3 float_left bs-return-ticket" data-price="<?php echo $price;?>">
							<?php
							for($i = 0; $i <= $seats_avail; $i++)
							{
								?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php
							}
							?>
						</select>
						<label class="block float_left r5 t5">x</label>
						<label class="block float_left t5"><?php echo pjUtil::formatCurrencySign( number_format($price, 2), $tpl['option_arr']['o_currency']);?></label>
					</span>
					<?php
				}
			} 
			?>
		</span>
		<input type="hidden" id="bs_return_number_of_seats" name="bs_return_number_of_seats" value="<?php echo $seats_avail; ?>"/>
	</p>
	<?php
}
$ticket = ob_get_contents();
ob_end_clean();
pjAppController::jsonResponse(compact('ticket'));
?>