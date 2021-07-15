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
							<tr class="title-row" lang="<?php echo $v['city_id']; ?>">
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
									<tr class="content_row_<?php echo $row['city_id']; ?>">
										<?php
										$j = 1;
										foreach($tpl['location_arr'] as $col)
										{
											if($j > 1)
											{
												$pair_id = $row['city_id'] . '_' . $col['city_id'];
												?>
												<td class="<?php echo $j == 2 ? 'first-col' : null;?>" >
													<?php
													if($col['order'] > $row['order'])
													{ 
														?>
															<span class="pj-form-field-custom pj-form-field-custom-before">
																<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
																<input type="text" name="return_price_<?php echo $pair_id;?>" class="pj-form-field number pj-grid-field w50" value="<?php echo isset($tpl['return_price_arr'][$pair_id]) ? $tpl['return_price_arr'][$pair_id] : null;?>" />
															</span>
														<?php
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