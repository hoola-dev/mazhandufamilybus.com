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
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminRoutes&amp;action=pjActionCreate"><?php __('lblAddRoute'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminRoutes&amp;action=pjActionLocation&amp;id=<?php echo $tpl['arr']['id']?>"><?php __('lblUpdateRoute'); ?></a></li>
		</ul>
	</div>
	<?php
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/submenu.php';
	
	pjUtil::printNotice(__('infoUpdatePriceTitle', true, false), __('infoUpdatePriceDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminRoutes&amp;action=pjActionPrice" method="post" id="frmUpdatePrice" class="pj-form form">
		<input type="hidden" name="route_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']?>" />
		
		<div class="pj-location-grid">
			<?php
			$col_width = 100;
			$number_of_locations = count($tpl['location_arr']); 
			if($number_of_locations > 0)
			{
				?>
				<div class="pj-first-column">
					<table cellpadding="0" cellspacing="0" border="0" class="display">
						<thead>
							<tr class="title-head-row">
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($tpl['location_arr'] as $k => $v)
							{
								if($k <= ($number_of_locations - 2))
								{
									?>
									<tr class="title-row" lang="<?php echo $v['id']; ?>">
										<td><?php echo pjSanitize::clean($v['name'])?></td>
									</tr>
									<?php
								}
							} 
							?>
						</tbody>
					</table>
				</div>
				<div class="pj-location-column">
					<div class="wrapper1">
				    	<div class="div1-compare" style="width: <?php echo $col_width * $number_of_locations; ?>px;"></div>
					</div>
					<div class="wrapper2">
						<div class="div2-compare" style="width: <?php echo $col_width * $number_of_locations; ?>px;">
							<table cellpadding="0" cellspacing="0" border="0" class="display" id="compare_table" width="<?php echo $col_width * $number_of_locations; ?>px">
			    				<thead>
									<tr class="content-head-row">
										<?php
										$j = 1;
										foreach($tpl['location_arr'] as $v)
										{
											if($j > 1)
											{
												?>
												<th class="<?php echo $j == 2 ? 'first-col' : null;?>" width="<?php echo $col_width;?>px">
													<?php echo pjSanitize::clean($v['name'])?>
												</th>
												<?php
											}
											$j++;
										} 
										?>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach($tpl['location_arr'] as $k => $row)
									{
										if($k <= ($number_of_locations - 2))
										{
											?>
											<tr id="content_row_<?php echo $row['id']; ?>" class="">
												<?php
												$j = 1;
												foreach($tpl['location_arr'] as $col)
												{
													if($j > 1)
													{
														$pair_id = $row['id'] . '_' . $col['id'];
														?>
														<td class="<?php echo $j == 2 ? 'first-col' : null;?>" >
															<?php
															if($col['order'] > $row['order'])
															{ 
																?><input type="text" name="price_<?php echo $pair_id;?>" class="pj-form-field pj-grid-field w50" value="<?php echo isset($tpl['weigh_arr'][$pair_id]) ? $tpl['weigh_arr'][$pair_id] : null;?>" /><?php
															}else{
																echo '&nbsp;';
															} 
															?>
														</td>
														<?php
													}
													$j++;
												} 
												?>
											</tr>
											<?php
										}
									} 
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<?php
			} 
			?>
		</div>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
		</p>
		
	</form>
	<?php
}
?>