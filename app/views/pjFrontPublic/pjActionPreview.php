<?php
$STORE = @$_SESSION[$controller->defaultStore];
$FORM = @$_SESSION[$controller->defaultForm];
$booked_data = $STORE['booked_data'];

$sub_total = 0;
$tax = 0;
$total = 0;
$deposit = 0;

$return_sub_total = 0;
$return_tax = 0;
$return_total = 0;
$return_deposit = 0;

$front_messages = __('front_messages', true, false);
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
				<form id="bsPreviewForm_<?php echo $_GET['index'];?>" action="" method="post">
					<input type="hidden" name="step_preview" value="1" />
					<?php
					include PJ_VIEWS_PATH . 'pjFrontEnd/elements/booking_details.php';
					?>
					
					<div class="pjBsFormBody">
						<p class="pjBsFormTitle"><?php __('front_personal_details');?></p><!-- /.pjBsFormTitle -->

						<ul class="list-unstyled pjBsListPersonalData">
							<?php
							if (in_array($tpl['option_arr']['o_bf_include_title'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_title'); ?>: </dt>
										<dd>
											<?php
											$name_titles = __('personal_titles', true, false);
											echo $name_titles[$FORM['c_title']];
											?>
										</dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_fname'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_fname'); ?>: </dt>
										<dd><?php echo isset($FORM['c_fname']) ? pjSanitize::clean($FORM['c_fname']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							} 
							if (in_array($tpl['option_arr']['o_bf_include_lname'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_lname'); ?>: </dt>
										<dd><?php echo isset($FORM['c_lname']) ? pjSanitize::clean($FORM['c_lname']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_phone'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_phone'); ?>: </dt>
										<dd><?php echo isset($FORM['c_phone']) ? pjSanitize::clean($FORM['c_phone']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_email'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_email'); ?>: </dt>
										<dd><?php echo isset($FORM['c_email']) ? pjSanitize::clean($FORM['c_email']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_company'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_company'); ?>: </dt>
										<dd><?php echo isset($FORM['c_company']) ? pjSanitize::clean($FORM['c_company']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_notes'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_notes'); ?>: </dt>
										<dd><?php echo isset($FORM['c_notes']) ? nl2br(pjSanitize::clean($FORM['c_notes'])) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_address'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_address'); ?>: </dt>
										<dd><?php echo isset($FORM['c_address']) ? pjSanitize::clean($FORM['c_address']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_city'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_city'); ?>: </dt>
										<dd><?php echo isset($FORM['c_city']) ? pjSanitize::clean($FORM['c_city']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_state'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_state'); ?>: </dt>
										<dd><?php echo isset($FORM['c_state']) ? pjSanitize::clean($FORM['c_state']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_zip'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_zip'); ?>: </dt>
										<dd><?php echo isset($FORM['c_zip']) ? pjSanitize::clean($FORM['c_zip']) : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if (in_array($tpl['option_arr']['o_bf_include_country'], array(2, 3)))
							{
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_country'); ?>: </dt>
										<dd><?php echo !empty($tpl['country_arr']) ? $tpl['country_arr']['country_title'] : null;?></dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<?php
							}
							if($tpl['option_arr']['o_payment_disable'] == 'No')
							{ 
								?>
								<li>
									<dl class="dl-horizontal">
										<dt><?php __('front_label_payment_medthod'); ?>: </dt>
										<dd>
											<?php 
                                            $payment_methods = __('payment_methods', true, false);
                                            if ($FORM['payment_method'] == '2checkout') {
                                                echo "2Checkout";
                                            } else if ($FORM['payment_method'] == 'pesapal') {
                                                echo "Pesapal";
                                            } else {
                                                echo $payment_methods[$FORM['payment_method']];
                                            }
											?>
										</dd>
									</dl><!-- /.dl-horizontal -->
								</li>
								<div id="bsCCData_<?php echo $_GET['index'];?>" style="display: <?php echo isset($FORM['payment_method']) && $FORM['payment_method'] == 'creditcard' ? 'block' : 'none'; ?>">
									<li>
										<dl class="dl-horizontal">
											<dt><?php __('front_label_cc_type'); ?>: </dt>
											<dd>
												<?php 
												$cc_types = __('cc_types', true, false);
												echo $cc_types[$FORM['cc_type']];
												?>
											</dd>
										</dl><!-- /.dl-horizontal -->
									</li>
									<li>
										<dl class="dl-horizontal">
											<dt><?php __('front_label_cc_num'); ?>: </dt>
											<dd><?php echo isset($FORM['cc_num']) ? pjSanitize::clean($FORM['cc_num']) : null;?></dd>
										</dl><!-- /.dl-horizontal -->
									</li>
									<li>
										<dl class="dl-horizontal">
											<dt><?php __('front_label_cc_exp'); ?>: </dt>
											<dd>
												<?php
												$month_arr = __('months', true, false);
												ksort($month_arr);
												echo $month_arr[$FORM['cc_exp_month']] . '-' . $FORM['cc_exp_year'];
												?>
											</dd>
										</dl><!-- /.dl-horizontal -->
									</li>
									<li>
										<dl class="dl-horizontal">
											<dt><?php __('front_label_cc_code'); ?>: </dt>
											<dd><?php echo isset($FORM['cc_code']) ? pjSanitize::clean($FORM['cc_code']) : null;?></dd>
										</dl><!-- /.dl-horizontal -->
									</li>
								</div>
								<?php
							}
							?>
						</ul><!-- /.list-unstyled pjBsListPersonalData -->
					</div><!-- /.pjBsFormBody -->
				</form>
				
				<footer class="pjBsFormFoot">
					<div class="clearfix pjBsFormMessages" style="display: none;">
						<div id="bsBookingMsg_<?php echo $_GET['index']?>" class="pjBrBookingMsg"></div>
					</div><!-- /.clearfix pjBsFormActions -->
					
					<div class="clearfix pjBsFormActions">
						<a href="#" id="bsBtnBack4_<?php echo $_GET['index'];?>" class="btn btn-default pull-left"><?php __('front_button_back'); ?></a>
						<button type="button" id="bsBtnConfirm_<?php echo $_GET['index'];?>" class="btn btn-primary pull-right"><?php __('front_button_confirm'); ?></button>
					</div><!-- /.clearfix pjBsFormActions -->
				</footer><!-- /.pjBsFormFoot -->
				
				<input type="hidden" id="bsDate_<?php echo $_GET['index'];?>" value="<?php echo $STORE['date'];?>" />
				<input type="hidden" id="bsPickupId_<?php echo $_GET['index'];?>" value="<?php echo $STORE['pickup_id'];?>" />
				<input type="hidden" id="bsReturnId_<?php echo $_GET['index'];?>" value="<?php echo $STORE['return_id'];?>" />
				<?php
				$failed_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[6]);
				$failed_msg = str_replace("[ETAG]", "</a>", $failed_msg);  
				?>
				<input type="hidden" id="bsFailMessage_<?php echo $_GET['index'];?>" value="<?php echo $failed_msg;?>" />
				
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