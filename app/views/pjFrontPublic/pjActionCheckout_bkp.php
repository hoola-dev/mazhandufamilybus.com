<?php
foreach ($controller->getCss() as $css)
{
    echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : PJ_INSTALL_URL).$css['path'].htmlspecialchars($css['file']).'" />';
}
foreach ($controller->getJs() as $js)
{
    echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : PJ_INSTALL_URL).$js['path'].htmlspecialchars($js['file']).'"></script>';
}
$STORE = @$_SESSION[$controller->defaultStore];
$FORM = @$_SESSION[$controller->defaultForm];
$booked_data = $STORE['booked_data'];
?>
<div class="panel panel-default pjBsMain">
	<?php
	include PJ_VIEWS_PATH . 'pjFrontEnd/elements/header.php';
	?>
	<div class="panel-body pjBsBody">
		<?php
		if($tpl['status'] == 'OK')
		{
			?>
			<div class="pjBsForm pjBsFormCheckout">
				<form id="bsCheckoutForm_<?php echo $_GET['index'];?>" action="" method="post" class="bsCheckoutForm" data-toggle="validator" role="form">
					<input type="hidden" name="step_checkout" value="1" />
					
					<?php
					include PJ_VIEWS_PATH . 'pjFrontEnd/elements/booking_details.php';
					?>
					
					<div class="pjBsFormBody">
						<p class="pjBsFormTitle"><?php __('front_personal_details');?></p><!-- /.pjBsFormTitle -->

						<div class="form-horizontal">
							<?php
							if (in_array($tpl['option_arr']['o_bf_include_title'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_title'); ?> <?php if($tpl['option_arr']['o_bf_include_title'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<select name="c_title" class="form-control pjBsFieldInline<?php echo ($tpl['option_arr']['o_bf_include_title'] == 3) ? ' required' : NULL; ?>"  data-msg-required="<?php __('front_required_field', false, true);?>">
											<option value="">----</option>
											<?php
											$title_arr = pjUtil::getTitles();
											$name_titles = __('personal_titles', true, false);
											foreach ($title_arr as $v)
											{
												?><option value="<?php echo $v; ?>"<?php echo isset($FORM['c_title']) && $FORM['c_title'] == $v ? ' selected="selected"' : NULL; ?>><?php echo $name_titles[$v]; ?></option><?php
											}
											?>
										</select>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_fname'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_fname'); ?> <?php if($tpl['option_arr']['o_bf_include_fname'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_fname" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_fname'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_fname']) ? pjSanitize::clean($FORM['c_fname']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							} 
							if (in_array($tpl['option_arr']['o_bf_include_lname'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_lname'); ?> <?php if($tpl['option_arr']['o_bf_include_lname'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_lname" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_lname'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_lname']) ? pjSanitize::clean($FORM['c_lname']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_phone'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label">Phone Country <?php if($tpl['option_arr']['o_bf_include_phone'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
                                    <div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
                                        <select name="c_phone_country" id="c_phone_country" onchange="javascript:set_phone_country();" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_phone'] == 3) ? ' required' : NULL; ?>" data-msg-required="<?php __('front_required_field', false, true);?>">
											<option value="">Select Country</option>
											<?php
											foreach ($tpl['country_arr'] as $v)
											{
												?><option value="<?php echo $v['id'].'_'.$v['phone_code']; ?>"<?php echo ((isset($FORM['c_country']) && $FORM['c_country'] == $v['id']) || $v['id'] == 248) ? ' selected="selected"' : NULL; ?>><?php echo $v['country_title']; ?></option><?php
											}
											?>
										</select>
                                        <div class="help-block with-errors"><ul class="list-unstyled" id="err_c_phone_country"></ul></div><!-- /.help-block with-errors -->
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label">Phone number with country code <?php if($tpl['option_arr']['o_bf_include_phone'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
                                    <div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">                                     
										<input type="text" name="c_phone" id="c_phone" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_phone'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_phone']) ? pjSanitize::clean($FORM['c_phone']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>" placeholder="Phone Number"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
                                <script language="javascript">
                                    set_phone_country();
                                </script>
								<?php
                            }
                            ?>
                            </div>
                            <div class="form-horizontal">
                            <?php
							 if (in_array($tpl['option_arr']['o_bf_include_email'], array(2, 3)))
							 {
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_email'); ?> <?php if($tpl['option_arr']['o_bf_include_email'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_email" class="form-control email<?php echo ($tpl['option_arr']['o_bf_include_email'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_email']) ? pjSanitize::clean($FORM['c_email']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_company'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_company'); ?> <?php if($tpl['option_arr']['o_bf_include_company'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_company" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_company'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_company']) ? pjSanitize::clean($FORM['c_company']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_notes'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_notes'); ?> <?php if($tpl['option_arr']['o_bf_include_notes'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<textarea name="c_notes" style="height: 100px;" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_notes'] == 3) ? ' required' : NULL; ?>" data-msg-required="<?php __('front_required_field', false, true);?>"><?php echo isset($FORM['c_notes']) ? pjSanitize::clean($FORM['c_notes']) : null;?></textarea>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_address'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_address'); ?> <?php if($tpl['option_arr']['o_bf_include_address'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_address" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_address'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_address']) ? pjSanitize::clean($FORM['c_address']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_city'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_city'); ?> <?php if($tpl['option_arr']['o_bf_include_city'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_city" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_city'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_city']) ? pjSanitize::clean($FORM['c_city']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_state'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_state'); ?> <?php if($tpl['option_arr']['o_bf_include_state'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_state" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_state'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_state']) ? pjSanitize::clean($FORM['c_state']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_zip'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_zip'); ?> <?php if($tpl['option_arr']['o_bf_include_zip'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<input type="text" name="c_zip" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_zip'] == 3) ? ' required' : NULL; ?>" value="<?php echo isset($FORM['c_zip']) ? pjSanitize::clean($FORM['c_zip']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_country'], array(2, 3)))
							{
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_country'); ?> <?php if($tpl['option_arr']['o_bf_include_country'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<select name="c_country" id="c_country" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_country'] == 3) ? ' required' : NULL; ?>" data-msg-required="<?php __('front_required_field', false, true);?> chosen">
											<option value="">----</option>
											<?php
											foreach ($tpl['country_arr'] as $v)
											{
												?><option value="<?php echo $v['id']; ?>"<?php echo isset($FORM['c_country']) && $FORM['c_country'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?php echo $v['country_title']; ?></option><?php
											}
											?>
										</select>
	
										<div class="help-block with-errors"><ul class="list-unstyled" id="err_c_country"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							}
							if($tpl['option_arr']['o_payment_disable'] == 'No')
							{ 
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_payment_medthod'); ?> <span class="pjBsAsterisk">*</span>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
                                    <select id="bsPaymentMethod_<?php echo $_GET['index'];?>" name="payment_method" class="form-control required" data-msg-required="<?php __('front_required_field', false, true);?>">
                                        <option value="">----</option>
                                    <?php
                                        foreach (__('payment_methods', true, false) as $k => $v)
                                        {
                                            if($tpl['option_arr']['o_allow_' . $k] == 'Yes')
                                            {
                                                if ($v == '2Checkout') {
                                                    $v = 'Credit Card';
                                                }
                                                ?>
                                                <option value="<?php echo $k; ?>"<?php echo isset($FORM['payment_method']) && $FORM['payment_method'] == $k ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
                                            }
                                        }
                                        ?>
                                    </select>
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<div id="bsCCData_<?php echo $_GET['index'];?>" style="display: <?php echo isset($FORM['payment_method']) && $FORM['payment_method'] == 'creditcard' ? 'block' : 'none'; ?>">
									<div class="form-group">
										<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_cc_type'); ?> <span class="pjBsAsterisk">*</span>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
		
										<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
											<select name="cc_type" class="form-control required" data-msg-required="<?php __('front_required_field', false, true);?>">
												<option value="">----</option>
												<?php
												foreach (__('cc_types', true, false) as $k => $v)
												{
													?><option value="<?php echo $k; ?>"<?php echo isset($FORM['cc_type']) && $FORM['cc_type'] == $k ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
												}
												?>
											</select>
		
											<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
										</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
									</div><!-- /.form-group -->
									<div class="form-group">
										<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_cc_num'); ?> <span class="pjBsAsterisk">*</span>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
		
										<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
											<input type="text" name="cc_num" class="form-control required" value="<?php echo isset($FORM['cc_num']) ? pjSanitize::clean($FORM['cc_num']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
		
											<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
										</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
									</div><!-- /.form-group -->
									<div class="form-group">
										<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_cc_exp'); ?> <span class="pjBsAsterisk">*</span>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
		
										<div class="col-lg-5 col-md-5 col-sm-4 col-xs-12">
											<select id="bsExpMonth_<?php echo $_GET['index'];?>" name="cc_exp_month" class="form-control required" data-msg-required="<?php __('front_required_field', false, true);?>">
												<?php
												$month_arr = __('months', true, false);
												ksort($month_arr);
												foreach ($month_arr as $key => $val)
												{
													?><option value="<?php echo $key;?>"<?php echo (int) @$FORM['cc_exp_month'] == $key ? ' selected="selected"' : NULL; ?>><?php echo $val;?></option><?php
												}
												?>
											</select>
											<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
										</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
										<div class="col-lg-5 col-md-4 col-sm-4 col-xs-12">
											<select id="bsExpYear_<?php echo $_GET['index'];?>" name="cc_exp_year" class="form-control required" data-msg-required="<?php __('front_required_field', false, true);?>">
												<?php
												$y = (int) date('Y');
												for ($i = $y; $i <= $y + 10; $i++)
												{
													?><option value="<?php echo $i; ?>"<?php echo @$FORM['cc_exp_year'] == $i ? ' selected="selected"' : NULL; ?>><?php echo $i; ?></option><?php
												}
												?>
											</select>
											<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
										</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
									</div><!-- /.form-group -->
									<div class="form-group">
										<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_cc_code'); ?> <span class="pjBsAsterisk">*</span>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
		
										<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
											<input type="text" name="cc_code" class="form-control required" value="<?php echo isset($FORM['cc_code']) ? pjSanitize::clean($FORM['cc_code']) : null;?>" data-msg-required="<?php __('front_required_field', false, true);?>"/>
		
											<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
										</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
									</div><!-- /.form-group -->
								</div>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_captcha'], array(2, 3)))
							{ 
								?>
								<div class="form-group">
									<label class="col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label"><?php __('front_label_captcha'); ?> <?php if($tpl['option_arr']['o_bf_include_captcha'] == 3): ?><span class="pjBsAsterisk">*</span><?php endif;?>: </label><!-- /.col-lg-2 col-md-3 col-sm-4 col-xs-12 control-label -->
	
									<div class="col-lg-10 col-md-9 col-sm-8 col-xs-12">
										<div class="pjBsCaptcha">
											<input type="text" id="pjBrCaptchaInput" name="captcha" class="form-control<?php echo ($tpl['option_arr']['o_bf_include_captcha'] == 3) ? ' required' : NULL; ?>" maxlength="6" autocomplete="off" data-msg-required="<?php __('front_required_field', false, true);?>"/>
											<img id="pjBrCaptchaImage" src="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjFrontEnd&action=pjActionCaptcha&rand=<?php echo rand(1, 9999); ?><?php echo isset($_GET['session_id']) ? '&session_id=' . $_GET['session_id'] : NULL;?>" alt="Captcha" style="border: solid 1px #E0E3E8;"/>
										</div><!-- /.pjBsCaptcha -->
	
										<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
									</div><!-- /.col-lg-10 col-md-9 col-sm-8 col-xs-12 -->
								</div><!-- /.form-group -->
								<?php
							} 
							?>
						</div><!-- /.form-horizontal -->
					</div><!-- /.pjBsFormBody -->
					
					<footer class="pjBsFormFoot">
						<div class="form-group">
							<p class="pjBsFormTitle"><?php __('front_label_terms_conditions');?></p><!-- /.pjBsFormTitle -->

							<div class="checkbox">
								<label>
									<?php
									if(!empty($tpl['terms_conditions']))
									{ 
										?>
										<input id="bsAgree_<?php echo $_GET['index']?>" name="agreement" type="checkbox" checked="checked" />&nbsp;<?php __('front_label_agree');?>&nbsp;<a href="#" data-toggle="modal" data-target="#pjBsModalTerms"><?php __('front_label_terms_conditions');?></a>
										<?php
									}else{
										?>
										<input id="bsAgree_<?php echo $_GET['index']?>" name="agreement" type="checkbox" checked="checked" />&nbsp;<?php __('front_label_agree');?>&nbsp;<?php __('front_label_terms_conditions');?>
										<?php
									} 
									?>
								</label>
							</div><!-- /.checkbox -->

							<div class="help-block with-errors"><ul class="list-unstyled"></ul></div><!-- /.help-block with-errors -->
						</div><!-- /.form-group -->
					
						<div class="clearfix pjBsFormMessages" style="display: none;">
							<div id="bsBookingMsg_<?php echo $_GET['index']?>" class="text-success pjBrBookingMsg"></div>
						</div><!-- /.clearfix pjBsFormActions -->
					
						<div class="clearfix pjBsFormActions">
							<a href="#" id="bsBtnBack3_<?php echo $_GET['index'];?>" class="btn btn-default pull-left"><?php __('front_button_back'); ?></a>
							<button type="button" id="bsBtnPreview_<?php echo $_GET['index'];?>" class="btn btn-primary pull-right"><?php __('front_button_preview'); ?></button>
						</div><!-- /.clearfix pjBsFormActions -->
					</footer><!-- /.pjBsFormFoot -->
				</form>
				
				<div class="modal fade pjBsModal pjBsModalTerms" id="pjBsModalTerms" tabindex="-1" role="dialog" aria-labelledby="pjBsModalTermsLabel">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<header class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
	
								<p class="modal-title"><?php __('front_label_terms_conditions');?></p><!-- /.modal-title -->
							</header><!-- /.modal-header -->
	
							<div class="modal-body">
								<?php echo nl2br(pjSanitize::clean($tpl['terms_conditions']));?>
							</div><!-- /.modal-body -->
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /#pjBsModalTerms.modal fade pjBsModal pjBsModalTerms -->
			</div><!-- /.pjBsForm pjBsFormCheckout -->
			<?php 
		} else {
			?>
			<div>
				<?php
				$front_messages = __('front_messages', true, false);
				$system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[5]);
				$system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
				echo $system_msg; 
				?>
			</div>
			<?php
		}
		?>
	</div><!-- /.panel-body pjBsBody -->
</div>
<script language="javascript">
    var chosen = ($.fn.chosen !== undefined);
    if (chosen) {
        $("#c_phone_country").chosen();
        $("#c_country").chosen();
    }
</script>