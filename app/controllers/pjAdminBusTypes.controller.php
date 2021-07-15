<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminBusTypes extends pjAdmin
{
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			$post_max_size = pjUtil::getPostMaxSize();
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size)
			{
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminBusTypes&action=pjActionIndex&err=ABT05");
			}
			if (isset($_POST['bus_type_create']))
			{
				$pjBusTypeModel = pjBusTypeModel::factory();
				$pjSeatModel = pjSeatModel::factory();
				
				$id = $pjBusTypeModel->setAttributes($_POST)->insert()->getInsertId();
				if ($id !== false && (int) $id > 0)
				{
					if (isset($_POST['i18n']))
					{
						pjMultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'pjBusType', 'data');
					}
					
					if (isset($_FILES['seats_map']))
					{
						if($_FILES['seats_map']['error'] == 0)
						{
							if (is_writable('app/web/upload/bus_types'))
							{
								$Image = new pjImage();
								if ($Image->getErrorCode() !== 200)
								{
									$Image->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
									if ($Image->load($_FILES['seats_map']))
									{
										$resp = $Image->isConvertPossible();
										if ($resp['status'] === true)
										{
											$hash = md5(uniqid(rand(), true));
											$image_path = PJ_UPLOAD_PATH . 'bus_types/' . $id . '_' . $hash . '.' . $Image->getExtension();
											
											$Image->loadImage();
											$Image->saveImage($image_path);
											$data = array();
											$data['seats_map'] = $image_path;
																													
											$pjBusTypeModel->reset()->where('id', $id)->limit(1)->modifyAll($data);
										}
									}
								}
							}else{
								pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionUpdate&id=$id&err=ABT11");
							}
						}else if($_FILES['seats_map']['error'] != 4){
							pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionUpdate&id=$id&err=ABT09");
						}
					}
					if (empty($_FILES['image']['tmp_name']))
					{
						for($i = 1; $i <= $_POST['seats_count']; $i++)
						{
							$sdata = array();
							$sdata['bus_type_id'] = $id;
							$sdata['name'] = $i;
							$pjSeatModel->reset()->setAttributes($sdata)->insert();
						}
					}
					$err = 'ABT03';
					
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionUpdate&id=$id&err=$err");
				} else {
					$err = 'ABT04';
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionIndex&err=$err");
				}
				
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
				$this->appendJs('pjAdminBusTypes.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionDeleteBusType()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $response = array();
                $pjBusTypeModel = pjBusTypeModel::factory();
                $arr = $pjBusTypeModel->find($_GET['id'])->getData();
                if ($pjBusTypeModel->reset()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
                {
                    if (file_exists(PJ_INSTALL_PATH . $arr['seats_map']))
                    {
                        @unlink(PJ_INSTALL_PATH . $arr['seats_map']);
                    }
                    pjMultiLangModel::factory()->where('model', 'pjBusType')->where('foreign_id', $_GET['id'])->eraseAll();
                    pjSeatModel::factory()->where('bus_type_id', $_GET['id'])->eraseAll();
                    
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
                
                pjAppController::jsonResponse($response);
            }
        }
		exit;
	}
	
	public function pjActionDeleteBusTypeBulk()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                if (isset($_POST['record']) && count($_POST['record']) > 0)
                {
                    $pjBusTypeModel = pjBusTypeModel::factory();
                    $arr = $pjBusTypeModel->whereIn('id', $_POST['record'])->findAll()->getData();
                    foreach($arr as $v)
                    {
                        if (file_exists(PJ_INSTALL_PATH . $v['seats_map'])) {
                            @unlink(PJ_INSTALL_PATH . $v['seats_map']);
                        }
                    }
                    $pjBusTypeModel->reset()->whereIn('id', $_POST['record'])->eraseAll();
                    pjMultiLangModel::factory()->where('model', 'pjBusType')->whereIn('foreign_id', $_POST['record'])->eraseAll();
                    pjSeatModel::factory()->whereIn('bus_type_id', $_POST['record'])->eraseAll();
                }
            }
        }
		exit;
	}
	
	public function pjActionExportBusType()
	{
		$this->checkLogin();
		
		if (isset($_POST['record']) && is_array($_POST['record']))
		{
			$arr = pjBusTypeModel::factory()->select('t1.id, t2.content as name')
											->join('pjMultiLang', "t2.model='pjBusType' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
											->whereIn('id', $_POST['record'])->findAll()->getData();
			$csv = new pjCSV();
			$csv
				->setHeader(true)
				->setName("BusTypes-".time().".csv")
				->process($arr)
				->download();
		}
		exit;
	}
	
	public function pjActionGetBusType()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjBusTypeModel = pjBusTypeModel::factory()
							->join('pjMultiLang', "t2.model='pjBusType' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer');
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				$pjBusTypeModel->where('t2.content LIKE', "%$q%");
			}
			if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
			{
				$pjBusTypeModel->where('t1.status', $_GET['status']);
			}
			$column = 'name';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjBusTypeModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $pjBusTypeModel->select(" t1.id, t1.seats_map, t1.seats_count, t1.status,t1.seat_layout, t2.content as name")
								 ->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
				
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
			$this->appendJs('pjAdminBusTypes.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSaveBusType()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $pjBusTypeModel = pjBusTypeModel::factory();
                if (!in_array($_POST['column'], $pjBusTypeModel->i18n))
                {
                    $value = $_POST['value'];
                    
                    $pjBusTypeModel->where('id', $_GET['id'])->limit(1)->modifyAll(array($_POST['column'] => $value));
                } else {
                    pjMultiLangModel::factory()->updateMultiLang(array($this->getLocaleId() => array($_POST['column'] => $_POST['value'])), $_GET['id'], 'pjBusType', 'data');
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
			$post_max_size = pjUtil::getPostMaxSize();
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size)
			{
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminBusTypes&action=pjActionIndex&err=ABT06");
			}	
			if (isset($_POST['bus_type_update']))
			{
				$pjBusTypeModel = pjBusTypeModel::factory();
				$pjSeatModel = pjSeatModel::factory();
				
				$arr = $pjBusTypeModel->find($_POST['id'])->getData();
				if (empty($arr))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionIndex&err=ABT08");
				}
				
				$data = array();
				
				if (isset($_FILES['seats_map']))
				{
					if($_FILES['seats_map']['error'] == 0)
					{
						if (is_writable('app/web/upload/bus_types'))
						{
							$Image = new pjImage();
							if ($Image->getErrorCode() !== 200)
							{
								$Image->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
								if ($Image->load($_FILES['seats_map']))
								{
									$resp = $Image->isConvertPossible();
									if ($resp['status'] === true)
									{
										$hash = md5(uniqid(rand(), true));
										$image_path = PJ_UPLOAD_PATH . 'bus_types/' . $_POST['id'] . '_' . $hash . '.' . $Image->getExtension();
										
										$Image->loadImage();
										$Image->saveImage($image_path);
										$data['seats_map'] = $image_path;
									}
								}
							}
										
							$pjSeatModel->where('bus_type_id', $_POST['id'])->eraseAll();
							if(isset($_POST['seats_count']))
							{
								unset($_POST['seats_count']);
							}
						}else{
							pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionUpdate&id=".$_POST['id']."&err=ABT11");
						}
					}else if($_FILES['seats_map']['error'] != 4){
						pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionUpdate&id=".$_POST['id']."&err=ABT10");
					}
				}
				
				if (isset($_POST['seats']))
				{
					$seat1_arr = array_values($pjSeatModel->where('bus_type_id', $_POST['id'])->findAll()->getDataPair('id', 'id'));
					$seat2_arr = array();
					$sdata = array();
					foreach ($_POST['seats'] as $seat)
					{
						list($id, $sdata['width'], $sdata['height'], $sdata['left'], $sdata['top'], $sdata['name']) = explode("|", $seat);
						$seat2_arr[] = $id;
						$sdata['bus_type_id'] = $_POST['id'];
						$pjSeatModel->reset()->where('id', $id)->limit(1)->modifyAll($sdata);
					}
					$diff = array_diff($seat1_arr, $seat2_arr);
					if (count($diff) > 0)
					{
						$pjSeatModel->reset()->whereIn('id', $diff)->eraseAll();
					}
				}
				if (isset($_POST['seats_new']))
				{
					$sdata = array();
					foreach ($_POST['seats_new'] as $seat)
					{
						list(, $sdata['width'], $sdata['height'], $sdata['left'], $sdata['top'], $sdata['name']) = explode("|", $seat);
						$sdata['bus_type_id'] = $_POST['id'];
						$pjSeatModel->reset()->setAttributes($sdata)->insert();
					}
				}
				
				if(!isset($_POST['seats']) && !isset($_POST['seats_new']) && isset($_POST['seats_count']))
				{
					$cnt_seats = $pjSeatModel->reset()->where('bus_type_id', $_POST['id'])->findCount()->getData();
					if($_POST['seats_count'] > $cnt_seats)
					{
						for($i = $cnt_seats + 1; $i <= $_POST['seats_count']; $i++)
						{
							$sdata = array();
							$sdata['bus_type_id'] = $_POST['id'];
							$sdata['name'] = $i;
							$pjSeatModel->reset()->setAttributes($sdata)->insert();
						}
					}else if($_POST['seats_count'] < $cnt_seats){
						$pjSeatModel->where('bus_type_id', $_POST['id'])->where("(name > ".$_POST['seats_count']." AND name <= $cnt_seats)")->eraseAll();
					}
				}
				
				$cnt_seats = $pjSeatModel->reset()->where('bus_type_id', $_POST['id'])->findCount()->getData();
				if($cnt_seats > 0)
				{
					$data['seats_count'] = $cnt_seats;
				}
				
				$pjBusTypeModel->reset()->where('id', $_POST['id'])->limit(1)->modifyAll(array_merge($_POST, $data));
				
				if (isset($_POST['i18n']))
				{
					pjMultiLangModel::factory()->updateMultiLang($_POST['i18n'], $_POST['id'], 'pjBusType', 'data');
				}
								
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionUpdate&id=".$_POST['id']."&err=ABT01");
				
			} else {
				$pjMultiLangModel = pjMultiLangModel::factory();
				
				$arr = pjBusTypeModel::factory()->find($_GET['id'])->getData();
				
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBusTypes&action=pjActionIndex&err=ABT08");
				}
				$arr['i18n'] = $pjMultiLangModel->getMultiLang($arr['id'], 'pjBusType');
				
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
				$this->appendJs('pjAdminBusTypes.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionDeleteMap()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $pjBusTypeModel = pjBusTypeModel::factory();
                $arr = $pjBusTypeModel->find($_POST['id'])->getData(); 
                
                if(!empty($arr))
                {
                    $map_path = $arr['seats_map'];
                    if (file_exists(PJ_INSTALL_PATH . $map_path)) {
                        @unlink(PJ_INSTALL_PATH . $map_path);
                    }
                    $data = array();
                    $data['seats_map'] = ':NULL';
                    $pjBusTypeModel->reset()->where(array('id' => $_POST['id']))->limit(1)->modifyAll($data);
                    pjSeatModel::factory()->where('bus_type_id', $_POST['id'])->eraseAll();
                    
                    $this->set('code', 200);
                }else{
                    $this->set('code', 100);
                }
            }
        }
        exit;
	}
}
?>