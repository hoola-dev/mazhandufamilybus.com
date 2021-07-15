<div class="login-box">
	<div class="logo-img">
	  <img src="<?php echo PJ_INSTALL_URL; ?>app/web/img/backend/logo.jpg" >
	</div>
	<div class="login-headtxt">
	   <h3><?php __('adminLogin'); ?></h3>
	</div>
	<div class="login-form-wrap">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionLogin" method="post" id="frmLoginAdmin" class="form">
			<input type="hidden" name="login_user" value="1" />
			<div class="d--flex">
				<label class="title"><?php __('email'); ?>:</label>
				<!-- <span class="pj-form-field-custom pj-form-field-custom-before">
					<span class="pj-form-field-before"><abbr class="pj-form-field-icon-email"></abbr></span>
				</span> -->
				<input type="text" name="login_email" id="login_email" class="pj-form-field required email width--100" />
            </div>
			<div class="d--flex">
				 <label class="title"><?php __('pass'); ?>:</label>
				<!--<span class="pj-form-field-custom pj-form-field-custom-before">
					<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				</span> -->
				<input type="password" name="login_password" id="login_password" class="pj-form-field required width--100" autocomplete="off" />
			</div>
			<div class="forgot-password">
		    	<a class="no-decor l10" href="<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdmin&action=pjActionForgot"><?php __('lblForgot'); ?></a>
			</div>
			<div >
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnLogin', false, true); ?>" class="pj-button login--btn" />
            </div>
			<?php
			if (isset($_GET['err']))
			{
				$err = __('login_err', true);
				switch ($_GET['err'])
				{
					case 1:
						?><em><label class="err" style="display: inline;"><?php echo $err[1]; ?></label></em><?php
						break;
					case 2:
						?><em><label class="err" style="display: inline;"><?php echo $err[2]; ?></label></em><?php
						break;
					case 3:
						?><em><label class="err" style="display: inline;"><?php echo $err[3]; ?></label></em><?php
						break;
				}
			}
			?>
		</form>
	</div>	
</div>
<div class="login-txt">
   <h1>Mazhandu <br> Bus Management System</h1>
    <p>
	   Copyright &copy; <?php echo date("Y"); ?> <a href="https://www.zedbooking.com" target="_blank">ZED Booking</a>
    </p>
</div>
