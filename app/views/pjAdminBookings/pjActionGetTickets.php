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
				if($tpl['arr']['set_seats_count'] == 'T')
				{
					$seats_avail = $v['seats_count'] - $v['cnt_booked'];
				}
				if($v['price'] != '')
				{
					if($tpl['booking_arr'] && (int) $tpl['booking_arr']['back_id'] > 0 && $tpl['booking_arr']['is_return'] == 'F')
					{
						$price = $v['price'] - ($v['price'] * $v['discount'] / 100);
					}else{
						$price = $v['price'];
					}
					?>
					<span class="block b5 overflow">
						<label class="block float_left r5 t5 w150"><?php echo $v['ticket'];?></label>
						<select name="ticket_cnt_<?php echo $v['ticket_id'];?>" class="pj-form-field w60 r3 float_left bs-ticket" data-price="<?php echo $price;?>">
							<?php
							for($i = 0; $i <= $seats_avail; $i++)
							{
								?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php
							}
							?>
						</select>
						<label class="block float_left r5 t5">x</label>
						<label class="block float_left t5">
							<?php echo pjUtil::formatCurrencySign($price, $tpl['option_arr']['o_currency']);?>
						</label>
					</span>
					<?php
				}
			} 
			?>
		</span>
		<input type="hidden" id="bs_number_of_seats" name="bs_number_of_seats" value="<?php echo $seats_avail; ?>"/>
	</p>
	<?php
}
$ticket = ob_get_contents();
ob_end_clean();

ob_start();
?>
<?php if ($controller->isAllowDiscount()) { ?>
    <label class="title">Max discount: <?php echo $tpl['option_arr']['o_currency'].' '.$tpl['max_discount']; ?><br>Discount on ticket:</label>
    <span class="pj-form-field-custom pj-form-field-custom-before">
        <span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
        <input type="text" id="new_discount_on_ticket" name="new_discount_on_ticket" class="pj-form-field number w108" value="" onkeyup="javascript:check_discount(this.value);"/>
    </span>
<?php } ?>
<?php
$discount_html = ob_get_contents();
ob_end_clean();

$departure_time = $tpl['departure_time'];
$arrival_time = $tpl['arrival_time'];
$max_discount = $tpl['max_discount'];
pjAppController::jsonResponse(compact('ticket', 'departure_time', 'arrival_time', 'max_discount','discount_html'));
?>