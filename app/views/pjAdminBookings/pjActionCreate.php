<?php
if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			pjUtil::printNotice(NULL, $status[2]);
			break;
	}
} else {
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);

		if ($_GET['err'] == 'CR01') {
			pjUtil::printNotice('No credit balance', 'Booking could not be added since you do not have enough credit balance.');
		} else {
			pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
		}
	}
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
	
	pjUtil::printNotice(__('infoAddBookingTitle', true, false), __('infoAddBookingDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionCreate" method="post" class="form pj-form" id="frmCreateBooking">
		<input type="hidden" name="booking_create" value="1" />
		<input type="hidden" id="booking_route" name="booking_route"/>
		<input type="hidden" id="booking_return_route" name="booking_return_route"/>
		<input type="hidden" id="reload_map" name="reload_map" value="1" />
		<input type="hidden" id="return_reload_map" name="return_reload_map" value="1" />
		<input type="hidden" name="tab_id" value="<?php echo isset($_GET['tab_id']) && !empty($_GET['tab_id']) ? $_GET['tab_id'] : 'tabs-1'; ?>" />
		
		
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('lblBookingDetails');?></a></li>
				<li><a href="#tabs-2"><?php __('lblClientDetails');?></a></li>
			</ul>
		
		
			<div id="tabs-1">
				<div class="bs-loader-outer">
					<div class="bs-loader"></div>
					<p>
						<label class="title"><?php __('lblDate'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="booking_date" id="booking_date" class="pj-form-field pointer w100 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo isset($_GET['bus_id']) ? $_GET['date'] : pjUtil::formatDate(date('Y-m-d'), 'Y-m-d', $tpl['option_arr']['o_date_format'])?>"/>
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
					</p>
					<div id="fromToBox">
						<p>
							<label class="title"><?php __('lblFrom'); ?>:</label>
							<span class="inline-block">
								<span id="pickupContainer">
									<select name="pickup_id" id="pickup_id" class="pj-form-field w200 required">
										<option value="">-- <?php __('lblChoose'); ?>--</option>
										<?php
										foreach($tpl['from_location_arr'] as $k => $v)
										{
											?><option value="<?php echo $v['id'];?>"<?php echo isset($_GET['bus_id']) ? ($v['id'] == $_GET['pickup_id'] ? ' selected="selected"' : null) : null; ?>><?php echo pjSanitize::clean($v['name']);?></option><?php
										} 
										?>
									</select>
								</span>
								<span id="bsDepartureTime" class="bs-time float_left l5"><?php echo isset($_GET['bus_id']) ? __('lblDepartureTime', true, false) . ': ' . pjUtil::formatTime($tpl['bus']['departure_time'], "H:i:s", $tpl['option_arr']['o_time_format']) : null;?></span>
							</span>
						</p>
						<p>
							<label class="title"><?php __('lblTo'); ?>:</label>
							<span class="inline-block">
								<span id="returnContainer">
									<select name="return_id" id="return_id" class="pj-form-field w200 required">
										<option value="">-- <?php __('lblChoose'); ?>--</option>
										<?php
										foreach($tpl['to_location_arr'] as $k => $v)
										{
											?><option value="<?php echo $v['id'];?>"<?php echo isset($_GET['bus_id']) ? ($v['id'] == $_GET['return_id'] ? ' selected="selected"' : null) : null; ?>><?php echo pjSanitize::clean($v['name']);?></option><?php
										} 
										?>
									</select>
								</span>
								<span id="bsArrivalTime" class="bs-time float_left l5"><?php echo isset($_GET['bus_id']) ? __('lblArrivalTime', true, false) . ': ' . pjUtil::formatTime($tpl['bus']['arrival_time'], "H:i:s", $tpl['option_arr']['o_time_format']) : null;?></span>
							</span>
						</p>
					</div>
					<div id="busBox">
						<p>
							<label class="title"><?php __('lblBus'); ?>:</label>
							<span class="inline-block">
								<select name="bus_id" id="bus_id" class="pj-form-field w300 required">
									<option value="">-- <?php __('lblChoose'); ?>--</option>
									<?php
									if(isset($_GET['bus_id']))
									{
										foreach ($tpl['bus_arr'] as $k => $v)
										{
											?><option value="<?php echo $v['id']; ?>"<?php echo $v['id'] == $_GET['bus_id'] ? ' selected="selected"' : null; ?> data-set="<?php echo !empty($v['seats_map']) ? 'T' : 'F';?>"><?php echo $v['route']; ?>, <?php echo $v['depart_arrive']; ?></option><?php
										}
									}
									?>
								</select>
							</span>
						</p>
					</div>
					
					<div id="ticketBox">
						<?php
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
										?>
										<span class="block b5 overflow">
											<label class="block float_left r5 t5 w150"><?php echo $v['ticket'];?></label>
											<select name="ticket_cnt_<?php echo $v['ticket_id'];?>" class="pj-form-field w60 r3 float_left bs-ticket" data-price="<?php echo $v['price'];?>">
												<?php
												for($i = 0; $i <= $seats_avail; $i++)
												{
													?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php
												}
												?>
											</select>
											<label class="block float_left r5 t5">x</label>
											<label class="block float_left t5"><?php echo pjUtil::formatCurrencySign( $v['price'], $tpl['option_arr']['o_currency']);?></label>
										</span>
										<?php
									} 
									?>
								</span>
								<input type="hidden" id="bs_number_of_seats" name="bs_number_of_seats" value="<?php echo $seats_avail; ?>"/>
							</p>
							<?php
						} 
						?>
					</div>
					<div id="seatsBox" style="display: none;">
						<p>
							<label class="title"><?php __('lblSeats'); ?>:</label>
							<span class="inline-block">
								<label class="content">
									<span id="bs_selected_seat_label" class="block float_left r10"></span>
									<a class="bs-select-seats" href="#"><?php __('lblSelectSeats');?></a>
								</label>
								<input type="hidden" id="selected_seats" name="selected_seats" value="" class="required"/>
							</span>
						</p>
					</div>
					<div id="selectSeatsBox" style="display: none;"></div>				
					<p>
						<label class="title"><?php __('lblIsReturn'); ?>:</label>
						<span class="inline-block t5">
							<input type="checkbox" name="is_return" id="is_return" value="T" />
						</span>
					</p>
					<div class="returnBox" style="display: none;">
						<p>
							<label class="title"><?php __('lblReturnDate'); ?>:</label>
							<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
								<input type="text" name="return_date" id="return_date" class="pj-form-field pointer w100 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value=""/>
								<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
							</span>
						</p>
					</div>
					<div id="returnBox" class="returnBox"></div>
					<div id="returnTicketBox" class="returnBox" style="display: none;">
						
					</div>
					<div id="seatsReturnBox" style="display: none;">
						<p>
							<label class="title"><?php __('lblReturnSeats'); ?>:</label>
							<span class="inline-block">
								<label class="content">
									<span id="bs_return_selected_seat_label" class="block float_left r10"></span>
									<a class="bs-select-return-seats" href="#"><?php __('lblSelectSeats');?></a>
								</label>
								<input type="hidden" id="return_selected_seats" name="return_selected_seats" value=""/>
							</span>
						</p>
					</div>
					<div id="selectReturnSeatsBox" style="display: none;"></div>				
							
					<p>
						<label class="title"><?php __('lblSubTotal'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="sub_total" name="sub_total" class="pj-form-field number w108" readonly="readonly"/>
							<input type="hidden" id="pickup_sub_total" name="pickup_sub_total"/>
							<input type="hidden" id="return_sub_total" name="return_sub_total"/>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblTax'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="tax" name="tax" class="pj-form-field number w108" readonly="readonly" data-tax="<?php echo $tpl['option_arr']['o_tax_payment'];?>"/>
							<input type="hidden" id="pickup_tax" name="pickup_tax"/>
							<input type="hidden" id="return_tax" name="return_tax"/>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblTotal'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="total" name="total" class="pj-form-field number w108" readonly="readonly"/>
							<input type="hidden" id="pickup_total" name="pickup_total"/>
							<input type="hidden" id="return_total" name="return_total"/>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblDeposit'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="deposit" name="deposit" class="pj-form-field number w108" readonly="readonly" data-deposit="<?php echo $tpl['option_arr']['o_deposit_payment'];?>"/>
							<input type="hidden" id="pickup_deposit" name="pickup_deposit"/>
							<input type="hidden" id="return_deposit" name="return_deposit"/>
						</span>
					</p>
                    <?php if ($controller->isAllowDiscount()) { ?>
                    <p id="discount_p" style="display:none;">						
					</p>
                    <?php } ?>
					<p>
						<label class="title"><?php __('lblPaymentMethod');?>:</label>
						<span class="inline-block">
							<select name="payment_method" id="payment_method" class="pj-form-field w150 required">
								<!-- <option value="">-- <?php __('lblChoose'); ?>--</option> -->
								<?php
                                foreach (__('payment_methods', true, false) as $k => $v)                                
								{
                                ?>
                                    <?php if ($k !== 'cash') { continue; } ?>
									<option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
					<p class="boxCC" style="display: none;">
						<label class="title"><?php __('lblCCType'); ?>:</label>
						<span class="inline-block">
							<select name="cc_type" class="pj-form-field w150">
								<option value="">---</option>
								<?php
								foreach (__('cc_types', true, false) as $k => $v)
								{
									?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
					<p class="boxCC" style="display: none;">
						<label class="title"><?php __('lblCCNum'); ?>:</label>
						<span class="inline-block">
							<input type="text" name="cc_num" id="cc_num" class="pj-form-field w136" />
						</span>
					</p>
					<p class="boxCC" style="display: none;">
						<label class="title"><?php __('lblCCExp'); ?>:</label>
						<span class="inline-block">
							<select name="cc_exp_month" class="pj-form-field">
								<option value="">---</option>
								<?php
								$month_arr = __('months', true, false);
								ksort($month_arr);
								foreach ($month_arr as $key => $val)
								{
									?><option value="<?php echo $key;?>"><?php echo $val;?></option><?php
								}
								?>
							</select>
							<select name="cc_exp_year" class="pj-form-field">
								<option value="">---</option>
								<?php
								$y = (int) date('Y');
								for ($i = $y; $i <= $y + 10; $i++)
								{
									?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
					<p class="boxCC" style="display: none">
						<label class="title"><?php __('lblCCCode'); ?>:</label>
						<span class="inline-block">
							<input type="text" name="cc_code" id="cc_code" class="pj-form-field w100" />
						</span>
					</p>
					<div class="p">
						<label class="title"><?php __('lblStatus'); ?>:</label>
						<span class="inline-block">
							<select name="status" id="status" class="pj-form-field w150 required">
								<option value="">-- <?php __('lblChoose'); ?>--</option>
								<?php
								foreach (__('booking_statuses', true, false) as $k => $v)
								{
									?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
								}
								?>
							</select>
						</span>
					</div>
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
						<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBookings&action=pjActionIndex';" />
					</p>
					
				</div><!-- .bs-loader-outer -->
			</div><!-- #tabs-1 -->
			
			<div id="tabs-2">
				<p>
					<label class="title"><?php __('lblBookingTitle'); ?>:</label>
					<span class="inline-block">
						<select name="c_title" id="c_title" class="pj-form-field w150<?php echo $tpl['option_arr']['o_bf_include_title'] == 3 ? ' required' : NULL; ?>">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							$title_arr = pjUtil::getTitles();
							$name_titles = __('personal_titles', true, false);
							foreach ($title_arr as $v)
							{
								?><option value="<?php echo $v; ?>"><?php echo $name_titles[$v]; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingFname'); ?>:</label>
					<span class="inline-block">
						<input type="text" name="c_fname" id="c_fname" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_fname'] == 3 ? ' required' : NULL; ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingLname'); ?>:</label>
					<span class="inline-block">
						<input type="text" name="c_lname" id="c_lname" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_lname'] == 3 ? ' required' : NULL; ?>" />
					</span>
				</p>
				<p>
					<label class="title">Phone with country code:</label>
					<span class="inline-block">
						<input type="text" name="c_phone" id="c_phone" value="+260" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_phone'] == 3 ? ' required' : NULL; ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingEmail'); ?>:</label>
					<span class="inline-block">
						<input type="text" name="c_email" id="c_email" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_email'] == 3 ? ' required' : NULL; ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingNotes'); ?>:</label>
					<span class="inline-block">
						<textarea name="c_notes" id="c_notes" class="pj-form-field w500 h120<?php echo $tpl['option_arr']['o_bf_include_notes'] == 3 ? ' required' : NULL; ?>"></textarea>
					</span>
				</p>
				<div>
					<p>
						<label class="title"><?php __('lblBookingCompany'); ?>:</label>
						<span class="inline-block">
							<input type="text" name="c_company" id="c_company" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_company'] == 3 ? ' required' : NULL; ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblBookingAddress'); ?>:</label>
						<span class="inline-block">
							<input type="text" name="c_address" id="c_address" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_address'] == 3 ? ' required' : NULL; ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblBookingCity'); ?>:</label>
						<span class="inline-block">
							<input type="text" name="c_city" id="c_city" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_city'] == 3 ? ' required' : NULL; ?>"/>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblBookingState'); ?>:</label>
						<span class="inline-block">
							<input type="text" name="c_state" id="c_state" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_state'] == 3 ? ' required' : NULL; ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblBookingZip'); ?>:</label>
						<span class="inline-block">
							<input type="text" name="c_zip" id="c_zip" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_zip'] == 3 ? ' required' : NULL; ?>" />
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblBookingCountry'); ?>:</label>
						<span class="inline-block">
							<select name="c_country" id="c_country" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_country'] == 3 ? ' required' : NULL; ?>">
								<option value="">-- <?php __('lblChoose'); ?>--</option>
								<?php
								foreach ($tpl['country_arr'] as $v)
								{
									?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['country_title']); ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
				</div>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBookings&action=pjActionIndex';" />
				</p>
			</div>
		</div>
	</form>
	
	<div id="dialogSelect" title="<?php __('lblSelectSeats'); ?>" style="display:none"><img src="<?php echo PJ_IMG_PATH . 'backend/pj-preloader.gif'?>" /></div>
	<div id="dialogReturnSelect" title="<?php __('lblSelectSeats'); ?>" style="display:none"><img src="<?php echo PJ_IMG_PATH . 'backend/pj-preloader.gif'?>" /></div>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.from = "<?php echo strtolower(__('lblFrom', true, false));?>";
	myLabel.to = "<?php echo strtolower(__('lblTo', true, false));?>";
	myLabel.assigned_seats = "<?php echo __('lblAssignedSeats');?>";
	myLabel.loader = '<img src="<?php echo PJ_IMG_PATH;?>backend/pj-preloader.gif" />';
	</script>
	<?php
	if (isset($_GET['tab_id']) && !empty($_GET['tab_id']))
	{		
		$tab_id = $_GET['tab_id'];
		$tab_id = $tab_id < 0 ? 0 : $tab_id;
		?>
		<script type="text/javascript">
		(function ($) {
			$(function () {
				$("#tabs").tabs("option", "selected", <?php echo $tab_id; ?>);
			});
		})(jQuery);
		</script>
		<?php
	}
}
?>