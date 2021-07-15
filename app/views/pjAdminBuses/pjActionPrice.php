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
	pjUtil::printNotice(__('infoTicketPricesTitle', true, false), __('infoTicketPricesDesc', true, false));
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionPrice" method="post" id="frmUpdatePrice" class="pj-form form">
		<input type="hidden" name="bus_update" value="1" />
		<input type="hidden" id="id" name="id" value="<?php echo $tpl['arr']['id']?>" />
		<?php
		$locale = isset($_GET['locale']) && (int) $_GET['locale'] > 0 ? (int) $_GET['locale'] : NULL;
		if (is_null($locale))
		{
			foreach ($tpl['lp_arr'] as $v)
			{
				if ($v['is_default'] == 1)
				{
					$locale = $v['id'];
					break;
				}
			}
		}
		if (is_null($locale))
		{
			$locale = @$tpl['lp_arr'][0]['id'];
		}
		?>
		<input type="hidden" name="locale" value="<?php echo $locale; ?>" />
		
		<div class="clear_both">
			<p>
				<label class="title"><?php __('lblTicket'); ?>:</label>
				<span class="inline_block" id="boxTicket">
					<?php
					if(count($tpl['ticket_arr']) > 0)
					{ 
						?>
						<select name="ticket_id" id="ticket_id" class="pj-form-field w250 block b5 required">
							<?php
							foreach ($tpl['ticket_arr'] as $v)
							{
								if(isset($tpl['ticket_id']) && $tpl['ticket_id'] == $v['id'])
								{
									?><option value="<?php echo $v['id']; ?>" selected="selected"><?php echo stripslashes($v['title']); ?></option><?php
								}else{
									?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['title']); ?></option><?php
								}
							}
							?>
						</select>
						<a href="#" class="pj-copy-ticket"><?php __('lblCopyTicketPrices');?></a>
						<?php
					}else{
						?>
						<label class="content"><?php __('lblDefineTickets'); ?></label>
						<?php
					} 
					?>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblDiscoutIfReturn'); ?>:</label>
				<span class="pj-form-field-custom pj-form-field-custom-after">
					<input type="text" name="discount" id="discount" value="<?php echo (float) $tpl['arr']['discount'] > 0 ? $tpl['arr']['discount'] : NULL;?>" class="pj-form-field w80" />
					<span class="pj-form-field-after"><abbr class="pj-form-field-icon-text">%</abbr></span>
				</span>
			</p>
			<div class="bs-loader-outer">
				<div class="bs-loader"></div>
				<div id="bs_price_grid" >
					<?php
					if(isset($tpl['location_arr']))
					{
						include_once PJ_VIEWS_PATH . 'pjAdminBuses/pjActionGetPriceGrid.php';
					} 
					?>
				</div>
			</div>
			<?php
			if(count($tpl['ticket_arr']) > 0)
			{ 
				?>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
				</p>
				<?php
			} 
			?>
		</div>
	</form>
	
	<div id="dialogCopy" title="<?php __('lblCopyPrices'); ?>" style="display:none">
		<form id="frmCopyPrice" class="pj-form form">
			<?php
			if(!empty($tpl['bus_arr']))
			{ 
				?>
				<p>
					<label class="title w100"><?php __('lblBus');?>:</label>
					<span class="inline_block">
						<select name="source_bus_id" id="source_bus_id" class="pj-form-field w200 required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach ($tpl['bus_arr'] as $k => $v)
							{
								?><option value="<?php echo $v['id']; ?>"><?php echo $v['route']; ?>, <?php echo pjUtil::formatTime($v['departure_time'], "H:i:s", $tpl['option_arr']['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrival_time'], "H:i:s", $tpl['option_arr']['o_time_format']); ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<div id="ticketTypeBox"></div>
				<?php
			}else{
				__('lblNoCopyPrice');
			} 
			?>
		</form>
	</div>
	
	<script type="text/javascript">
	var locale_array = new Array(); 
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('bs_field_required'); ?>";
	myLabel.buses = <?php echo count($tpl['bus_arr']);?>
	</script>
	<?php
}
?>