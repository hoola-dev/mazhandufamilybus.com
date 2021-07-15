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
	?>
	<div class="bold b10 fs14"><?php __('lblRoute'); ?>: <?php echo pjSanitize::html($tpl['route_arr']['title']);?></div>
	<?php
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/submenu.php';
	pjUtil::printNotice(__('infoUpdateTicketTitle', true, false), __('infoUpdateTicketDesc', true, false));
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionTicket" method="post" id="frmUpdateTicket" class="pj-form form">
		<input type="hidden" name="bus_update" value="1" />
		<input type="hidden" id="index_arr" name="index_arr" value="" />
		<input type="hidden" id="remove_arr" name="remove_arr" value="" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']?>" />
		<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
		<div class="multilang b10"></div>
		<?php endif;?>
		<div class="clear_both">
			<p>
				<label class="title"><?php __('lblSeatsAvailable');?>:</label>
				<span class="inline_block">
					<label class="content"><?php echo !empty($tpl['seats_available']) ? $tpl['seats_available'] : 0;?></label>
					<input type="hidden" id="seats_available" name="seats_available" value="<?php echo $tpl['seats_available'];?>" />
				</span>
			</p>
			<p style="display: none;">
				<label class="title"><?php __('lblSetSeatsCount');?>:</label>
				<span class="inline_block">
					<input type="checkbox" id="set_seats_count" name="set_seats_count" value="T" class="t10"<?php echo $tpl['arr']['set_seats_count'] == 'T' ? ' checked="checked"' : null;?>/>
				</span>
			</p>
			<p class="pj-ticket-count pj-ticket-title<?php echo $tpl['arr']['set_seats_count'] == 'F' ? ' pj-hide-count' : null;?>">
				<label class="title">&nbsp;</label>
				<span class="inline_block">
					<label class="content float_left r218"><?php __('lblType');?></label>
					<label class="content float_left"><?php __('lblCount');?></label>
				</span>
			</p>
			<div id="bs_ticket_list" class="bs-ticket-list">
				<?php
				if(count($tpl['ticket_arr']) > 0)
				{
					foreach($tpl['ticket_arr'] as $k => $ticket)
					{
						?>
						<div class="bs-ticket-row" data-index="<?php echo $ticket['id'];?>">
							<?php
							foreach ($tpl['lp_arr'] as $v)
							{
								?>
								<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
									<label class="title bs-title-<?php echo $ticket['id'];?>"><?php __('lblTicket'); ?> <?php echo $k + 1;?>:</label>
									<span class="inline_block">
										<input type="text" name="i18n[<?php echo $v['id']; ?>][title][<?php echo $ticket['id'];?>]" class="pj-form-field float_left r3 w200<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo htmlspecialchars(stripslashes(@$ticket['i18n'][$v['id']]['title'])); ?>"/>
										<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
										<span class="pj-multilang-input float_left r10"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
										<?php endif;?>
									</span>
								</p>
								<?php
							}
							?>
							<input type="text" name="seats_count[<?php echo $ticket['id'];?>]" class="pj-form-field pj-ticket-count w60<?php echo $tpl['arr']['set_seats_count'] == 'F' ? ' pj-hide-count' : null;?>" value="<?php echo $ticket['seats_count']; ?>"/>
							<?php
							if($k > 0)
							{
								?>
								<div class="ticket-icons<?php echo $tpl['arr']['set_seats_count'] == 'F' ? ' pj-right-250' : ' pj-right-168';?>">
									<input type="button" value="<?php __('btnRemove'); ?>" class="pj-button pj-remove-ticket" />
								</div>
								<?php
							} 
							?>
						</div>
						<?php
					}
				} else{
					$index = 'bs_' . rand(1, 999999);
					?>
					<div class="bs-ticket-row" data-index="<?php echo $index;?>">
						<?php
						foreach ($tpl['lp_arr'] as $v)
						{
							?>
							<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
								<label class="title bs-title-<?php echo $index;?>"><?php __('lblTicket'); ?> 1:</label>
								<span class="inline_block">
									<input type="text" name="i18n[<?php echo $v['id']; ?>][title][<?php echo $index;?>]" class="pj-form-field float_left r3 w200<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>"/>
									<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
									<span class="pj-multilang-input float_left r10"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
									<?php endif;?>
								</span>
							</p>
							<?php
						}
						?>
						<input type="text" name="seats_count[<?php echo $index;?>]" class="pj-form-field pj-ticket-count w60<?php echo $tpl['arr']['set_seats_count'] == 'F' ? ' pj-hide-count' : null;?>"/>
					</div>
					<?php
				}
				?>
			</div>			
			<p>
				<label class="title">&nbsp;</label>
				<input type="button" value="<?php __('btnAdd'); ?>" class="pj-button pj-add-ticket" />
				<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
			</p>
		</div>
	</form>
	
	<div id="dialogValidate" title="<?php __('lblValidate'); ?>" style="display:none"><?php __('lblEqualTo'); ?></div>
	
	<div id="bs_ticket_clone" style="display:none;">
		<div class="bs-ticket-row" data-index="{INDEX}">
			<?php
			foreach ($tpl['lp_arr'] as $v)
			{
				?>
				<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
					<label class="title bs-title-{INDEX}"><?php __('lblTicket'); ?> {ORDER}:</label>
					<span class="inline_block">
						<input type="text" name="i18n[<?php echo $v['id']; ?>][title][{INDEX}]" class="pj-form-field float_left r3 w200<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>"/>
						<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
						<span class="pj-multilang-input float_left r10"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
						<?php endif;?>
					</span>
				</p>
				<?php
			}
			?>
			<input type="text" name="seats_count[{INDEX}]" class="pj-form-field pj-ticket-count w60<?php echo $tpl['arr']['set_seats_count'] == 'F' ? ' pj-hide-count' : null;?>"/>
			<div class="ticket-icons{CLASS}">
				<input type="button" value="<?php __('btnRemove'); ?>" class="pj-button pj-remove-ticket" />
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
	var locale_array = new Array(); 
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('bs_field_required'); ?>";
	myLabel.equalTo = "<?php __('lblEqualTo'); ?>";
	myLabel.ticket = "<?php __('lblTicket'); ?>";
	myLabel.localeId = "<?php echo $controller->getLocaleId(); ?>";
	<?php
	foreach ($tpl['lp_arr'] as $v)
	{
		?>locale_array.push(<?php echo $v['id'];?>);<?php
	} 
	?>
	myLabel.locale_array = locale_array;
	(function ($) {
		$(function() {
			$(".multilang").multilang({
				langs: <?php echo $tpl['locale_str']; ?>,
				flagPath: "<?php echo PJ_FRAMEWORK_LIBS_PATH; ?>pj/img/flags/",
				select: function (event, ui) {
					
				}
			});
		});
	})(jQuery_1_8_2);
	</script>
	<?php
}
?>