<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminRoutes extends pjAdmin
{
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			if (isset($_POST['route_create']))
			{				
				$pjMultiLangModel = pjMultiLangModel::factory();
				$id = pjRouteModel::factory($_POST)->insert()->getInsertId();
				if ($id !== false && (int) $id > 0)
				{
					if (isset($_POST['i18n']))
					{
						$pjMultiLangModel->saveMultiLang($_POST['i18n'], $id, 'pjRoute', 'data');
						
					}
					if(isset($_POST['index_arr']) && $_POST['index_arr'] != '')
					{
						$index_arr = explode("|", $_POST['index_arr']);
						
						$pjRouteCityModel = pjRouteCityModel::factory();
						foreach($index_arr as $k => $index)
						{
							if(isset($_POST['city_id_' . $index]) && (int) $_POST['city_id_' . $index] > 0)
							{
								$city_id = $_POST['city_id_' . $index];
								$data = array();
								$data['route_id'] = $id;
								$data['city_id'] = $city_id;
								$data['order'] = $k + 1;
								$pjRouteCityModel->reset()->setAttributes($data)->insert();
								
								if($k == 0)
								{
									$i18n_arr = $pjMultiLangModel->reset()->getMultiLang($city_id, 'pjCity');
									$i18n_arr = pjUtil::changeLangField($i18n_arr, 'from', 'name');
									$pjMultiLangModel->reset()->saveMultiLang($i18n_arr, $id, 'pjRoute', 'data');
								}
								if($k == count($index_arr) - 1)
								{
									$i18n_arr = $pjMultiLangModel->reset()->getMultiLang($city_id, 'pjCity');
									$i18n_arr = pjUtil::changeLangField($i18n_arr, 'to', 'name');
									$pjMultiLangModel->reset()->saveMultiLang($i18n_arr, $id, 'pjRoute', 'data');
								}
							}
						}
					}
					pjRouteModel::factory()->updateRouteDetail($id);
					
					$err = 'AR03';
				} else {
					$err = 'AR04';
				}
				
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminRoutes&action=pjActionIndex&err=$err");
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
				
				if(isset($_GET['id']))
				{
					$direction = 'ASC';
					if($_GET['type'] == 'reverse')
					{
						$direction = 'DESC';
					}
					$city_id_arr = pjRouteCityModel::factory()->getCity($_GET['id'], $direction);
					$this->set('city_id_arr', $city_id_arr);
				}
				
				$city_arr = pjCityModel::factory()
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select('t1.*, t2.content as name')
					->where('status', 'T')
					->orderBy("name ASC")
					->findAll()
					->getData();
				$this->set('city_arr', $city_arr);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendJs('pjAdminRoutes.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionDeleteRoute()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $response = array();
                
                if (pjRouteModel::factory()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
                {
                    pjMultiLangModel::factory()->where('model', 'pjRoute')->where('foreign_id', $_GET['id'])->eraseAll();
                    pjRouteDetailModel::factory()->where('route_id', $_GET['id'])->eraseAll();
                    pjRouteCityModel::factory()->where('route_id', $_GET['id'])->eraseAll();
                    
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
                
                pjAppController::jsonResponse($response);
            }
        }
		exit;
	}
	
	public function pjActionDeleteRouteBulk()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                if (isset($_POST['record']) && count($_POST['record']) > 0)
                {
                    pjRouteModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                    pjMultiLangModel::factory()->where('model', 'pjRoute')->whereIn('foreign_id', $_POST['record'])->eraseAll();
                    pjRouteDetailModel::factory()->whereIn('route_id', $_POST['record'])->eraseAll();
                    pjRouteCityModel::factory()->whereIn('route_id', $_POST['record'])->eraseAll();
                }
            }
        }
		exit;
	}
	
	public function pjActionExportRoute()
	{
		$this->checkLogin();
		
		if (isset($_POST['record']) && is_array($_POST['record']))
		{
			$arr = pjRouteModel::factory()	->select('t1.id, t2.content as title')
											->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
											->whereIn('id', $_POST['record'])->findAll()->getData();
			$csv = new pjCSV();
			$csv
				->setHeader(true)
				->setName("Routes-".time().".csv")
				->process($arr)
				->download();
		}
		exit;
	}
	
	public function pjActionGetRoute()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjRouteModel = pjRouteModel::factory()
							->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
							->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.id AND t3.field='from' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
							->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.id AND t4.field='to' AND t4.locale='".$this->getLocaleId()."'", 'left outer');
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				$pjRouteModel->where('t2.content LIKE', "%$q%");
			}
	
			if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
			{
				$pjRouteModel->where('t1.status', $_GET['status']);
			}
			
			$column = 'title';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjRouteModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $pjRouteModel
				->select(" t1.id, t1.status, t2.content as title, t3.content as `from`, t4.content as `to`")
				->orderBy("$column $direction")
				->limit($rowCount, $offset)
				->findAll()
				->getData();
			
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor() || $this->isInvestor())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminRoutes.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSaveRoute()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjRouteModel = pjRouteModel::factory();
			if (!in_array($_POST['column'], $pjRouteModel->i18n))
			{
				$value = $_POST['value'];
				
				$pjRouteModel->where('id', $_GET['id'])->limit(1)->modifyAll(array($_POST['column'] => $value));
			} else {
				pjMultiLangModel::factory()->updateMultiLang(array($this->getLocaleId() => array($_POST['column'] => $_POST['value'])), $_GET['id'], 'pjRoute', 'data');
			}
		}
		exit;
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
				
			if (isset($_POST['route_update']))
			{
				$pjMultiLangModel = pjMultiLangModel::factory();
				$pjRouteModel = pjRouteModel::factory();
				$arr = $pjRouteModel->find($_POST['id'])->getData();
				if (empty($arr))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminRoutes&action=pjActionIndex&err=AR08");
				}
				
				$pjRouteModel->reset()->where('id', $_POST['id'])->limit(1)->modifyAll($_POST);
				if (isset($_POST['i18n']))
				{
					$pjMultiLangModel->updateMultiLang($_POST['i18n'], $_POST['id'], 'pjRoute', 'data');
				}
				
				if(!isset($_POST['has_bookings']))
				{
					$pjRouteCityModel = pjRouteCityModel::factory();
					$pjRouteCityModel->where('route_id', $_POST['id'])->eraseAll();
					if(isset($_POST['index_arr']) && $_POST['index_arr'] != '')
					{
						$index_arr = explode("|", $_POST['index_arr']);
					
						foreach($index_arr as $k => $index)
						{
							if(isset($_POST['city_id_' . $index]) && (int) $_POST['city_id_' . $index] > 0)
							{
								$city_id = $_POST['city_id_' . $index];
								$data = array();
								$data['route_id'] = $_POST['id'];
								$data['city_id'] = $city_id;
								$data['order'] = $k + 1;
								$pjRouteCityModel->reset()->setAttributes($data)->insert();
								if($k == 0)
								{
									$i18n_arr = $pjMultiLangModel->reset()->getMultiLang($city_id, 'pjCity');
									$i18n_arr = pjUtil::changeLangField($i18n_arr, 'from', 'name');
									$pjMultiLangModel->reset()->updateMultiLang($i18n_arr, $_POST['id'], 'pjRoute', 'data');
								}
								if($k == (count($index_arr) - 1))
								{
									$i18n_arr = $pjMultiLangModel->reset()->getMultiLang($city_id, 'pjCity');
									$i18n_arr = pjUtil::changeLangField($i18n_arr, 'to', 'name');
									$pjMultiLangModel->reset()->updateMultiLang($i18n_arr, $_POST['id'], 'pjRoute', 'data');
								}
							}
						}
					}
					$pjRouteModel->updateRouteDetail($_POST['id']);
					$pjRouteModel->updateBusTime($_POST['id']);
					$pjRouteModel->updateBusPrice($_POST['id']);
				}
				
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminRoutes&action=pjActionUpdate&id=" . $_POST['id'] . "&err=AR01");
				
			} else {
				$pjMultiLangModel = pjMultiLangModel::factory();
				
				$arr = pjRouteModel::factory()->find($_GET['id'])->getData();
				
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminRoutes&action=pjActionIndex&err=AR08");
				}
				$arr['i18n'] = $pjMultiLangModel->getMultiLang($arr['id'], 'pjRoute');
				$arr['city'] = pjRouteCityModel::factory()->getCity($arr['id'], 'ASC');
				
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
				$this->set('arr', $arr);
				
				$city_arr = pjCityModel::factory()
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select('t1.*, t2.content as name')
					->where('status', 'T')
					->orderBy("name ASC")
					->findAll()
					->getData();
				$this->set('city_arr', $city_arr);
				
				$cnt_bookings = pjBookingModel::factory()
					->where("t1.bus_id IN (SELECT TB.id FROM `".pjBusModel::factory()->getTable()."` AS TB WHERE TB.route_id = ".$_GET['id'].")")
					->findCount()
					->getData();
				$this->set('cnt_bookings', $cnt_bookings);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendJs('pjAdminRoutes.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
}
?>