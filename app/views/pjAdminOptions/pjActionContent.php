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
			
	if (isset($tpl['arr']))
	{
		if (is_array($tpl['arr']))
		{
			$count = count($tpl['arr']) - 1;
			if ($count > 0)
			{
				?>
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
				
				<div class="clear_both">
					<form id="frmContent" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionUpdate" method="post" class="form pj-form"  enctype="multipart/form-data">
						<input type="hidden" name="options_update" value="1" />
						<input type="hidden" name="next_action" value="pjActionContent" />
						
						<?php
						pjUtil::printNotice(__('infoContentTitle', true), __('infoContentDesc', true)); 
						?>
						
						<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
						<div class="multilang b10"></div>
						<?php endif;?>
						
						<div class="clear_both">
							<p>
								<label class="title"><?php __('lblImage'); ?></label>
								<input type="file" name="image" id="image" class="pj-form-field" />
							</p>
							<?php
							if(!empty($tpl['image_arr']['value']))
							{
								$image_url = PJ_INSTALL_URL . $tpl['image_arr']['value'];
								?>
								<p id="image_container">
									<label class="title">&nbsp;</label>
									<span class="inline_block">
										<img class="bs-image" src="<?php echo $image_url; ?>" />
										<a href="javascript:void(0);" class="pj-delete-image" data-href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionDeleteImage"><?php __('lblDelete');?></a>
									</span>
								</p>
								<?php
							} 
							foreach ($tpl['lp_arr'] as $v)
							{
								?>
								<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
									<label class="title"><?php __('lblContent'); ?></label>
									<span class="inline_block">
										<textarea name="i18n[<?php echo $v['id']; ?>][<?php echo $tpl['arr']['key'] ?>]" class="pj-form-field" style="width: 500px; height: 250px;"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']][$tpl['arr']['key']])); ?></textarea>
										<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
										<span class="pj-multilang-input"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
										<?php endif;?>
									</span>
								</p>
								<?php
							}
							?>
							<p>
								<label class="title">&nbsp;</label>
								<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
							</p>
						</div>
					</form>
				</div>
				<div id="dialogDeleteImage" style="display: none" title="<?php __('lblDeleteImage');?>"><?php __('lblDeleteConfirmation');?></div>				
				<?php
			}
		}
	}
}
?>
<script type="text/javascript">
(function ($) {
$(function() {
	$(".multilang").multilang({
		langs: <?php echo $tpl['locale_str']; ?>,
		flagPath: "<?php echo PJ_FRAMEWORK_LIBS_PATH; ?>pj/img/flags/",
		select: function (event, ui) {
			
		}
	});
	$(".multilang").find("a[data-index='<?php echo $locale; ?>']").trigger("click");
});
})(jQuery_1_8_2);
</script>