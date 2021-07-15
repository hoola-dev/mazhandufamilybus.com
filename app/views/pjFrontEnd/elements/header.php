<header class="panel-heading clearfix pjBsHeader">
	<div class="btn-group pull-left pjBsNav">
		<?php
		$action = $_GET['action'];
		$menu_label = __('front_step_1', true);
		switch ($action) {
			case 'pjActionSearch':
				$menu_label = __('front_step_1', true);
				;
				break;
			case 'pjActionSeats':
				$menu_label = __('front_step_2', true, false);
				;
				break;
			case 'pjActionCheckout':
				$menu_label = __('front_step_3', true, false);
				;
				break;
			case 'pjActionPreview':
				$menu_label = __('front_step_4', true, false);
				;
				break;
		}
		$menu_items = __('front_menu_items', true);
		?>
	
		<button class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<?php echo $menu_label;?>
			<span class="caret"></span>
		</button>

		<ul class="dropdown-menu">
			<li<?php echo $action == 'pjActionSearch' ? ' class="active"' : NULL;?>><a href="#" data-load="Search" class="pjBrBtnMenu<?php echo in_array($action, array('pjActionSearch')) ? ' active' : (in_array($action, array('pjActionSeats', 'pjActionCheckout', 'pjActionPreview')) ? NULL : ' pjBsDdisabled') ; ?>"><?php __('front_step_1');?></a></li>
			<li<?php echo $action == 'pjActionSeats' ? ' class="active"' : NULL;?>><a href="#" data-load="Seats" class="pjBrBtnMenu<?php echo in_array($action, array('pjActionSeats')) ? ' active' : (in_array($action, array('pjActionCheckout', 'pjActionPreview')) ? NULL : ' pjBsDdisabled'); ?>"><?php __('front_step_2');?></a></li>
			<li<?php echo $action == 'pjActionCheckout' ? ' class="active"' : NULL;?>><a href="#" data-load="Checkout" class="pjBrBtnMenu<?php echo in_array($action, array('pjActionCheckout')) ? ' active' : (in_array($action, array('pjActionPreview')) ? NULL : ' pjBsDdisabled'); ?>"><?php __('front_step_3');?></a></li>
			<li<?php echo $action == 'pjActionPreview' ? ' class="active"' : NULL;?>><a href="#" data-load="Preview" class="pjBrBtnMenu<?php echo in_array($action, array('pjActionPreview')) ? ' active' : ' pjBsDdisabled'; ?>"><?php __('front_step_4');?></a></li>
		</ul><!-- /.dropdown-menu -->
	</div><!-- /.btn-group pull-left pjBsNav -->

	<?php
	if (isset($tpl['locale_arr']) && is_array($tpl['locale_arr']) && !empty($tpl['locale_arr']) && count($tpl['locale_arr']) > 1)
	{ 
		$selected_title = null;
		$selected_src = NULL;
		foreach ($tpl['locale_arr'] as $locale)
		{
			if($controller->getLocaleId() == $locale['id'])
			{
				$selected_title = $locale['language_iso'];
				$lang_iso = explode("-", $selected_title);
				if(isset($lang_iso[1]))
				{
					$selected_title = $lang_iso[1];
				}
				if (!empty($locale['flag']) && is_file(PJ_INSTALL_PATH . $locale['flag']))
				{
					$selected_src = PJ_INSTALL_URL . $locale['flag'];
				} elseif (!empty($locale['file']) && is_file(PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'])) {
					$selected_src = PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'];
				}
				break;
			}
		}
		?>
		<div class="btn-group pull-right pjBsNav pjBsNavLang">
			<button class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<img src="<?php echo $selected_src; ?>" alt="">
				<span class="title"><?php echo $selected_title; ?></span>
				<span class="caret"></span>
			</button>
	
			<ul class="dropdown-menu">
				<?php
				foreach ($tpl['locale_arr'] as $locale)
				{
					$selected_src = NULL;
					if (!empty($locale['flag']) && is_file(PJ_INSTALL_PATH . $locale['flag']))
					{
						$selected_src = PJ_INSTALL_URL . $locale['flag'];
					} elseif (!empty($locale['file']) && is_file(PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'])) {
						$selected_src = PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'];
					}
					?>
					<li<?php echo $controller->getLocaleId() == $locale['id'] ? ' class="active"' : NULL;?>>
						<a href="#" class="bsSelectorLocale" rel="<?php echo $locale['id']; ?>" data-id="<?php echo $locale['id']; ?>" data-dir="<?php echo $locale['dir']; ?>" title="<?php echo htmlspecialchars($locale['title']); ?>">
							<img src="<?php echo $selected_src; ?>" alt="">
							<?php echo pjSanitize::html($locale['name']); ?>
						</a>
					</li>
					<?php
				} 
				?>
			</ul><!-- /.dropdown-menu -->
		</div><!-- /.btn-group pull-right pjBsNav pjBsNavLang -->
		<?php
	} 
	?>
</header><!-- /.panel-heading clearfix pjBsHeader -->