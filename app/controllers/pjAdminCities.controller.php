<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminCities extends pjAdmin
{
	public function pjActionCheckCity()
	{
		$this->setAjax(true);
		
		if ($this->isXHR() && isset($_POST['locale']))
		{
			$locale = $_POST['locale'];
			
			$value = $_POST['i18n'][$locale]['name'];
			
			$pjCityModel = pjCityModel::factory();
			
			if (isset($_POST['id']) && (int) $_POST['id'] > 0)
			{
				$pjCityModel->where('t1.id !=', $_POST['id']);
			}
			$pjCityModel->where("t1.id IN(SELECT TL.foreign_id FROM `".pjMultiLangModel::factory()->getTable()."` AS TL WHERE TL.model='pjCity' AND TL.field='name' AND TL.content = '".$value."' AND TL.locale='$locale')");
			echo $pjCityModel->findCount()->getData() == 0 ? 'true' : 'false';
		}
		exit;
	}
	
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			if (isset($_POST['city_create']))
			{
				$id = pjCityModel::factory($_POST)->insert()->getInsertId();
				if ($id !== false && (int) $id > 0)
				{
					if (isset($_POST['i18n']))
					{
						pjMultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'pjCity', 'data');
					}
					$err = 'ACT03';
				} else {
					$err = 'ACT04';
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminCities&action=pjActionIndex&err=$err");
			} else {
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
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendJs('pjAdminCities.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
		
	public function pjActionDeleteCity()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $response = array();
                if (pjCityModel::factory()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
                {
                    pjMultiLangModel::factory()->where('model', 'pjCity')->where('foreign_id', $_GET['id'])->eraseAll();
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
                pjAppController::jsonResponse($response);
            }
        }
		exit;
	}
	
	public function pjActionDeleteCityBulk()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                if (isset($_POST['record']) && count($_POST['record']) > 0)
                {
                    pjCityModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                    pjMultiLangModel::factory()->where('model', 'pjCity')->whereIn('foreign_id', $_POST['record'])->eraseAll();
                }
            }
        }
		exit;
	}
	
	public function pjActionGetCity()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjCityModel = pjCityModel::factory()
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer');;
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				$pjCityModel->where('t2.content LIKE', "%$q%");
			}

			$column = 'name';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjCityModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}
			
			$data = $pjCityModel->select('t1.*, t2.content as name')
				->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
				
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
		
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminCities.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSaveCity()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
        {
            if ($this->isXHR())
            {
                $pjCityModel = pjCityModel::factory();
                if (!in_array($_POST['column'], $pjCityModel->i18n))
                {
                    $value = $_POST['value'];
                    
                    $pjCityModel->where('id', $_GET['id'])->limit(1)->modifyAll(array($_POST['column'] => $value));
                } else {
                    pjMultiLangModel::factory()->updateMultiLang(array($this->getLocaleId() => array($_POST['column'] => $_POST['value'])), $_GET['id'], 'pjCity', 'data');
                }
            }
        }
		exit;
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();

		if ($this->isAdmin() || $this->isBusAdmin())
		{
			if (isset($_POST['city_update']))
			{
				pjCityModel::factory()->where('id', $_POST['id'])->limit(1)->modifyAll($_POST);
				if (isset($_POST['i18n']))
				{
					pjMultiLangModel::factory()->updateMultiLang($_POST['i18n'], $_POST['id'], 'pjCity', 'data');
				}
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminCities&action=pjActionIndex&err=ACT01");
				
			} else {
				$pjMultiLangModel = pjMultiLangModel::factory();
				$arr = pjCityModel::factory()->find($_GET['id'])->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminCities&action=pjActionIndex&err=ACT08");
				}
				$arr['i18n'] = $pjMultiLangModel->getMultiLang($arr['id'], 'pjCity');
				
				$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
					->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
					->where('t2.file IS NOT NULL')
					->orderBy('t1.sort ASC')->findAll()->getData();
						
				$lp_arr = array();
				foreach ($locale_arr as $item)
				{
					$lp_arr[$item['id']."_"] = $item['file'];
				}
				$this->set('seat_arr', pjSeatModel::factory()->where('bus_type_id', $_GET['id'])->findAll()->getData());
				
				$this->set('lp_arr', $locale_arr);
				$this->set('locale_str', pjAppController::jsonEncode($lp_arr));
				$this->set('arr', $arr);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendJs('pjAdminCities.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
}
?>