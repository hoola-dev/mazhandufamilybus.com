<?php
$ticket = '<select name="ticket_id" id="ticket_id" class="pj-form-field w250 required">';
$ticket .= sprintf('<option value="">-- %s --</option>', __('lblChoose', true));
if (isset($tpl['ticket_arr']) && is_array($tpl['ticket_arr']))
{
	foreach ($tpl['ticket_arr'] as $v)
	{
		$ticket .= sprintf('<option value="%u">%s</option>', $v['id'], stripslashes($v['title']));
	}
}
$ticket .= '</select>';

pjAppController::jsonResponse(compact('ticket'));
?>