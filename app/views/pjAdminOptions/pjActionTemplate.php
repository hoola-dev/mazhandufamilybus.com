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
					<form id="frmTicketTemplate" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionUpdate" method="post" class="form pj-form">
						<input type="hidden" name="options_update" value="1" />
						<input type="hidden" name="next_action" value="pjActionTemplate" />
						
						<?php
						pjUtil::printNotice(__('infoTicketTemplateTitle', true), __('infoTicketTemplateDesc', true)); 
						?>
						<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
						<div class="multilang b10"></div>
						<?php endif;?>
						<div class="clear_both">			
							<table class="pj-table" cellpadding="0" cellspacing="0" style="width: 100%">
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
										if ($tpl['arr'][$i]['tab_id'] == 5 && (int) $tpl['arr'][$i]['is_visible'] === 1)
										{
											$rowClass = NULL;
											$rowStyle = NULL;
											?>
											<tr class="pj-table-row-odd<?php echo $rowClass; ?>" style="<?php echo $rowStyle; ?>">
												<td width="20%" valign="top">
													<span class="block bold"><?php __('opt_' . $tpl['arr'][$i]['key']); ?></span>
													<!-- <span class="fs10"><?php nl2br(__('opt_' . $tpl['arr'][$i]['key'].'_text')); ?></span> -->
													<?php
													if(in_array($tpl['arr'][$i]['key'], array('o_ticket_template')))
													{ 
														?><span class="fs10"><?php nl2br(__('lblTemplateTokens')); ?></span><?php
													} 
													?>
												</td>
												<td>
													<?php
													switch ($tpl['arr'][$i]['type'])
													{
														case 'string':
															if(in_array($tpl['arr'][$i]['key'], array('o_ticket_template')))
															{
															?>
																<?php
																	foreach ($tpl['lp_arr'] as $v)
																	{
																		?>
																		<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
																			<span class="inline_block">
																				<input type="text" name="i18n[<?php echo $v['id']; ?>][<?php echo $tpl['arr'][$i]['key'] ?>]" class="pj-form-field w400" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']][$tpl['arr'][$i]['key']])); ?>" />
																				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
																				<span class="pj-multilang-input"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
																				<?php endif;?>
																			</span>
																		</p>
																		<?php
																	}
																?>
															<?php
															}
															else { ?>
																<input type="text" name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field w400" value="<?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?>" />
															<?php }
															break;
														case 'text':
															
															if(in_array($tpl['arr'][$i]['key'], array('o_ticket_template')))
															{
															?>
																<?php
																	foreach ($tpl['lp_arr'] as $v)
																	{
																		?>
																		<p class="pj-multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
																			<span class="inline_block">
																				<textarea name="i18n[<?php echo $v['id']; ?>][<?php echo $tpl['arr'][$i]['key'] ?>]" class="pj-form-field mceEditor" style="width: 550px; height: 400px;"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']][$tpl['arr'][$i]['key']])); ?></textarea>
																				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
																				<span class="pj-multilang-input"><img src="<?php echo PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $v['file']; ?>" alt="" /></span>
																				<?php endif;?>
																			</span>
																		</p>
																		<?php
																	}
																?>
															<?php
															}
															else { ?>
																<textarea name="value-<?php echo $tpl['arr'][$i]['type']; ?>-<?php echo $tpl['arr'][$i]['key']; ?>" class="pj-form-field" style="width: 460px; height: 400px;"><?php echo htmlspecialchars(stripslashes($tpl['arr'][$i]['value'])); ?></textarea>
															<?php }
																	
															break;
													}
													?>
												</td>
											</tr>
											<?php
										}
									}
									?>
								</tbody>
							</table>
								
							<p><input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" /></p>
						</div>
					</form>
				</div>				
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