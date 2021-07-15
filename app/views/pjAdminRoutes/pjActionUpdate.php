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
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminRoutes&amp;action=pjActionIndex"><?php __('menuRoutes'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminCities&amp;action=pjActionIndex"><?php __('lblCities'); ?></a></li>
		</ul>
	</div>
	<?php
	pjUtil::printNotice(__('infoUpdateRouteTitle', true, false), __('infoUpdateRouteDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminRoutes&amp;action=pjActionUpdate" method="post" id="frmUpdateRoute" class="pj-form form">
		<input type="hidden" name="route_update" value="1" />
		<input type="hidden" id="index_arr" name="index_arr" value="" />
		<input type="hidden" id="remove_arr" name="remove_arr" value="" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']?>" />
		<?php
		if($tpl['cnt_bookings'] > 0)
		{
			?><input type="hidden" id="has_bookings" name="has_bookings" value="1" /><?php
		} 
		?>
		<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
		<div class="multilang b10"></div>
		<?php endif;?>
		<div class="clear_both">
			<?php
			if($tpl['cnt_bookings'] > 0)
			{
				$number_replace = '<a href="'.$_SERVER['PHP_SELF'].'?controller=pjAdminBookings&amp;action=pjActionIndex&route_id='.$tpl['arr']['id'].'">'.$tpl['cnt_bookings'].'</a>';
				$message = __('lblEditRouteImpossible', true);
				$message = str_replace("{NUMBER}", $number_replace, $message);
				?>
				<p>
					<label class=""><?php echo $message;?></label>
				</p>
				<?php
			}
			foreach ($tpl['lp_arr'] as $v)
			{
			?>
				<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
					<label class="title"><?php __('lblTitle'); ?>:</label>
					<span class="inline_block">
						<input type="text" name="i18n[<?php echo $v['id']; ?>][title]" class="pj-form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['title'])); ?>"/>
						<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
						<span class="pj-multilang-input"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
						<?php endif;?>
					</span>
				</p>
				<?php
			}
			?>
			<div id="bs_location_list" class="bs-location-list">
				<?php
				if(isset($tpl['arr']['city']) && count($tpl['arr']['city']) > 0)
				{
					$city_id_arr = $tpl['arr']['city'];
					foreach($city_id_arr as $k => $city_id)
					{
						$index = 'bs_' . rand(1, 999999);
						?>
						<div class="<?php echo $tpl['cnt_bookings'] == 0 ? 'bs-location-row' : null;?>" data-index="<?php echo $index;?>">
							<p>
								<label class="title bs-title-<?php echo $index;?>"><?php __('lblLocation'); ?> <?php echo $k;?>:</label>
								<span class="inline_block">
									<select name="city_id_<?php echo $index;?>" class="pj-form-field w300 required bs-city"<?php echo $tpl['cnt_bookings'] > 0 ? ' disabled="disabled"' : null;?>>
										<option value="">-- <?php __('lblChoose'); ?>--</option>
										<?php
										foreach($tpl['city_arr'] as $v)
										{
											?><option value="<?php echo $v['id'];?>"<?php echo $city_id == $v['id'] ? ' selected="selected"' : null;?>><?php echo pjSanitize::clean($v['name']);?></option><?php
										} 
										?>
									</select>
								</span>
							</p>
							<div class="location-icons">
								<a href="javascript:void(0);" class="location-delete-icon"></a>
								<a href="javascript:void(0);" class="location-move-icon"></a>
							</div>
						</div>
						<?php
					}
				} else{
					$index = 'bs_' . rand(1, 999999);
					?>
					<div class="<?php echo $tpl['cnt_bookings'] == 0 ? 'bs-location-row' : null;?>" data-index="<?php echo $index;?>">
						<p>
							<label class="title bs-title-<?php echo $index;?>"><?php __('lblLocation'); ?> 1:</label>
							<span class="inline_block">
								<select name="city_id_<?php echo $index;?>" class="pj-form-field w300 required bs-city">
									<option value="">-- <?php __('lblChoose'); ?>--</option>
									<?php
									foreach($tpl['city_arr'] as $k => $v)
									{
										?><option value="<?php echo $v['id'];?>"><?php echo pjSanitize::clean($v['name']);?></option><?php
									} 
									?>
								</select>
							</span>
						</p>
						<div class="location-icons">
							<a href="javascript:void(0);" class="location-delete-icon"></a>
							<a href="javascript:void(0);" class="location-move-icon"></a>
						</div>
					</div>
					<?php
				} 
				?>
			</div>
			<?php
			if($tpl['cnt_bookings'] == 0)
			{ 
				?>
				<p>
					<label class="title">&nbsp;</label>
					<input type="button" value="<?php __('btnAdd'); ?>" class="pj-button pj-add-location" />
				</p>
				<?php
			} 
			?>
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
				<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminRoutes&action=pjActionIndex';" />
			</p>
		</div>
	</form>
	
	<div id="bs_location_clone" style="display:none;">
		<div class="bs-location-row" data-index="{INDEX}">
			<p>
				<label class="title bs-title-{INDEX}"><?php __('lblLocation'); ?> {ORDER}:</label>
				<span class="inline_block">
					<select name="city_id_{INDEX}" class="pj-form-field w300 required bs-city">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach($tpl['city_arr'] as $k => $v)
						{
							?><option value="<?php echo $v['id'];?>"><?php echo pjSanitize::clean($v['name']);?></option><?php
						} 
						?>
					</select>
				</span>
			</p>
			<div class="location-icons">
				<a href="javascript:void(0);" class="location-delete-icon"></a>
				<a href="javascript:void(0);" class="location-move-icon"></a>
			</div>
		</div>
	</div>
	<div id="dialogPrompt" title="<?php __('lblSameLocation'); ?>" style="display:none">
		<p><?php __('lblSameLocationText'); ?></p>
	</div>
	<script type="text/javascript">
	var locale_array = new Array(); 
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('bs_field_required'); ?>";
	myLabel.location = "<?php __('lblLocation'); ?>";
	<?php
	foreach ($tpl['lp_arr'] as $v)
	{
		?>locale_array.push(<?php echo $v['id'];?>);<?php
	} 
	?>
	myLabel.locale_array = locale_array;
	myLabel.number_of_cities = <?php echo count($tpl['city_arr']); ?>;
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