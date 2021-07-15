<?php
$sub_total = $tpl['price_arr']['sub_total'];
$tax = $tpl['price_arr']['tax'];
$total = $tpl['price_arr']['total'];
$deposit = $tpl['price_arr']['deposit'];

$return_sub_total = isset($tpl['return_price_arr']['sub_total']) ? $tpl['return_price_arr']['sub_total'] : 0;
$return_tax = isset($tpl['return_price_arr']['tax']) ? $tpl['return_price_arr']['tax'] : 0;
$return_total = isset($tpl['return_price_arr']['total']) ? $tpl['return_price_arr']['total'] : 0;
$return_deposit = isset($tpl['return_price_arr']['deposit']) ? $tpl['return_price_arr']['deposit'] : 0;
?>
<header class="pjBsFormHead">
	<p class="pjBsFormTitle"><?php __('front_booking_details');?></p><!-- /.pjBsFormTitle -->

	<div class="row pjBsFormBoxes">
		<div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
			<div class="pjBsFormBoxInner">
				<p class="pjBsFormBoxTitle"><?php __('front_journey');?></p><!-- /.pjBsFormBoxTitle -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_date');?>: </dt>
					<dd><?php echo $STORE['date'];?> <a href="#" class="btn btn-link bsChangeDate"><?php __('front_link_change_date');?></a></dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_departure_from');?>: </dt>
					<dd><?php echo $tpl['from_location']?> <?php __('front_at');?> <?php echo (isset($_SESSION['departure_start_time'])) ? $_SESSION['departure_start_time'] : $tpl['bus_arr']['departure_time'];?></dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_arrive_to');?>: </dt>
					<dd><?php echo $tpl['to_location']?> <?php __('front_at');?> <?php echo (isset($_SESSION['destination_arrival_time'])) ? $_SESSION['destination_arrival_time'] : $tpl['bus_arr']['arrival_time'];?></dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_bus');?>: </dt>
					<dd><?php echo $tpl['bus_arr']['route_title'];?></dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
				<?php
				if (isset($tpl['is_return']) && $tpl['is_return'] == 'T')
				{
					?>
					<hr/>
					<dl class="dl-horizontal pjBsFormBoxData">
						<dt><?php __('front_return_date');?>: </dt>
						<dd><?php echo $STORE['return_date'];?> <a href="#" class="btn btn-link bsChangeDate"><?php __('front_link_change_date');?></a></dd>
					</dl><!-- /.dl-horizontal pjBsFormBoxData -->
													
					<dl class="dl-horizontal pjBsFormBoxData">
						<dt><?php __('front_departure_from');?>: </dt>
						<dd><?php echo $tpl['return_from_location']?> <?php __('front_at');?> <?php echo $tpl['return_bus_arr']['departure_time'];?></dd>
					</dl><!-- /.dl-horizontal pjBsFormBoxData -->
													
					<dl class="dl-horizontal pjBsFormBoxData">
						<dt><?php __('front_arrive_to');?>: </dt>
						<dd><?php echo $tpl['return_to_location']?> <?php __('front_at');?> <?php echo $tpl['return_bus_arr']['arrival_time'];?></dd>
					</dl><!-- /.dl-horizontal pjBsFormBoxData -->
													
					<dl class="dl-horizontal pjBsFormBoxData">
						<dt><?php __('front_bus');?>: </dt>
						<dd><?php echo $tpl['return_bus_arr']['route_title'];?></dd>
					</dl><!-- /.dl-horizontal pjBsFormBoxData -->
					<?php
				} 
				?>
			</div><!-- /.pjBsFormBoxInner -->
		</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox -->

		<div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
			<div class="pjBsFormBoxInner">
				<p class="pjBsFormBoxTitle"><?php __('front_tickets');?></p><!-- /.pjBsFormBoxTitle -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_tickets');?>: </dt>
					
					<dd>
						<?php
						foreach($tpl['ticket_arr'] as $k => $v)
						{
							if(isset($booked_data['ticket_cnt_' . $v['ticket_id']]) && $booked_data['ticket_cnt_' . $v['ticket_id']] > 0)
							{
								?><p><?php echo $booked_data['ticket_cnt_' . $v['ticket_id']];?> <?php echo $v['ticket'];?> x <?php echo pjUtil::formatCurrencySign($v['price'], $tpl['option_arr']['o_currency']);?></p><?php
							}
						}
						if (isset($tpl['return_ticket_arr']))
						{
							foreach($tpl['return_ticket_arr'] as $k => $v)
							{
								if(isset($booked_data['return_ticket_cnt_' . $v['ticket_id']]) && $booked_data['return_ticket_cnt_' . $v['ticket_id']] > 0)
								{
									$price = $v['price'] - ($v['price'] * $v['discount'] / 100);
									?><p><?php echo $booked_data['return_ticket_cnt_' . $v['ticket_id']];?> <?php echo $v['ticket'];?> x <?php echo pjUtil::formatCurrencySign(number_format($price, 2), $tpl['option_arr']['o_currency']);?></p><?php
								}
							}
						}
						?>
						<p><a href="#" class="btn btn-link bsChangeSeat"><?php __('front_link_change_seats');?></a></p>
					</dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
				<?php
				if(!empty($tpl['selected_seat_arr']))
				{ 
					?>								
					<dl class="dl-horizontal pjBsFormBoxData">
						<dt><?php echo ucfirst(__('front_seats', true, false));?>: </dt>
						<dd><?php echo join(", ", $tpl['selected_seat_arr']);?></dd>
					</dl><!-- /.dl-horizontal pjBsFormBoxData -->
					<?php
				}
				if(!empty($tpl['return_selected_seat_arr']))
				{
					?>
					<dl class="dl-horizontal pjBsFormBoxData">
						<dt><?php echo ucfirst(__('front_return_seats', true, false));?>: </dt>
						<dd><?php echo join(", ", $tpl['return_selected_seat_arr']);?></dd>
					</dl><!-- /.dl-horizontal pjBsFormBoxData -->
					<?php
				} 
				?>
			</div><!-- /.pjBsFormBoxInner -->
		</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox -->

		<div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
			<div class="pjBsFormBoxInner">
				<p class="pjBsFormBoxTitle"><?php __('front_payment');?></p><!-- /.pjBsFormBoxTitle -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_tickets_total');?></dt>
					<dd><?php echo pjUtil::formatCurrencySign(number_format($sub_total + $return_sub_total, 2), $tpl['option_arr']['o_currency']);?></dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_tax');?></dt>
					<dd><?php echo pjUtil::formatCurrencySign(number_format($tax + $return_tax, 2), $tpl['option_arr']['o_currency']);?></dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_total');?></dt>
					<dd><?php echo pjUtil::formatCurrencySign(number_format($total + $return_total, 2), $tpl['option_arr']['o_currency']);?></dd>
				</dl><!-- /.dl-horizontal pjBsFormBoxData -->
												
				<dl class="dl-horizontal pjBsFormBoxData">
					<dt><?php __('front_deposit');?></dt>
					<dd><?php echo pjUtil::formatCurrencySign(number_format($deposit + $return_deposit, 2), $tpl['option_arr']['o_currency']);?></dd>                    
				</dl>
              
                <?php if ($tpl['conversion_detail'][0] ==1) { ?>
                    <dl class="dl-horizontal pjBsFormBoxData">
                        <dt>Deposit in USD</dt>	
                        <dd><?php echo pjUtil::formatCurrencySign(number_format($tpl['conversion_detail'][2], 2), $tpl['conversion_detail'][1]);?></dd>                    
                    </dl>
                <?php } ?>
                <!-- /.dl-horizontal pjBsFormBoxData -->
			</div><!-- /.pjBsFormBoxInner -->
		</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox -->
	</div><!-- /.row pjBsFormBoxes -->
</header><!-- /.pjBsFormHead -->