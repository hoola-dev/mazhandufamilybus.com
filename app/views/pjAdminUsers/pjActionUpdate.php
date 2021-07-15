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
	
	pjUtil::printNotice(__('infoEditUserTitle', true, false), __('infoEditUserDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminUsers&amp;action=pjActionUpdate" method="post" id="frmUpdateUser" class="form pj-form">
		<input type="hidden" name="user_update" value="1" />
		<input type="hidden" name="id" value="<?php echo (int) $tpl['arr']['id']; ?>" />
		<p>
			<label class="title"><?php __('lblRole'); ?></label>
			<?php
			if ((int) $tpl['arr']['id'] !== 1)
			{
				?>
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
							?><option value="<?php echo $v['id']; ?>"<?php echo $tpl['arr']['role_id'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['role_name']); ?></option><?php
						}
						?>
					</select>
				</span>
				<?php
			} else {
				?>
				<span class="left">
				<?php
				foreach ($tpl['role_arr'] as $v)
				{
					if ($tpl['arr']['role_id'] == $v['id'])
					{                        
						echo stripslashes($v['role']);
						break;
					}
				}
				?>
				</span>
				<input type="hidden" name="role_id" value="1" />
				<?php
			}
			?>
		</p>
        <?php if($tpl['arr']['role_id'] == 2) { ?>
            <p id="p_agent_code">
                <label class="title">Agent Code</label>
                <span class="pj-form-field-custom pj-form-field-custom-before">                    
                <label class="title"><?php echo $tpl['arr']['user_code']; ?></label>
                </span>
            </p>            
        <?php } ?>    
        <p id="p_agent_type" style="display:<?php echo ($tpl['arr']['role_id'] == 2) ? 'block' : 'none'; ?>">
            <label class="title">Agent Type</label>
            <span class="inline_block">
                <select name="agent_type" id="agent_type" class="pj-form-field required" onchange="javascript:show_hide_open_close();">
                    <option value="">-- <?php __('lblChoose'); ?>--</option>
                    <?php
                    foreach ($tpl['agent_type_arr'] as $v)
                    {
                        if (!$controller->isSuperAgent() && $v['at_id'] ==4) { 
                            //continue;
                        }
                        if ($controller->isSuperAgent() && $v['at_id'] !=4) { 
                            continue;
                        }
                        if ($controller->isBusAdmin() && $v['at_id'] ==2) { 
                            continue;
                        }
                    ?>
                        <option value="<?php echo $v['at_id']; ?>"<?php echo $tpl['arr']['agent_type'] == $v['at_id'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['at_type']); ?></option><?php
                    }
                    ?>
                </select>
            </span>
        </p>
        <p id="p_agent_commission" style="display:<?php echo ($tpl['arr']['role_id'] == 2) ? 'block' : 'none'; ?>">
            <label class="title">Agent Commission %</label>
            <span class="inline_block">
                <input type="text" name="commission_percent" id="commission_percent" value="<?php echo pjSanitize::html($tpl['arr']['commission_percent']); ?>" class="pj-form-field w250 <?php if ($tpl['arr']['role_id'] == 2 && $tpl['arr']['agent_type'] !=1) { ?>required<?php } ?>" />
            </span>
        </p>
        <?php if (!$controller->isSuperAgent()) { ?>
        <p id="p_agent_mobile_app_status" style="display:<?php echo ($tpl['arr']['role_id'] == 2) ? 'block' : 'none'; ?>">
			<label class="title">Mobile App Status</label>
            <span class="inline_block">
                <select name="mobile_app_status" id="mobile_app_status" class="pj-form-field required">
                    <option value="1"<?php echo $tpl['arr']['mobile_app_status'] == 1 ? ' selected="selected"' : NULL; ?>>Show in Mobile App</option>
                    <option value="0"<?php echo $tpl['arr']['mobile_app_status'] == 0 ? ' selected="selected"' : NULL; ?>>Hide in Mobile App</option>                    
                </select>
            </span>				
		</p>
        <p id="p_agent_discount_status">
			<label class="title">Allow to add discount</label>
            <span class="inline_block">
                <select name="discount_status" id="discount_status" class="pj-form-field required">
                    <option value="0"<?php echo $tpl['arr']['discount_status'] == 0 ? ' selected="selected"' : NULL; ?>>No</option>
                    <option value="1"<?php echo $tpl['arr']['discount_status'] == 1 ? ' selected="selected"' : NULL; ?>>Yes</option>                    
                </select>
            </span>				
		</p>
        <?php } ?>
        <p id="p_agent_open" style="display:<?php echo ($tpl['arr']['agent_type'] == 4) ? 'block' : 'none'; ?>">
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
                        <option value="<?php echo date("H:i",$time_now); ?>"<?php echo $tpl['arr']['open_time'] == date("H:i",$time_now) ? ' selected="selected"' : NULL; ?>><?php echo date("h:i a",$time_now); ?></option>';
                    <?php
                        $time_now = strtotime('+30 minutes',$time_now);
                    }
                    ?>
                </select>
            </span>
        </p>
        <p id="p_agent_close" style="display:<?php echo ($tpl['arr']['agent_type'] == 4) ? 'block' : 'none'; ?>">
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
                        <option value="<?php echo date("H:i",$time_now); ?>"<?php echo $tpl['arr']['close_time'] == date("H:i",$time_now) ? ' selected="selected"' : NULL; ?>><?php echo date("h:i a",$time_now); ?></option>';
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
				<input type="text" name="email" id="email" class="pj-form-field required email w200" value="<?php echo pjSanitize::html($tpl['arr']['email']); ?>" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('pass'); ?></label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				<input type="password" name="password" id="password" class="pj-form-field w200" value="<?php echo pjSanitize::html($tpl['arr']['password']); ?>" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblName'); ?></label>
			<span class="inline_block">
				<input type="text" name="name" id="name" value="<?php echo pjSanitize::html($tpl['arr']['name']); ?>" class="pj-form-field w250 required" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblPhone'); ?></label>
			<span class="inline_block">
				<input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['phone'])); ?>" class="pj-form-field w250" />
			</span>
		</p>
        <p>
            <label class="title">Address</label>
            <span class="inline-block">
                <textarea name="address" id="address" class="pj-form-field w500 h120"><?php echo htmlspecialchars(stripslashes($tpl['arr']['address'])); ?></textarea>
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
                        <option value="<?php echo $v['dt_id']; ?>"<?php echo $v['dt_id'] == $tpl['arr']['district'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['dt_name']); ?></option><?php
					}
					?>
				</select>
			</span>
		</p>		
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<?php
			if ((int) $tpl['arr']['id'] !== 1)
			{
				?>
				<span class="inline_block">
					<select name="status" id="status" class="pj-form-field required">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach (__('u_statarr', true) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php echo $k == $tpl['arr']['status'] ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
						}
						?>
					</select>
				</span>
				<?php
			} else {
				$status = __('u_statarr', true)
				?>
				<span class="left"><?php echo @$status[$tpl['arr']['status']]; ?></span>
				<input type="hidden" name="status" value="T" />
				<?php
			}
			?>
		</p>        
		<p>
			<label class="title"><?php __('lblUserCreated'); ?></label>
			<span class="left"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($tpl['arr']['created'])); ?>, <?php echo date("H:i", strtotime($tpl['arr']['created'])); ?></span>
		</p>
		<p>
			<label class="title"><?php __('lblIp'); ?></label>
			<span class="left"><?php echo $tpl['arr']['ip']; ?></span>
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