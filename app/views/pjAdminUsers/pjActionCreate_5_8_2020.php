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
	
	pjUtil::printNotice(__('infoAddUserTitle', true, false), __('infoAddUserDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminUsers&amp;action=pjActionCreate" method="post" id="frmCreateUser" class="form pj-form" autocomplete="off">
		<input type="hidden" name="user_create" value="1" />
        <p>
            <label class="title"><?php __('lblRole'); ?></label>
            <span class="inline_block">
                <select name="role_id" id="role_id" class="pj-form-field required" onchange="javascript:show_hide_agent_type();">
                    <option value="">-- <?php __('lblChoose'); ?>--</option>
                    <?php
                    foreach ($tpl['role_arr'] as $v)
                    {
                        if ($controller->isBusAdmin() && $v['id'] == 1) { 
                            continue;
                        }
                        if ($controller->isSuperAgent() && $v['id'] !=2) { 
                            continue;
                        }
                        ?>
                        <option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['role_name']); ?></option><?php
                    }
                    ?>
                </select>
            </span>
        </p>
        <p id="p_agent_type" style="display:none;">
            <label class="title">Agent Type</label>
            <span class="inline_block">
                <select name="agent_type" id="agent_type" class="pj-form-field required" onchange="javascript:show_hide_open_close();">
                    <option value="">-- <?php __('lblChoose'); ?>--</option>
                    <?php
                    foreach ($tpl['agent_type_arr'] as $v)
                    {
                        if (!$controller->isSuperAgent() && $v['at_id'] ==4) { 
                            continue;
                        }
                        if ($controller->isSuperAgent() && $v['at_id'] !=4) { 
                            continue;
                        }
                    ?>
                        <option value="<?php echo $v['at_id']; ?>"><?php echo stripslashes($v['at_type']); ?></option><?php
                    }
                    ?>
                </select>
            </span>
        </p>
        <p id="p_agent_commission" style="display:none;">
            <label class="title">Agent Commission %</label>
            <span class="inline_block">
                <input type="text" name="commission_percent" id="commission_percent" value="" class="pj-form-field w250 required" />
            </span>
        </p>
        <p id="p_agent_open" style="display:none;">
            <label class="title">Open Time</label>
            <span class="inline_block">
                <select name="open_time" id="open_time" class="pj-form-field required">
                    <option value="">-- <?php __('lblChoose'); ?>--</option>
                    <?php
                    $start = "00:00";
                    $end = "23:30";
                
                    $start_time = strtotime($start);
                    $end_time = strtotime($end);
                    $time_now = $start_time;
                    while($time_now <= $end_time){
                    ?>
                        <option value="<?php echo date("H:i",$time_now); ?>"><?php echo date("h:i a",$time_now); ?></option>';
                    <?php
                        $time_now = strtotime('+30 minutes',$time_now);
                    }
                    ?>
                </select>
            </span>
        </p>
        <p id="p_agent_close" style="display:none;">
            <label class="title">Close Time</label>
            <span class="inline_block">
                <select name="close_time" id="close_time" class="pj-form-field required">
                    <option value="">-- <?php __('lblChoose'); ?>--</option>
                    <?php
                    $start = "00:00";
                    $end = "23:30";
                
                    $start_time = strtotime($start);
                    $end_time = strtotime($end);
                    $time_now = $start_time;
                    while($time_now <= $end_time){
                    ?>
                        <option value="<?php echo date("H:i",$time_now); ?>"><?php echo date("h:i a",$time_now); ?></option>';
                    <?php
                        $time_now = strtotime('+30 minutes',$time_now);
                    }
                    ?>
                </select>
            </span>
        </p>
		<p>
			<label class="title"><?php __('email'); ?></label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-email"></abbr></span>
				<input type="text" name="email" id="email" class="pj-form-field required email w200" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('pass'); ?></label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				<input type="password" name="password" id="password" class="pj-form-field required w200" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblName'); ?></label>
			<span class="inline_block">
				<input type="text" name="name" id="name" class="pj-form-field w250 required" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblPhone'); ?></label>
			<span class="inline_block">
				<input type="text" name="phone" id="phone" class="pj-form-field w250" />
			</span>
		</p>
        <p>
            <label class="title">Address</label>
            <span class="inline-block">
                <textarea name="address" id="address" class="pj-form-field w500 h120"></textarea>
            </span>
        </p>
        <p>
			<label class="title">District</label>
			<span class="inline_block">
				<select name="district" id="district" class="pj-form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach ($tpl['district_arr'] as $v)
					{
                    ?>
                        <option value="<?php echo $v['dt_id']; ?>"><?php echo stripslashes($v['dt_name']); ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<span class="inline_block">
				<select name="status" id="status" class="pj-form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('u_statarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"<?php echo $k == 'T' ? ' selected="selected"' : NULL;?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminUsers&action=pjActionIndex';" />
		</p>
	</form>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.email_taken = "<?php __('pj_email_taken', false, true); ?>";
	</script>
	<?php
}
?>