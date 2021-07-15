<?php
$bus = '<p>
				<label class="title">'.__('lblBus', true, false).':</label>
				<span class="inline-block">
					<select name="bus_id" id="bus_id" class="pj-form-field w300 required">
						<option value="">-- '.__('lblChoose', true, false).'--</option>';
$bus .=				'</select>';
$bus .=			'</span>';
$bus .='</p>';

$location = '<p>';
$location .= '<label class="title">' .  __('lblFrom', true, false) .':</label>';
$location .= '	<span class="inline-block">';
$location .= '	 	<span id="pickupContainer">';
$location .= '		<select name="pickup_id" id="pickup_id" class="pj-form-field w200 required">';
$location .= '			<option value="">-- '.__('lblChoose', true, false).'--</option>';
						foreach($tpl['from_location_arr'] as $k => $v)
						{
$location .= '				<option value="'.$v['id'].'">'.stripslashes($v['name']).'</option>';							
						}
$location .= '		</select>';
$location .= ' 		</span>';
$location .= '      <span id="bsDepartureTime" class="bs-time float_left l5"></span>';
$location .= ' </span>';
$location .= '</p>';

$location .= '<p>';
$location .= '<label class="title">' .  __('lblTo', true, false) .':</label>';
$location .= '	<span class="inline-block">';
$location .= '	 	<span id="returnContainer">';
$location .= '		<select name="return_id" id="return_id" class="pj-form-field w200 required">';
$location .= '			<option value="">-- '.__('lblChoose', true, false).'--</option>';
						foreach($tpl['to_location_arr'] as $k => $v)
						{
$location .= '				<option value="'.$v['id'].'">'.stripslashes($v['name']).'</option>';							
						}
$location .= '		</select>';
$location .= ' 		</span>';
$location .= '      <span id="bsArrivalTime" class="bs-time float_left l5"></span>';
$location .= ' </span>';
$location .= '</p>';

pjAppController::jsonResponse(compact('location', 'bus'));
?>