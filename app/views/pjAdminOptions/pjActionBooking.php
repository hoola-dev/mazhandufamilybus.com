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
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/optmenu.php';
		
	pjUtil::printNotice(__('infoBookingsTitle', true), __('infoBookingsDesc', true));
	
	if (isset($tpl['arr']))
	{
		if (is_array($tpl['arr']))
		{
			$count = count($tpl['arr']);
			if ($count > 0)
			{
				?>
				<form id="frmUpdateSettings" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionUpdate" method="post" class="form pj-form">
					<input type="hidden" name="options_update" value="1" />
					<input type="hidden" name="next_action" value="pjActionBooking" />
					<table class="pj-table tblError" cellpadding="0" cellspacing="0" style="width: 100%">
						<thead>
							<tr>
								<th><?php __('lblOption'); ?></th>
								<th><?php __('lblValue'); ?></th>
							</tr>
						</thead>
						<tbody>
	
				<?php
				for ($i = 0; $i < $count; $i++)
				{
					if ($tpl['arr'][$i]['tab_id'] != 2 || (int) $tpl['arr'][$i]['is_visible'] === 0 || $tpl['arr'][$i]['key'] == 'o_tax_type') continue;
					
					$rowClass = NULL;
					$rowStyle = NULL;
					if (in_array($tpl['arr'][$i]['key'], array('o_paypal_address')))
					{
						$rowClass = " boxPaypal";
						$rowStyle = "display: none";
						if ($tpl['option_arr']['o_allow_paypal'] == 'Yes')
						{
							$rowStyle = NULL;
						}
					}
					if (in_array($tpl['arr'][$i]['key'], array('o_authorize_merchant_id', 'o_authorize_transkey', 'o_authorize_timezone', 'o_authorize_md5_hash')))
					{
						$rowClass = " boxAuthorize";
						$rowStyle = "display: none";
						if ($tpl['option_arr']['o_allow_authorize'] == 'Yes')
						{
							$rowStyle = NULL;
						}
					}
					if (in_array($tpl['arr'][$i]['key'], array('o_bank_account')))
					{
						$rowClass = " boxBankAccount";
						$rowStyle = "display: none";
						if ($tpl['option_arr']['o_allow_bank'] == 'Yes')
						{
							$rowStyle = NULL;
						}
					}
					
					?>
					<tr class="pj-table-row-odd<?php echo $rowClass; ?>" style="<?php echo $rowStyle; ?>">
						<td width="50%">
                            <?php 
                            if ($tpl['arr'][$i]['key'] == 'o_cash_min_hour') {
                                echo "Cash Payment Time:<br>A period of time, to make the Cash payment before the seat get cancelled.";
                            } else if ($tpl['arr'][$i]['key'] == 'o_allow_2checkout') {
                                echo "Allow payments with 2Checkout";                            
                            }  else if ($tpl['arr'][$i]['key'] == 'o_allow_pesapal ') {
                                echo "Allow payments with Pesapal";
                            } else if ($tpl['arr'][$i]['key'] == 'o_2checkout_sid') {
                                echo "2Checkout Account ID";
                            } else if ($tpl['arr'][$i]['key'] == 'o_allow_flutterwave') {
                                echo "Allow payments with Flutterwave";
                            } else if ($tpl['arr'][$i]['key'] == 'o_flutterwave_public_key') {
                                echo "Flutterwave Public Key";
                            } else if ($tpl['arr'][$i]['key'] == 'o_flutterwave_secret_key') {
                                echo "Flutterwave Secret Key";
                            } else if ($tpl['arr'][$i]['key'] == 'o_flutterwave_encryption_key') {
                                echo "Flutterwave Encryption Key";
                            }  else if ($tpl['arr'][$i]['key'] == 'o_allow_wallet') {
                                echo "Allow payments with MTN Wallet";
                            }  else if ($tpl['arr'][$i]['key'] == 'o_max_seats') {
                                echo "Max Booking Seats";
                            }  else if ($tpl['arr'][$i]['key'] == 'o_allow_cgrate') {
                                echo "Allow payments with cGrate";
                            }  else if ($tpl['arr'][$i]['key'] == 'o_local_bus_discount') {
                                echo "Max Local Bus Discount";
                            }   else if ($tpl['arr'][$i]['key'] == 'o_international_bus_discount') {
                                echo "Max International Bus Discount";
                            }  else if ($tpl['arr'][$i]['key'] == 'o_api_discount') {
                                echo "App Discount";
                            }  else if ($tpl['arr'][$i]['key'] == 'o_allow_zamtel') {
                                echo "Allow payments with Zamtel";
                            }   else if ($tpl['arr'][$i]['key'] == 'o_allow_airtel') {
                                echo "Allow payments with Airtel";
                            }  else {
                                __('opt_' . $tpl['arr'][$i]['key']);
                            } 
                            ?>
						</td>
                        
						<td>
							<?php
							switch ($tpl['arr'][$i]['type'])
							{
								case 'string':
									?>
                                    <?php
                                    if($tpl['arr'][$i]['key'] == 'o_local_bus_discount' || $tpl['arr'][$i]['key'] == 'o_international_bus_discount' || $tpl['arr'][$i]['key'] == 'o_api_discount')
									{ 
										?>
										<span class="pj-form-field-custom pj-form-field-custom-before">
											<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
											<input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field w60 number align_right" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" />
										</span>
										<?php 
									} else { ?>
                                        <input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field w400" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" />
                                    <?php 
                                    }
                                    break;
								case 'text':
									?><textarea name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field" style="width: 400px; height: 80px;"><?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?></textarea><?php
									break;
								case 'int':
									?>
									<?php
									
									if($tpl['arr'][$i]['key'] == 'o_deposit_payment' || $tpl['arr'][$i]['key'] == 'o_tax_payment')
									{ 
										?>
										<span class="pj-form-field-custom pj-form-field-custom-before">
											<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text">%</abbr></span>
											<input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field w60 number align_right" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" />
										</span>
										<?php 
									} else { 
										if($tpl['arr'][$i]['key'] == 'o_min_hour')
										{
											?>
											<span class="inline-block">
												<input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field field-int number w60" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" />
												<?php __('lblMinutes');?>
											</span>
											<?php
										}else if($tpl['arr'][$i]['key'] == 'o_cash_min_hour')
										{
											?>
											<span class="inline-block">
												<input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field field-int number w60" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" />
												<?php echo "Hour"; ?>
											</span>
											<?php
										}else{
											?>
											<input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field field-int number w60" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" />
											<?php
										}
									} 
									break;
								case 'float':
									if($tpl['arr'][$i]['key'] == 'o_booking_price'){ 
										?>
										<span class="pj-form-field-custom pj-form-field-custom-before">
											<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
											<input type="text" name="value-int-<?php echo $tpl['arr'][$i]['key']; ?>" value="<?php echo (int) $tpl['arr'][$i]['value']; ?>" class="pj-form-field number w60" />
										</span>
										<?php
									}else{
										?><input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field field-float w60" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" /><?php
									}
									break;
								case 'enum':
									?><select name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field">
									<?php
									$default = explode("::", $tpl['arr'][$i]['value']);
									$enum = explode("|", $default[0]);
									
									$enumLabels = array();
									if (!empty($tpl['arr'][$i]['label']) && strpos($tpl['arr'][$i]['label'], "|") !== false)
									{
										$enumLabels = explode("|", $tpl['arr'][$i]['label']);
									}
									
									$enum_arr = __('enum_arr', true, true);
									switch ($tpl['arr'][$i]['key']) {
										case 'o_week_start':
											$enum_arr = __('days', true, true);
											break;
										case 'o_booking_status':
										case 'o_payment_status':
											$enum_arr = __('booking_statuses', true, true);
											break;
									}

									foreach ($enum as $k => $el)
									{
										?><option value="<?php echo $default[0].'::'.$el; ?>" <?php echo ($default[1] == $el) ? 'selected="selected"' : ''; ?>><?php echo (!empty($enum_arr[strtolower($el)])) ? $enum_arr[strtolower($el)] : (array_key_exists($k, $enumLabels) ? stripslashes($enumLabels[$k]) : stripslashes($el)); ?></option><?php
									}
									?>
									</select>
									<?php
									break;
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
						</tbody>
					</table>
					
					<p><input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" /></p>
				</form>
				
				<?php
			}
		}
	}
}
?>
<script type="text/javascript">
var myLabel = myLabel || {};
myLabel.positive_number = "<?php __('lblPositiveNumber', false, false);?>";
</script>