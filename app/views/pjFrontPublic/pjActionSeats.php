<?php
$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
$STORE = @$_SESSION[$controller->defaultStore];

if(isset($STORE['booked_data']))
{
	$booked_data = $STORE['booked_data'];
}
?>
<div class="panel panel-default pjBsMain">
	<?php
	include PJ_VIEWS_PATH . 'pjFrontEnd/elements/header.php';
	?>
	<div class="panel-body pjBsBody">
		<div class="pjBsForm pjBsFormTickets">
			<?php
			if($tpl['status'] == 'OK')
			{
				$current_date = date('Y-m-d');
				$selected_date = pjUtil::formatDate($STORE['date'], $tpl['option_arr']['o_date_format']);
				$previous_date = pjUtil::formatDate(date('Y-m-d', strtotime($selected_date . ' -1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
				$next_date = pjUtil::formatDate(date('Y-m-d', strtotime($selected_date . ' +1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
				
				$is_return = 'F';
				$return_date = '';
				if (isset($STORE['is_return']) && $STORE['is_return'] == 'T')
				{
					$is_return = 'T';
					$return_date = $STORE['return_date'];
					$return_selected_date = pjUtil::formatDate($STORE['return_date'], $tpl['option_arr']['o_date_format']);
					$return_previous_date = pjUtil::formatDate(date('Y-m-d', strtotime($return_selected_date . ' -1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
					$return_next_date = pjUtil::formatDate(date('Y-m-d', strtotime($return_selected_date . ' +1 day')), 'Y-m-d', $tpl['option_arr']['o_date_format']);
				}
				?>
				<form id="bsSelectSeatsForm_<?php echo $_GET['index'];?>" action="" method="post">
					<header class="pjBsFormHead clearfix">
						<p class="pjBsFormTitle pull-left"><?php __('front_journey_from');?> <strong><?php echo $tpl['from_location']?></strong> <?php __('front_to');?> <strong><?php echo $tpl['to_location']?></strong></p><!-- /.pjBsFormTitle pull-left -->

						<dl class="dl-horizontal pjBsDepartureDate pull-right">
							<dt><?php __('front_date_departure');?>: </dt>

							<dd>
								<?php
								if(strtotime($current_date) < strtotime($selected_date)) 
								{ 
									?><a href="#" class="bsDateNav" data-pickup="<?php echo $STORE['pickup_id']?>" data-return="<?php echo $STORE['return_id']?>" data-date="<?php echo $previous_date;?>" data-is_return="<?php echo $is_return; ?>" data-return_date="<?php echo $return_date; ?>">&laquo;&nbsp;<?php echo __('front_prev');?></a><?php
								} 
								?>
								<strong><?php echo $STORE['date'];?></strong>
								<?php if($is_return == 'F' || ($is_return == 'T' && strtotime($selected_date) < strtotime($return_selected_date))) { ?>
									<a href="#" class="bsDateNav" data-pickup="<?php echo $STORE['pickup_id']?>" data-return="<?php echo $STORE['return_id']?>" data-date="<?php echo $next_date;?>" data-is_return="<?php echo $is_return; ?>" data-return_date="<?php echo $return_date; ?>"><?php echo __('front_next');?> &raquo;</a>
								<?php } ?>
							</dd>
						</dl><!-- /.dl-horizontal pjBsDepartureDate pull-right -->
					</header><!-- /.pjBsFormHead clearfix -->
					
					<div class="pjBsFormBody">
						<?php
						if(isset($tpl['bus_arr']) && $tpl['bus_arr'])
						{ 
							?>
							<div class="panel panel-default pjBsSeats bsBusContainer">
							
								<header class="panel-heading pjBsSeatsHead">
									<div class="row">
										<div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_bus');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_available_seats');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_departure_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
											<p class="panel-title pjBsSeatsTitle"><?php __('front_arrival_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->
										
										<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs">
											<p class="panel-title pjBsSeatsTitle"><?php echo __('front_duration');?></p><!-- /.panel-title pjBsSeatsTitle -->
										</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs -->
									</div><!-- /.row -->
								</header><!-- /.panel-heading pjBsSeatsHead -->
								
								<ul class="list-group pjBsListBusses">
									<?php
									foreach($tpl['bus_arr'] as $bus)
									{ 
										$seats_avail = $bus['seats_available'];
										$location_arr = $bus['locations'];
										?>
										<li class="list-group-item">
											<div id="bsRow_<?php echo $bus['id'];?>" class="row bsRow<?php echo isset($booked_data) && $booked_data['bus_id'] == $bus['id'] ? ' bsFocusRow' : null;?>">
												<div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
													<p class="clearfix pjBsBusTitle">
														<?php echo $bus['route'];?>
														<a href="#" class="pull-right pjBrDestinationTip" data-id="<?php echo $bus['id'];?>">
															<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
														</a>
													</p><!-- /.clearfix pjBsBusTitle -->
													
													<div id="pjBrTipClone_<?php echo $bus['id'];?>" style="display: none;">
														<ul class="list-unstyled pjBsListTicks">
															<?php
                                                            $i = 1;
															foreach($location_arr as $location)
															{ 
																?>
                                                                <!-- <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php echo $location['content'] . " - " . (!empty($location['departure_time']) ? pjUtil::formatTime($location['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format']) : pjUtil::formatTime($location['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format']));?></li> -->
                                                                <?php if (count($location_arr) == $i) { ?>
                                                                    <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php echo $location['content'] . " - " . date($tpl['option_arr']['o_date_format'], strtotime($bus['arrival_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($bus['arrival_time']));?></li>
                                                                <?php } else { ?>
                                                                    <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php echo $location['content'] . " - " . $location['departure_with_delay'];?></li>
                                                                <?php } ?>
                                                                
                                                                <?php
                                                                $i++;
															} 
															?>
														</ul><!-- /.list-unstyled pjBsListTicks -->
													</div>
												</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusAvailableSeats"><?php echo $seats_avail;?></p><!-- /.pjBsBusAvailableSeats -->
													<input type="hidden" id="bs_avail_seats_<?php echo $bus['id'];?>" name="avail_seats" value="<?php echo join("~|~", $bus['seat_avail_arr']) ;?>"/>
													<input type="hidden" id="bs_number_of_seats_<?php echo $bus['id'];?>" value="<?php echo $seats_avail;?>"/>
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($bus['departure_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($bus['departure_time']));?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($bus['arrival_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($bus['arrival_time']));?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
													<p class="pjBsBusDate"><?php echo $bus['duration'];?></p><!-- /.pjBsBusDate -->
												</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
		
												<div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">
													<div class="row pjBsRowInline">
														<?php
														$ticket_arr = $bus['ticket_arr'];
														for($i = 0; $i < $tpl['ticket_columns']; $i++)
														{
															if(isset($ticket_arr[$i]))
															{
																$ticket = $ticket_arr[$i];
																if($ticket['price'] != '')
																{
																	?>
																	<div class="col-sm-4 col-xs-12 ticket_select">
																		<div class="form-group">
																			<label for=""><?php echo $ticket['ticket'];?></label>
																			
																			<div class="input-group">
																				<select name="ticket_cnt_<?php echo $ticket['ticket_id'];?>" class="form-control bsTicketSelect bsTicketSelect-<?php echo $bus['id'];?>" data-set="<?php echo !empty($bus['seats_map']) ? 'T' : 'F';?>" data-bus="<?php echo $bus['id']; ?>" data-price="<?php echo $ticket['price'];?>">
																					<?php
																					for($j = 0; $j <= $seats_avail; $j++)
																					{
                                                                                        if ($j > $tpl['option_arr']['o_max_seats']) { continue; }
                                                                                    ?>
                                                                                        <option value="<?php echo $j; ?>"<?php echo isset($booked_data) && $booked_data['ticket_cnt_' . $ticket['ticket_id']] == $j ? ' selected="selected"' : null;?>><?php echo $j; ?></option><?php
																					}
																					?>
																				</select>
																													
																				<span class="input-group-addon">x <?php echo pjUtil::formatCurrencySign( $ticket['price'], $tpl['option_arr']['o_currency']);?></span>
																			</div><!-- /.input-group -->
																		</div><!-- /.form-group -->
																	</div>
																	<?php
																}
															}
														} 
														?>
													</div><!-- /.row -->
												</div><!-- /.col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12 -->
											</div><!-- /.row -->
										</li><!-- /.list-group-item -->
										<?php
									} 
									?>
								</ul><!-- /.list-group pjBsListBusses -->
								<?php
								if(isset($booked_data))
								{
									$selected_seats_arr = explode("|", $booked_data['selected_seats']);
									$intersect = array_intersect($tpl['booked_seat_arr'], $selected_seats_arr);
								}
								?>
								<div class="panel-body pjBsSeatsBody pjBsPickupSeatsBody" style="display: <?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
								
									<div class="text-danger bsTicketErrorMsg" style="display: none;"><?php __('front_validation_tickets');?></div>
									
									<div class="pjBsListSeats">
										<div class="pjBsChosenSeats" style="display:<?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
											<p><?php __('front_select');?> <strong id="bsSeats_<?php echo $_GET['index'];?>"><?php echo isset($booked_data) ? ( empty($intersect) ? ( $booked_data['selected_ticket'] > 0 ? ($booked_data['selected_ticket'] != 1 ? ($booked_data['selected_ticket'] . ' ' . pjSanitize::clean(__('front_seats', true, false))) : ($booked_data['selected_ticket'] . ' ' . pjSanitize::clean(__('front_seat', true, false))) ) :null) :null): null;?></strong></p>
	
											<dl class="dl-horizontal" style="display: <?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'inline-block' : 'none';?>;">
												<dt><?php __('front_selected_seats');?></dt>
												<dd id="bsSelectedSeatsLabel_<?php echo $_GET['index'];?>"><?php echo isset($booked_data) ? ( empty($intersect) ? ($booked_data['selected_seats'] != '' ? join(", ", $tpl['selected_seat_arr']) : null) : null ) : null;?></dd>
											</dl><!-- /.dl-horizontal -->
	
											<button type="button" class="btn btn-link bsReSelect" style="display:<?php echo isset($booked_data) ? (empty($intersect) ? ( $booked_data['has_map'] == 'T' ? 'inline-block' : 'none') :'none' ) : 'none';?>;"><?php __('front_reselect');?></button>
										</div><!-- /.pjBsChosenSeats -->
										<div id="bsMapContainer_<?php echo $_GET['index'];?>" class="bsMapContainer" style="display:<?php echo isset($booked_data) && $booked_data['has_map'] == 'T' ? 'block' : 'none';?>;">
											<?php
											if(isset($booked_data) && $booked_data['has_map'] == 'T')
											{
												include PJ_VIEWS_PATH . 'pjFrontEnd/pjActionGetSeats.php';
											} 
											?>
										</div>
									</div><!-- /.pjBsListSeats -->
									
									<div class="text-danger bsSeatErrorMsg"></div>
								</div><!-- /.panel-body pjBsSeatsBody -->
	
	
								<footer class="panel-footer pjBsSeatsFoot pjBsPickupSeatsFoot" style="display: <?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
									<ul class="list-inline pjBsSeatsKey">
										<li>
											<span class="pjBsSeat pjBsSeatAvailable"></span>
											<span><?php __('front_available');?></span>
										</li>
	
										<li>
											<span class="pjBsSeat pjBsSeatSelected"></span>
											<span><?php __('front_selected');?></span>
										</li>
	
										<li>
											<span class="pjBsSeat pjBsSeatBooked"></span>
											<span><?php __('front_booked');?></span>
										</li>
									</ul><!-- /.list-inline pjBsSeatsKey -->
									
								</footer><!-- /.panel-footer pjBsSeatsFoot -->
								
								<input type="hidden" id="bs_selected_tickets_<?php echo $_GET['index'];?>" name="selected_ticket" value="<?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? $booked_data['selected_ticket'] : null;?>" data-map="<?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? (!empty($tpl['bus_type_arr']['seats_map']) ? 'T' : 'F') : 'F';?>"/>
								<input type="hidden" id="bs_selected_seats_<?php echo $_GET['index'];?>" name="selected_seats" value="<?php echo isset($booked_data) && $booked_data['selected_seats'] != '' ? $booked_data['selected_seats'] : null;?>"/>
								<input type="hidden" id="bs_selected_bus_<?php echo $_GET['index'];?>" name="bus_id" value="<?php echo isset($booked_data) && $booked_data['bus_id'] != '' ? $booked_data['bus_id'] : null;?>"/>
								<input type="hidden" id="bs_has_map_<?php echo $_GET['index'];?>" name="has_map" value="<?php echo isset($booked_data) ? $booked_data['has_map'] : null;?>"/>
								
							</div><!-- /.panel panel-default pjBsSeats -->	
							<?php
						}else{
							?>
							<div class="panel panel-default">
								<div class="panel-body"><?php __('front_no_bus_available');?></div>
							</div><?php 
						} 
						?>
					</div><!-- /.pjBsFormBody -->

					<?php
					if($is_return == 'T')
					{
						?>
						<header class="pjBsFormHead clearfix">
							<p class="pjBsFormTitle pull-left"><?php __('front_journey_from');?> <strong><?php echo $tpl['to_location']?></strong> <?php __('front_to');?> <strong><?php echo $tpl['from_location']?></strong></p><!-- /.pjBsFormTitle -->
	
							<dl class="dl-horizontal pjBsDepartureDate pull-right">
								<dt><?php __('front_date_departure');?>: </dt>
	
								<dd>
									<?php
									if(strtotime($selected_date) < strtotime($return_selected_date))
									{ 
										?><a href="#" class="bsDateNav" data-pickup="<?php echo $STORE['pickup_id']?>" data-return="<?php echo $STORE['return_id']?>" data-date="<?php echo $STORE['date'];?>" data-is_return="T" data-return_date="<?php echo $return_previous_date;?>">&laquo;&nbsp;<?php echo __('front_prev');?></a><?php
									} 
									?>
									<strong><?php echo $STORE['return_date'];?></strong>
									<a href="#" class="bsDateNav" data-pickup="<?php echo $STORE['pickup_id']?>" data-return="<?php echo $STORE['return_id']?>" data-date="<?php echo $STORE['date'];?>" data-is_return="T" data-return_date="<?php echo $return_next_date;?>"><?php echo __('front_next');?> &raquo;</a>
								</dd>
							</dl><!-- /.dl-horizontal pjBsDepartureDate -->
						</header><!-- /.pjBsFormHead -->
						
						<div class="pjBsFormBody">
						
							<div class="panel panel-default pjBsSeats bsBusContainer">
								<?php if (isset($tpl['return_bus_arr']) && !empty($tpl['return_bus_arr'])) { ?>
									<header class="panel-heading pjBsSeatsHead">
										<div class="row">
											<div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
												<p class="panel-title pjBsSeatsTitle"><?php __('front_bus');?></p><!-- /.panel-title pjBsSeatsTitle -->
											</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->
											
											<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
												<p class="panel-title pjBsSeatsTitle"><?php __('front_available_seats');?></p><!-- /.panel-title pjBsSeatsTitle -->
											</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
											
											<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
												<p class="panel-title pjBsSeatsTitle"><?php __('front_departure_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
											</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->
											
											<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs">
												<p class="panel-title pjBsSeatsTitle"><?php __('front_arrival_time');?></p><!-- /.panel-title pjBsSeatsTitle -->
											</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-2 hidden-xs -->
											
											<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs">
												<p class="panel-title pjBsSeatsTitle"><?php echo __('front_duration');?></p><!-- /.panel-title pjBsSeatsTitle -->
											</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 hidden-xs -->
										</div><!-- /.row -->
									</header><!-- /.panel-heading pjBsSeatsHead -->
								
									<ul class="list-group pjBsListBusses">
										<?php
										foreach($tpl['return_bus_arr'] as $return_bus)
										{ 
											$seats_avail = $return_bus['seats_available'];
											$location_arr = $return_bus['locations'];
											?>
											<li class="list-group-item">
												<div id="bsReturnRow_<?php echo $bus['id'];?>" class="row bsReturnRow bsReturnRow_<?php echo $bus['id'];?><?php echo isset($booked_data) && $booked_data['bus_id'] == $bus['id'] ? ' bsFocusRow' : null;?>">
													<div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
														<p class="clearfix pjBsBusTitle">
															<?php echo $return_bus['route'];?>
															<a href="#" class="pull-right pjBrDestinationTip" data-id="<?php echo $return_bus['id'];?>">
																<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
															</a>
														</p><!-- /.clearfix pjBsBusTitle -->
														
														<div id="pjBrTipClone_<?php echo $return_bus['id'];?>" style="display: none;">
															<ul class="list-unstyled pjBsListTicks">
																<?php
																foreach($location_arr as $location)
																{ 
																	?><li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?php echo $location['content'] . " - " . (!empty($location['departure_time']) ? pjUtil::formatTime($location['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format']) : pjUtil::formatTime($location['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format']));?></li><?php
																} 
																?>
															</ul><!-- /.list-unstyled pjBsListTicks -->
														</div>
													</div><!-- /.col-lg-4 col-md-4 col-sm-4 col-xs-8 -->
			
													<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
														<p class="pjBsBusAvailableSeats"><?php echo $seats_avail;?></p><!-- /.pjBsBusAvailableSeats -->
														<input type="hidden" id="bs_return_avail_seats_<?php echo $return_bus['id'];?>" name="return_avail_seats" value="<?php echo join("~|~", $bus['seat_avail_arr']) ;?>"/>
														<input type="hidden" id="bs_return_number_of_seats_<?php echo $return_bus['id'];?>" value="<?php echo $seats_avail;?>"/>
													</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
			
													<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
														<p class="pjBsBusDate"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($return_bus['departure_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($return_bus['departure_time']));?></p><!-- /.pjBsBusDate -->
													</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
			
													<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
														<p class="pjBsBusDate"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($return_bus['arrival_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($return_bus['arrival_time']));?></p><!-- /.pjBsBusDate -->
													</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
			
													<div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
														<p class="pjBsBusDate"><?php echo $return_bus['duration'];?></p><!-- /.pjBsBusDate -->
													</div><!-- /.col-lg-2 col-md-2 col-sm-2 col-xs-4 -->
			
													<div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">
														<div class="row pjBsRowInline">
															<?php
															$ticket_arr = $return_bus['ticket_arr'];
															for($i = 0; $i < $tpl['return_ticket_columns']; $i++)
															{
																if(isset($ticket_arr[$i]))
																{
																	$ticket = $ticket_arr[$i];
																	$price = $ticket['price'] - ($ticket['price'] * $ticket['discount'] / 100);
																	?>
																	<div class="col-sm-4 col-xs-12 ticket_select">
																		<div class="form-group">
																			<label for=""><?php echo $ticket['ticket'];?></label>
																			
																			<div class="input-group">
																				<select id="return_ticket_cnt_<?php echo $return_bus['id'] . '_' . $ticket['ticket_id'];?>" name="return_ticket_cnt_<?php echo $ticket['ticket_id'];?>" class="form-control bsReturnTicketSelect bsReturnTicketSelect-<?php echo $return_bus['id'];?>" data-set="<?php echo !empty($return_bus['seats_map']) ? 'T' : 'F';?>" data-pickup="<?php echo $bus['id']; ?>" data-pickup-bus="<?php echo $bus['id']; ?>" data-bus="<?php echo $return_bus['id']; ?>" data-price="<?php echo $ticket['price'];?>">
																					<?php
																					for($j = 0; $j <= $seats_avail; $j++)
																					{
                                                                                        if ($j > $tpl['option_arr']['o_max_seats']) { continue; }
																						?><option value="<?php echo $j; ?>"<?php echo isset($booked_data['return_ticket_cnt_' . $ticket['ticket_id']]) && $booked_data['return_ticket_cnt_' . $ticket['ticket_id']] == $j ? ' selected="selected"' : null;?>><?php echo $j; ?></option><?php
																					}
																					?>
																				</select>
																													
																				<span class="input-group-addon">x <?php echo pjUtil::formatCurrencySign(number_format($price, 2), $tpl['option_arr']['o_currency']);?></span>
																			</div><!-- /.input-group -->
																		</div><!-- /.form-group -->
																	</div>
																	<?php
																}
															} 
															?>
														</div><!-- /.row -->
													</div><!-- /.col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12 -->
												</div><!-- /.row -->
											</li><!-- /.list-group-item -->
											<?php
										} 
										?>
									</ul><!-- /.list-group pjBsListBusses -->
									<?php
									if (isset($STORE['is_return']) && $STORE['is_return'] == 'T')
									{
										if(isset($booked_data))
										{
											$selected_seats_arr = explode("|", $booked_data['return_selected_seats']);
											$intersect = array_intersect($tpl['booked_return_seat_arr'], $selected_seats_arr);
										}
										?>
										<div class="panel-body pjBsSeatsBody pjBsReturnSeatsBody" style="display:<?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
										
											<div class="text-danger bsReturnTicketErrorMsg" style="display: none;"><?php __('front_validation_tickets');?></div>
											
											<div class="pjBsListSeats">
												<div class="pjBsChosenSeats" style="display:<?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
													<p><?php __('front_select');?> <strong id="bsReturnSeats_<?php echo $_GET['index'];?>"><?php echo isset($booked_data) ? ( empty($intersect) ? ( $booked_data['return_selected_ticket'] > 0 ? ($booked_data['return_selected_ticket'] != 1 ? ($booked_data['return_selected_ticket'] . ' ' . pjSanitize::clean(__('front_seats', true, false))) : ($booked_data['return_selected_ticket'] . ' ' . pjSanitize::clean(__('front_seat', true, false))) ) :null) :null): null;?></strong></p>
			
													<dl class="dl-horizontal" style="display: <?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'inline-block' : 'none';?>;">
														<dt><?php __('front_selected_seats');?></dt>
														<dd id="bsReturnSelectedSeatsLabel_<?php echo $_GET['index'];?>"><?php echo isset($booked_data) ? ( empty($intersect) ? ($booked_data['return_selected_seats'] != '' ? join(", ", $tpl['return_selected_seat_arr']) : null) : null ) : null;?></dd>
													</dl><!-- /.dl-horizontal -->
			
													<button type="button" class="btn btn-link bsReturnReSelect" style="display:<?php echo isset($booked_data) ? (empty($intersect) ? ( $booked_data['return_has_map'] == 'T' ? 'inline-block' : 'none') :'none' ) : 'none';?>;"><?php __('front_reselect');?></button>
												</div><!-- /.pjBsChosenSeats -->
												<div id="bsReturnMapContainer_<?php echo $_GET['index'];?>" class="bsReturnMapContainer" style="display:<?php echo isset($booked_data) && $booked_data['return_has_map'] == 'T' ? 'block' : 'none';?>;">
													<?php
													if(isset($booked_data) && $booked_data['return_has_map'] == 'T')
													{
														include PJ_VIEWS_PATH . 'pjFrontEnd/pjActionGetReturnSeats.php';
													} 
													?>
												</div>
											</div><!-- /.pjBsListSeats -->
											
											<div class="text-danger bsReturnSeatErrorMsg"></div>
										</div><!-- /.panel-body pjBsSeatsBody -->
										<?php
									} 
									?>
	
									<footer class="panel-footer pjBsSeatsFoot pjBsReturnSeatsFoot" style="display:<?php echo isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
										<ul class="list-inline pjBsSeatsKey">
											<li>
												<span class="pjBsSeat pjBsSeatAvailable"></span>
												<span><?php __('front_available');?></span>
											</li>
		
											<li>
												<span class="pjBsSeat pjBsSeatSelected"></span>
												<span><?php __('front_selected');?></span>
											</li>
		
											<li>
												<span class="pjBsSeat pjBsSeatBooked"></span>
												<span><?php __('front_booked');?></span>
											</li>
										</ul><!-- /.list-inline pjBsSeatsKey -->
										
									</footer><!-- /.panel-footer pjBsSeatsFoot -->
								
									<input type="hidden" id="bs_return_selected_tickets_<?php echo $_GET['index'];?>" name="return_selected_ticket" value="<?php echo isset($booked_data) && $booked_data['return_selected_ticket'] > 0 ? $booked_data['return_selected_ticket'] : null;?>" data-map="<?php echo isset($booked_data) && $booked_data['return_selected_ticket'] > 0 ? (!empty($tpl['bus_type_arr']['seats_map']) ? 'T' : 'F') : 'F';?>"/>
									<input type="hidden" id="bs_return_selected_seats_<?php echo $_GET['index'];?>" name="return_selected_seats" value="<?php echo isset($booked_data) && $booked_data['return_selected_seats'] != '' ? $booked_data['return_selected_seats'] : null;?>"/>
									<input type="hidden" id="bs_return_selected_bus_<?php echo $_GET['index'];?>" name="return_bus_id" value="<?php echo isset($booked_data) && $booked_data['return_bus_id'] != '' ? $booked_data['return_bus_id'] : null;?>"/>
									<input type="hidden" id="bs_return_has_map_<?php echo $_GET['index'];?>" name="return_has_map" value="<?php echo isset($booked_data) ? $booked_data['has_map'] : null;?>"/>
								<?php } else { ?>
									<div class="panel-body"><?php __('front_no_bus_available');?></div>
								<?php } ?>
							</div><!-- /.panel panel-default pjBsSeats -->	
						</div>
						<?php
						
					} 
					?>
					<footer class="pjBsFormFoot">
						<p class="text-right pjBsTotalPrice">
							<strong id="bsRoundtripPrice_<?php echo $_GET['index'];?>"></strong>
						</p><!-- /.text-right pjBsTotalPrice -->

						<div class="clearfix pjBsFormActions">
							<a href="#" id="bsBtnCancel_<?php echo $_GET['index'];?>" class="btn btn-default pull-left"><?php __('front_button_back'); ?></a>
							<?php
							if(($is_return == 'T' && isset($tpl['return_bus_arr']) && !empty($tpl['return_bus_arr']) && isset($tpl['bus_arr']) && !empty($tpl['bus_arr']))
								|| ($is_return == 'F' && isset($tpl['bus_arr']) && !empty($tpl['bus_arr']))
							)
							{ 
								?>
								<button type="button" id="bsBtnCheckout_<?php echo $_GET['index'];?>" class="btn btn-primary pull-right"><?php __('front_button_checkout'); ?></button>
								<?php
							} 
							?>
						</div><!-- /.clearfix pjBsFormActions -->
					</footer><!-- /.pjBsFormFoot -->
				</form>
				<?php
			}else{
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
		</div><!-- /.pjBsForm pjBsFormTickets -->
	</div><!-- /.panel-body pjBsBody -->
</div>
<div class="modal fade pjBsModal pjBsModalRoute" id="pjBsModalRoute" tabindex="-1" role="dialog" aria-labelledby="pjBsModalRouteLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<header class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>

				<p class="modal-title"><?php __('front_destinations');?></p><!-- /.modal-title -->
			</header><!-- /.modal-header -->

			<div class="modal-body">
				
			</div><!-- /.modal-body -->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /#pjBsModalRoute.modal fade pjBsModal pjBsModalRoute -->