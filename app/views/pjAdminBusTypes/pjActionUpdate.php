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
		$titles = __('error_titles', true, false);
		$bodies = __('error_bodies', true, false);
		
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	
	pjUtil::printNotice(__('infoUpdateBusTypeTitle', true, false), __('infoUpdateBusTypeDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBusTypes&amp;action=pjActionUpdate" method="post" id="frmUpdateBusType" class="pj-form form" enctype="multipart/form-data">
		<input type="hidden" name="bus_type_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']?>" />
		<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
		<div class="multilang b10"></div>
		<?php endif;?>
		<div class="clear_both">
			<?php
			foreach ($tpl['lp_arr'] as $v)
			{
			?>
				<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
					<label class="title"><?php __('lblName'); ?>:</label>
					<span class="inline_block">
						<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="pj-form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['name'])); ?>"/>
						<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
						<span class="pj-multilang-input"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
						<?php endif;?>
					</span>
				</p>
				<?php
			}
			?>
			
			<?php
			$map = $tpl['arr']['seats_map']; 
			if (is_file($map))
			{
				$size = getimagesize($map);
				?>
				<div id="boxMap">
					<p>
						<label class="title"><?php __('lblSeatsMap'); ?></label>
						<span class="inline_block">
							<input type="button" value="<?php __('btnDeleteMap'); ?>" class="pj-button pj-delete-map" lang="<?php echo $tpl['arr']['id']?>"/>
						</span>
					</p>
					<div class="bsMapHolder">
						<div id="mapHolder" style="position: relative; overflow: hidden; width: <?php echo $size[0]; ?>px; height: <?php echo $size[1]; ?>px; margin: 0 auto;">
							<img id="map" src="<?php echo $map; ?>" alt="" style="margin: 0; border: none; position: absolute; top: 0; left: 0; z-index: 500" />
							<?php
							foreach ($tpl['seat_arr'] as $seat)
							{
								?><span rel="hi_<?php echo $seat['name']; ?>" title="<?php echo $seat['name']; ?>" class="rect empty" style="width: <?php echo $seat['width']; ?>px; height: <?php echo $seat['height']; ?>px; left: <?php echo $seat['left']; ?>px; top: <?php echo $seat['top']; ?>px; line-height: <?php echo $seat['height']; ?>px"><span class="bsInnerRect" data-name="hi_<?php echo $seat['id']; ?>"><?php echo stripslashes($seat['name']); ?></span></span><?php
							}
							?>
						</div>
						<input type="hidden" id="number_of_seats" name="number_of_seats" value="" class="required"/>
					</div>
					<div id="hiddenHolder">
						<?php
						foreach ($tpl['seat_arr'] as $seat)
						{
							?><input id="hi_<?php echo $seat['name']; ?>" type="hidden" name="seats[]" value="<?php echo join("|", array($seat['id'], $seat['width'], $seat['height'], $seat['left'], $seat['top'], $seat['name'])); ?>" /><?php
						}
						?>
					</div>
					<div id="dialogDelete" title="<?php __('btnDeleteMap'); ?>" style="display:none">
						<p><?php __('lblDeleteMapConfirm'); ?></p>
					</div>
				</div>
				<?php
			}else{
				?>
				<p>
					<label class="title"><?php __('lblSeatsMap'); ?></label>
					<span class="inline_block">
						<input type="file" name="seats_map" id="seats_map" class="pj-form-field" />
					</span>
				</p>
				<?php
			} 
			?>
			<p style="display:<?php echo (is_file($map)) ? 'none' : 'block';?>">
				<label class="title"><?php __('lblSeatsCount'); ?></label>
				<span class="inline_block">
					<input type="text" name="seats_count" id="seats_count" class="pj-form-field w80" value="<?php echo $tpl['arr']['seats_count'] != '' ? $tpl['arr']['seats_count'] : null; ?>"/>
				</span>
			</p>
			<p class="bsDefineSeats" style="display:none">
				<label class="title">&nbsp;</label>
				<span class="inline_block">
					<label class="content"><?php __('lblDefineSeats');?></label>
				</span>
			</p>
            <p>
                <label class="title">Seat Layout</label>
                <span class="inline_block">
                    <select name="seat_layout" id="seat_layout" class="pj-form-field required">
                        <option value="">-- <?php __('lblChoose'); ?>--</option>
                        <option value="2-2" <?php if ($tpl['arr']['seat_layout'] == '2-2') { echo "selected"; } ?>>2-2</option>
                        <option value="2-3" <?php if ($tpl['arr']['seat_layout'] == '2-3') { echo "selected"; } ?>>2-3</option>
                    </select>
                </span>
            </p>
			<div style="clear:both;"></div>
			<p>
				<label class="title">&nbsp;</label>
				<span class="inline_block">
					<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button float_left r5" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button float_left r5" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBusTypes&action=pjActionIndex';" />
					<input type="button" id="pj_delete_seat" value="" class="pj-button float_left" style="display: none;"/>
				</span>
			</p>
		</div>
	</form>
	
	<script type="text/javascript">
	var locale_array = new Array(); 
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('bs_field_required'); ?>";
	myLabel.seats_required = "<?php __('bs_seats_required'); ?>";
	myLabel.delete = "<?php __('lblDelete'); ?>";
	<?php
	foreach ($tpl['lp_arr'] as $v)
	{
		?>locale_array.push(<?php echo $v['id'];?>);<?php
	} 
	?>
	myLabel.locale_array = locale_array;
	myLabel.localeId = "<?php echo $controller->getLocaleId(); ?>";
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