<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminOptions extends pjAdmin
{
	public function pjActionIndex()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			$this->set('arr', $arr);
			
            $this->appendJs('pjAdminOptions.js');            
            
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			if (isset($_POST['options_update']))
			{
				$OptionModel = new pjOptionModel();
			
				foreach ($_POST as $key => $value)
				{
					if (preg_match('/value-(string|text|int|float|enum|bool|color)-(.*)/', $key) === 1)
					{
						list(, $type, $k) = explode("-", $key);
						if (!empty($k))
						{
							$OptionModel
								->reset()
								->where('foreign_id', $this->getForeignId())
								->where('`key`', $k)
								->limit(1)
								->modifyAll(array('value' => $value));
						}
					}
                }                

                if (PJ_COMBINED_API_IS_ACTIVE) {
                    $current_date_value = pjOptionModel::factory()
                    ->where('foreign_id', $this->getForeignId())
                    ->where('`key`', 'o_date_format')
                    ->findAll()
                    ->getData();

                    $explode = explode('::',$current_date_value[0]['value']);

                    if ($this->option_arr['o_date_format'] != $explode[1]) {
                        $result = $this->requestAction(array('controller' => 'pjCombinedApi', 'action' => 'pjActionSetDate', 'params' => array('date_format'=>$explode[1])), array('return'));
                    }
                }

				if (isset($_POST['i18n']))
				{
					pjMultiLangModel::factory()->updateMultiLang($_POST['i18n'], 1, 'pjOption', 'data');
				}
				
				if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name']))
				{
					$img_arr = pjOptionModel::factory()
						->where('t1.foreign_id', $this->getForeignId())
						->where('t1.key', 'o_image_path')
						->orderBy('t1.order ASC')
						->findAll()
						->getData();
					if(!empty($img_arr[0]['o_image_path']))
					{
						@unlink(PJ_INSTALL_PATH . $img_arr[0]['o_image_path']);
					}
					
					$Image = new pjImage();
					if ($Image->getErrorCode() !== 200)
					{
						$Image->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
						if ($Image->load($_FILES['image']))
						{
							$resp = $Image->isConvertPossible();
							if ($resp['status'] === true)
							{
								$hash = md5(uniqid(rand(), true));
								$source_path = PJ_UPLOAD_PATH . $hash . '.' . $Image->getExtension();
								if ($Image->save($source_path))
								{
									$OptionModel
										->reset()
										->where('foreign_id', $this->getForeignId())
										->where('`key`', 'o_image_path')
										->limit(1)
										->modifyAll(array('value' => $source_path));
									$OptionModel
										->reset()
										->where('foreign_id', $this->getForeignId())
										->where('`key`', 'o_image_name')
										->limit(1)
										->modifyAll(array('value' => $_FILES['image']['name']));
								}
							}
						}
					}
				}
				
				if (isset($_POST['next_action']))
				{
					switch ($_POST['next_action'])
					{
						case 'pjActionIndex':
							$err = 'AO01';
							break;
						case 'pjActionBooking':
							$err = 'AO02';
							break;
						case 'pjActionBookingForm':
							$err = 'AO03';
							break;
						case 'pjActionConfirmation':
							$err = 'AO04&tab_id=' . $_POST['tab_id'];
							break;
						case 'pjActionTemplate':
							$err = 'AO05';
							break;
						case 'pjActionTerm':
							$err = 'AO06';
							break;
						case 'pjActionContent':
							$err = 'AO07';
							break;
					}
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminOptions&action=" . @$_POST['next_action'] . "&err=$err");
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionInstall()
	{
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.title')
				->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left outer')
				->orderBy('t1.sort ASC')->findAll()->getData();
			$this->set('locale_arr', $locale_arr);
					
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
		
	public function pjActionPreview()
	{
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.title')
			->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left outer')
			->orderBy('t1.sort ASC')->findAll()->getData();
			$this->set('locale_arr', $locale_arr);
			
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionBooking()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC, t1.key ASC')
				->findAll()
				->getData();
			
			$this->set('arr', $arr);
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('additional-methods.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionBookingForm()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			$this->set('arr', $arr);
			$this->appendJs('pjAdminOptions.js');
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionConfirmation()
	{
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			$arr['i18n'] = pjMultiLangModel::factory()->getMultiLang(1, 'pjOption');
				
			$this->set('arr', $arr);
			
			$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
				->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
				->where('t2.file IS NOT NULL')
				->orderBy('t1.sort ASC')->findAll()->getData();
			
			$lp_arr = array();
			foreach ($locale_arr as $item)
			{
				$lp_arr[$item['id']."_"] = $item['file'];
			}
            $this->set('lp_arr', $locale_arr);
			$this->set('locale_str', pjAppController::jsonEncode($lp_arr));
			
			$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminOptions.js');
			
		}
	}
	
	public function pjActionTemplate(){
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			$arr['i18n'] = pjMultiLangModel::factory()->getMultiLang(1, 'pjOption');
				
			$this->set('arr', $arr);
			
			$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
				->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
				->where('t2.file IS NOT NULL')
				->orderBy('t1.sort ASC')->findAll()->getData();
			
			$lp_arr = array();
			foreach ($locale_arr as $item)
			{
				$lp_arr[$item['id']."_"] = $item['file'];
			}
			$this->set('lp_arr', $locale_arr);
			$this->set('locale_str', pjAppController::jsonEncode($lp_arr));
			
			$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('tinymce.min.js', PJ_THIRD_PARTY_PATH . 'tinymce/');
			$this->appendJs('pjAdminOptions.js');
			
		}
	}
	
	public function pjActionContent(){
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->where('t1.key', 'o_content')
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			$img_arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->where('t1.key', 'o_image_path')
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
				
			$arr = !empty($arr) ? $arr[0] : array();
			$image_arr = !empty($img_arr) ? $img_arr[0] : array();
			
			$arr['i18n'] = pjMultiLangModel::factory()->getMultiLang(1, 'pjOption');
			
			$this->set('arr', $arr);
			$this->set('image_arr', $image_arr);
			
			$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
				->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
				->where('t2.file IS NOT NULL')
				->orderBy('t1.sort ASC')->findAll()->getData();
			
			$lp_arr = array();
			foreach ($locale_arr as $item)
			{
				$lp_arr[$item['id']."_"] = $item['file'];
			}
			$this->set('lp_arr', $locale_arr);
			$this->set('locale_str', pjAppController::jsonEncode($lp_arr));
			
			$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			$this->appendJs('pjAdminOptions.js');
			
		}
	}
	
	public function pjActionTerm()
	{
		$this->checkLogin();
		
		if ($this->isAdmin())
		{
			
			$arr = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			$arr['i18n'] = pjMultiLangModel::factory()->getMultiLang(1, 'pjOption');

			$this->set('arr', $arr);
			
			$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
				->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
				->where('t2.file IS NOT NULL')
				->orderBy('t1.sort ASC')->findAll()->getData();
			
			$lp_arr = array();
			foreach ($locale_arr as $item)
			{
				$lp_arr[$item['id']."_"] = $item['file'];
			}
			$this->set('lp_arr', $locale_arr);
			$this->set('locale_str', pjAppController::jsonEncode($lp_arr));
			
			$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminOptions.js');
			
		}
	}
	
	public function pjActionDeleteImage()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$response = array();
			$OptionModel = pjOptionModel::factory();
			
			$img_arr = $OptionModel
				->where('t1.foreign_id', $this->getForeignId())
				->where('t1.key', 'o_image_path')
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			
			if(!empty($img_arr))
			{
				$img_arr = $img_arr[0];
				if(!empty($img_arr['value']))
				{
					@unlink(PJ_INSTALL_PATH . $img_arr['value']);
				}
				$OptionModel
							->reset()
							->where('foreign_id', $this->getForeignId())
							->where('`key`', 'o_image_path')
							->limit(1)
							->modifyAll(array('value' => ''));
				$OptionModel
							->reset()
							->where('foreign_id', $this->getForeignId())
							->where('`key`', 'o_image_name')
							->limit(1)
							->modifyAll(array('value' => ''));
				$response['code'] = 200;
			}else{
				$response['code'] = 100;
			}
			
			pjAppController::jsonResponse($response);
		}
	}
	
	public function pjActionUpdateTheme()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			pjOptionModel::factory()
			->where('foreign_id', $this->getForeignId())
			->where('`key`', 'o_theme')
			->limit(1)
			->modifyAll(array('value' => 'theme1|theme2|theme3|theme4|theme5|theme6|theme7|theme8|theme9|theme10::theme' . $_GET['theme']));
				
		}
	}
}
?>