<?php
$route = '<select name="route_id" id="route_id" class="pj-form-field w250 required">';
$route .= sprintf('<option value="">-- %s --</option>', __('lblChoose', true));
if (isset($tpl['route_arr']) && is_array($tpl['route_arr']))
{
	foreach ($tpl['route_arr'] as $v)
	{
		$route .= sprintf('<option value="%u">%s</option>', $v['id'], stripslashes($v['title']));
	}
}
$route .= '</select>';

pjAppController::jsonResponse(compact('route'));
?>