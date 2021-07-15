<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminUsers extends pjAdmin
{
	public function pjActionCheckEmail()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (!isset($_GET['email']) || empty($_GET['email']))
			{
				echo 'false';
				exit;
			}
			$pjUserModel = pjUserModel::factory()->where('t1.email', $_GET['email']);
			if (isset($_GET['id']) && (int) $_GET['id'] > 0)
			{
				$pjUserModel->where('t1.id !=', $_GET['id']);
			}
			echo $pjUserModel->findCount()->getData() == 0 ? 'true' : 'false';
		}
		exit;
	}
	
	public function pjActionCloneUser()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_POST['record']) && count($_POST['record']) > 0)
			{
				$MultiLangModel = new pjMultiLangModel();

				$data = pjUserModel::factory()->whereIn('id', $_POST['record'])->findAll()->getData();
				foreach ($data as $item)
				{
					$item_id = $item['id'];
					unset($item['id']);
					unset($item['email']);

					$id = pjUserModel::factory($item)->insert()->getInsertId();
					if ($id !== false && (int) $id > 0)
					{
						$_data = pjMultiLangModel::factory()->getMultiLang($item_id, 'pjUser');
						$MultiLangModel->saveMultiLang($_data, $id, 'pjUser');
					}
				}
			}
		}
		exit;
	}
	
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent())
		{
			if (isset($_POST['user_create']))
			{
				$data = array();
                $data['is_active'] = 'T';
                $data['status'] = $_POST['status'];
                $data['ip'] = $_SERVER['REMOTE_ADDR'];
                $data['added_by'] = $this->getUserId();

                // if ($_POST['role_id'] == 2) {
                //     $_POST['status'] = 'F';
                // }

				$id = pjUserModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
				if ($id !== false && (int) $id > 0)
				{
                    if ($_POST['role_id'] == 2) {
                        //if ($_POST['commission_percent']) {
                            $item['user_id'] = $id;
                            $item['commission_percent'] = $_POST['commission_percent'];
                            $item['added_by'] = $this->getUserId();
                            $item['created'] = date("Y-m-d H:i:s");
                            $result = pjCreditModel::factory($item)->insert()->getInsertId();
                        //}

                        $length = 17;
                        $code = substr(str_shuffle(md5(microtime().$id)),0,$length);

                        $district = pjDistrictModel::factory()->find($_POST['district'])->getData();
                        $agent_last_code = pjSettingsModel::factory()->find(1)->getData();
                        $agent_last_code = $agent_last_code['st_value'] + 1;
                        if (strlen($agent_last_code) < 5) {
                            $remaining = 5 - strlen($agent_last_code);
                            $agent_code = str_repeat(0, $remaining).$agent_last_code;
                        } else {
                            $agent_code = $agent_last_code;
                        }
                        $agent_code = 'M'.$agent_code;

                        $result = pjUserModel::factory()->where('id', $id)->modifyAll(array(
                            'confirm_code' => $code,
                            'user_code' => $agent_code,
                            //'status' => 'F'
                        ));

                        if ($result) {
                            pjSettingsModel::factory()->where('st_id', 1)->modifyAll(array(
                                'st_value' => $agent_last_code
                            ));

                            $option_arr = $this->option_arr;

                            $Email = new pjEmail();
                            if ($option_arr['o_send_email'] == 'smtp')
                            {
                                $Email
                                    ->setTransport('smtp')
                                    ->setSmtpHost($option_arr['o_smtp_host'])
                                    ->setSmtpPort($option_arr['o_smtp_port'])
                                    ->setSmtpUser($option_arr['o_smtp_user'])
                                    ->setSmtpPass($option_arr['o_smtp_pass'])
                                    ->setSender($option_arr['o_smtp_user'])
                                ;
                            }
                            $Email->setContentType('text/html');

                            $link = PJ_INSTALL_URL.'index.php?controller=pjFrontEnd&action=pjActionConfirmAgentEmail&code='.$code;

                            $opt_array = pjOptionModel::factory()
                            ->where("`key` = 'o_agent_email_confirm_message'")
                            ->findAll()
                            ->getData();

                            if ($opt_array[0]['value']) {
                                $message = str_replace (array('{Agent_Password}','{Agent_Link}'), array($_POST['password'],$link), preg_replace("/\n/","<br>",$opt_array[0]['value']));

                                $super_admin_email = $this->getAdminEmail();
                                $from_email = $super_admin_email;
                                if(!empty($option_arr['o_sender_email']))
                                {
                                    $from_email = $option_arr['o_sender_email'];
                                }
        
                                $subject = 'Active your Agent Account';

                                $Email
                                ->setTo($_POST['email'])
                                ->setFrom($from_email)
                                ->setSubject($subject)
                                ->send($message);
                            }
                        }                       
                    }
                    if ($_POST['role_id'] == 3 || ($_POST['role_id'] == 2 && $_POST['agent_type'] == 2)) {
                        $Email = new pjEmail();
                        if ($option_arr['o_send_email'] == 'smtp')
                        {
                            $Email
                                ->setTransport('smtp')
                                ->setSmtpHost($option_arr['o_smtp_host'])
                                ->setSmtpPort($option_arr['o_smtp_port'])
                                ->setSmtpUser($option_arr['o_smtp_user'])
                                ->setSmtpPass($option_arr['o_smtp_pass'])
                                ->setSender($option_arr['o_smtp_user'])
                            ;
                        }
                        $Email->setContentType('text/html');

                        $email = $_POST['email'];
                        $phone = $_POST['phone'];
        
                        $new_password = '';
                        for($i = 0; $i < 6; $i++) {
                            $new_password .= mt_rand(0, 9);
                        }
        
                        $result = pjUserModel::factory()->where('id', $id)->modifyAll(array(
                            'credit_password' => $new_password
                        ));
        
                        $text = 'Your Current Credit Password with '.PJ_SITE_NAME.' is '.$new_password;
                        $message = pjUtil::textToHtml($text);
                        $subject = 'Current Credit Password';
        
                        $from_email = $this->getAdminEmail();
                        if(!empty($option_arr['o_sender_email'])) {
                            $from_email = $option_arr['o_sender_email'];
                        }
        
                        $is_sent = false;
                        if ($result) {
                            if ($email) {
                                $result = $Email
                                    ->setTo($email)
                                    ->setFrom($from_email)
                                    ->setSubject($subject)
                                    ->send($message);
                                    
                                $is_sent = true;
                            }
        
                            if ($phone) {
                                $params = array(
                                    'text' => $text,
                                    'type' => 'unicode',
                                    'key' => md5($option_arr['private_key'] . PJ_SALT),
                                    'number' => $phone
                                );
        
                                $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
        
                                $is_sent = true;
                            }
                        }
                    }
					$err = 'AU03';
				} else {
					$err = 'AU04';
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminUsers&action=pjActionIndex&err=$err");
			} else {
                $this->set('role_arr', pjRoleModel::factory()->orderBy('t1.id ASC')->findAll()->getData());
                $this->set('district_arr', pjDistrictModel::factory()->orderBy('t1.dt_name ASC')->findAll()->getData());
                $this->set('agent_type_arr', pjAgentTypeModel::factory()->orderBy('t1.at_id ASC')->findAll()->getData());
		
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminUsers.js');
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionDeleteUser()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent())
		{
            if ($this->isXHR())
            {
                $response = array();
                if ($_GET['id'] != $this->getUserId() && $_GET['id'] != 1)
                {
                    $pjUserModel = pjUserModel::factory();
                    $pjUserModel->where('t1.id =',$_GET['id']);

                    if ($this->isBusAdmin()) {
                        $pjUserModel->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')) OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 1)))');
                    }
                    if ($this->isEditor()) {
                        $pjUserModel->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                    }
                    $arr1 = $pjUserModel->findAll()->getData();
                    $arr = (count($arr1)) ? $arr1[0] : array();
                
                    if (count($arr) && pjUserModel::factory()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
                    {
                        $response['code'] = 200;
                    } else {
                        $response['code'] = 100;
                    }
                } else {
                    $response['code'] = 100;
                }
                pjAppController::jsonResponse($response);
            }
        }
		exit;
	}
	
	public function pjActionDeleteUserBulk()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent())
		{
            if ($this->isXHR())
            {
                if (isset($_POST['record']) && count($_POST['record']) > 0)
                {
                    pjUserModel::factory()
                        ->where('id !=', $this->getUserId())
                        ->where('id !=', 1)
                        ->whereIn('id', $_POST['record'])->eraseAll();
                }
            }
        }
		exit;
	}
	
	public function pjActionExportUser()
	{
		$this->checkLogin();
        
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if (isset($_POST['record']) && is_array($_POST['record']))
            {
                $arr = pjUserModel::factory()->whereIn('id', $_POST['record'])->findAll()->getData();
                $csv = new pjCSV();
                $csv
                    ->setHeader(true)
                    ->setName("Users-".time().".csv")
                    ->process($arr)
                    ->download();
            }
        }
		exit;
	}
	
	public function pjActionGetUser()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent() || $this->isInvestor())
		{
            if ($this->isXHR())
            {
                $pjUserModel = pjUserModel::factory();
                
                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    // $pjUserModel->where('t1.email LIKE', "%$q%");
                    // $pjUserModel->orWhere('t1.name LIKE', "%$q%");
                    $pjUserModel->where("(t1.email LIKE '%$q%' OR t1.name LIKE' %$q%')");  
                }

                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
                {
                    $pjUserModel->where('t1.status', $_GET['status']);
                }                

                if ($this->isBusAdmin()) {
                    $pjUserModel->where('(id = '.$this->getUserId().' OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')) OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	
                }

                if ($this->isSuperAgent()) {
                    $pjUserModel->where('(id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }
                    
                $column = 'name';
                $direction = 'ASC';
                if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
                {
                    $column = $_GET['column'];
                    $direction = strtoupper($_GET['direction']);
                }

                $total = $pjUserModel->findCount()->getData();
                $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                $pages = ceil($total / $rowCount);
                $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                $offset = ((int) $page - 1) * $rowCount;
                if ($page > $pages)
                {
                    $page = $pages;
                }

                $data = array();
                
                $pjUserModel = pjUserModel::factory();
                
                $data_object = $pjUserModel->select('t1.id, t1.email, t1.name, DATE(t1.created) AS created, t1.status, t1.is_active, t1.role_id, t1.agent_type, t2.role, t3.at_type')
                    ->join('pjRole', 't2.id=t1.role_id', 'left')
                    ->join('pjAgentType', 't3.at_id=t1.agent_type', 'left');

                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    // $pjUserModel->where('t1.email LIKE', "%$q%");
                    // $pjUserModel->orWhere('t1.name LIKE', "%$q%");
                    $data_object->where("(t1.email LIKE '%$q%' OR t1.name LIKE '%$q%')"); 
                }

                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
                {
                    $data_object->where('t1.status', $_GET['status']);
                }                

                if ($this->isBusAdmin()) {
                    $data_object->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))  OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                }

                if ($this->isSuperAgent()) {
                    $data_object->where('(t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }
                
                $data = $data_object->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
        
                pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
            }
        }
		exit;
	}
	
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent() || $this->isInvestor())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminUsers.js?'.PJ_CSS_JS_VERSION);
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSetActive()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			$pjUserModel = pjUserModel::factory();
			
			$arr = $pjUserModel->find($_POST['id'])->getData();
			
			if (count($arr) > 0)
			{
				switch ($arr['is_active'])
				{
					case 'T':
						$sql_status = 'F';
						break;
					case 'F':
						$sql_status = 'T';
						break;
					default:
						return;
				}
				$pjUserModel->reset()->setAttributes(array('id' => $_POST['id']))->modify(array('is_active' => $sql_status));
			}
		}
		exit;
	}
	
	public function pjActionSaveUser()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjUserModel = pjUserModel::factory();
			
			$pass = true;
			if ((int) $_GET['id'] === 1 && in_array($_POST['column'], array('role_id', 'status', 'is_active')))
			{
				$pass = false;
			}
			if ($pass)
			{
				$pjUserModel->where('id', $_GET['id'])->limit(1)->modifyAll(array($_POST['column'] => $_POST['value']));
			}
		}
		exit;
	}
	
	public function pjActionStatusUser()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_POST['record']) && count($_POST['record']) > 0)
			{
				pjUserModel::factory()->whereIn('id', $_POST['record'])->modifyAll(array(
					'status' => ":IF(`status`='F','T','F')"
				));
			}
		}
		exit;
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();

		if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent())
		{
            $pjUserModel = pjUserModel::factory();
				
			if (isset($_POST['user_update']))
			{
                $pjUserModel->where('t1.id =',$_POST['id'])
                ->select ('t1.*,t2.*')
				->join('pjCredit', 't2.user_id = t1.id', 'left outer');

                if ($this->isBusAdmin()) {
                    $pjUserModel->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))  OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                }
                if ($this->isSuperAgent()) {
                    $pjUserModel->where('(t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }

                $arr1 = $pjUserModel->findAll()->getData();

                $arr = (count($arr1)) ? $arr1[0] : array();

				if (count($arr) === 0)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminUsers&action=pjActionIndex&err=AU08");
                }
                
                $data['agent_type'] = ($_POST['role_id'] == 2) ? $_POST['agent_type'] : 0;

                if (!$_POST['password']) {
                    unset($_POST['password']);
                }

                if ($arr['role_id'] != $_POST['role_id'] && $_POST['role_id'] == 2) {
                    $length = 17;
                    $code = substr(str_shuffle(md5(microtime().$_POST['id'])),0,$length);

                    $district = pjDistrictModel::factory()->find($_POST['district'])->getData();
                    $agent_code = $district['dt_code'].$_POST['id'];

                    $agent_last_code = pjSettingsModel::factory()->find(1)->getData();
                    $agent_last_code = $agent_last_code['st_value'] + 1;
                    if (strlen($agent_last_code) < 5) {
                        $remaining = 5 - strlen($agent_last_code);
                        $agent_code = str_repeat(0, $remaining).$agent_last_code;
                    } else {
                        $agent_code = $agent_last_code;
                    }
                    $agent_code = 'M'.$agent_code;

                    $data['user_code'] = $agent_code;
                }

				$result_modify = pjUserModel::factory()->where('id', $_POST['id'])->limit(1)->modifyAll(array_merge($_POST, $data));

                if ($result_modify && $arr['role_id'] != $_POST['role_id'] && $_POST['role_id'] == 2) {
                    pjSettingsModel::factory()->where('st_id', 1)->modifyAll(array(
                        'st_value' => $agent_last_code
                    ));
                }

                $result = pjCreditModel::factory()->whereIn('user_id', $_POST['id'])->modifyAll(array(
                    'commission_percent' => ($_POST['commission_percent']) ? $_POST['commission_percent'] : 0
                ));

                if ($_POST['role_id'] == 2) {
                    $pjCreditModel = pjCreditModel::factory()->where('t1.user_id', $_POST['id']);				
                    $count =  $pjCreditModel->findCount()->getData();

                    if ($count) {
                        $result = pjCreditModel::factory()->whereIn('user_id', $_POST['id'])->modifyAll(array(
                            'commission_percent' => ($_POST['commission_percent']) ? $_POST['commission_percent'] : 0
                        ));
                    } else {
                        $item['user_id'] = $_POST['id'];
                        $item['commission_percent'] = ($_POST['commission_percent']) ? $_POST['commission_percent'] : 0;
                        $item['added_by'] = $this->getUserId();
                        $item['created'] = date("Y-m-d H:i:s");

                        $result = pjCreditModel::factory($item)->insert()->getInsertId();
                    }
                }

				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjAdminUsers&action=pjActionIndex&err=AU01");
				
			} else {
                $pjUserModel->where('t1.id =',$_GET['id'])
                ->select ('t1.*,t2.user_id,t2.credit,t2.commission_percent')
				->join('pjCredit', 't2.user_id = t1.id', 'left outer');

                if ($this->isBusAdmin()) {                    
                    $pjUserModel->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))  OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                }
                if ($this->isSuperAgent()) {
                    $pjUserModel->where('(t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }

                $arr1 = $pjUserModel->findAll()->getData();
                
                $arr = (count($arr1)) ? $arr1[0] : array();

				if (count($arr) === 0)
				{
					pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminUsers&action=pjActionIndex&err=AU08");
				}
				$this->set('arr', $arr);
				
				$this->set('role_arr', pjRoleModel::factory()->orderBy('t1.id ASC')->findAll()->getData());
                $this->set('district_arr', pjDistrictModel::factory()->orderBy('t1.dt_name ASC')->findAll()->getData());
                $this->set('agent_type_arr', pjAgentTypeModel::factory()->orderBy('t1.at_id ASC')->findAll()->getData());
                
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('pjAdminUsers.js');
                $this->appendJs('jquery.easy-autocomplete.js');
                $this->appendCss('easy-autocomplete.css');
                $this->appendCss('easy-autocomplete.themes.css');
			}
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionCredit()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin()) {
            $pjUserModel = pjUserModel::factory();            

			$arr_obj = $pjUserModel->select ('t1.*,t2.credit,t2.created,t3.name AS last_added_by')
            ->join('pjCredit', 't2.user_id = t1.id', 'left outer')
            ->join('pjUser', 't3.id = t2.added_by', 'left outer')
            ->where('t1.id = '.$_GET['id']);
            
            if ($this->isBusAdmin()) {
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');  
            }

            $arr1 = $arr_obj->findAll()->getData();
            $arr = (count($arr1)) ? $arr1[0] : array();

			if (count($arr) === 0)
			{
				pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminUsers&action=pjActionIndex&err=AU08");
            }
			$this->set('arr', $arr);			
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdminUsers.js');
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionCreditUpdate() {
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin()) {
            $user_id = $_POST['user_id'];
            $credit = $_POST['credit'];

            $pjUserModel = pjUserModel::factory();            

            $arr_obj = $pjUserModel->select ('t1.*,t2.credit')
            ->join('pjCredit', 't2.user_id = t1.id', 'left outer')
            ->where('t1.id = '.$user_id);
            
            if ($this->isBusAdmin()) {
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');  
            }

            $arr1 = $arr_obj->findAll()->getData();
            $arr = (count($arr1)) ? $arr1[0] : array();

            if (count($arr) === 0)
            {
                pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminUsers&action=pjActionIndex&err=AU08");
            } else {
                $user = pjUserModel::factory()
                    ->where('t1.id', $this->getUserId())
                    ->where(sprintf("t1.credit_password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($_POST['credit_password']), PJ_SALT))
                    ->limit(1)
                    ->findAll()
                    ->getData();

                    if (count($user) != 1) {
                        pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminUsers&action=pjActionCredit&id=".$user_id."&&err=ACU01");
                    }
            }
        
            if ($user_id) {
                $pjCreditModel = pjCreditModel::factory()->where('t1.user_id', $user_id);				
                $count =  $pjCreditModel->findCount()->getData();

                if ($count) {
                    $result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
                        'credit' => $credit,
                        'added_by' => $this->getUserId(),
                        'created' => date("Y-m-d H:i:s")
                    ));
                } else {
                    $item['user_id'] = $user_id;
                    $item['credit'] = $credit;

                    $result = pjCreditModel::factory($item)->insert()->getInsertId();	
                }
                if ($result) {
                    $added_credit = $credit - $arr['credit'];
                    $Email = new pjEmail();
                    $option_arr = $this->option_arr;
                    if ($option_arr['o_send_email'] == 'smtp')
                    {
                        $Email
                            ->setTransport('smtp')
                            ->setSmtpHost($option_arr['o_smtp_host'])
                            ->setSmtpPort($option_arr['o_smtp_port'])
                            ->setSmtpUser($option_arr['o_smtp_user'])
                            ->setSmtpPass($option_arr['o_smtp_pass'])
                            ->setSender($option_arr['o_smtp_user'])
                        ;
                    }
                    $Email->setContentType('text/html');

                    $opt_array = pjOptionModel::factory()
                    ->where("`key` = 'o_agent_credit_message'")
                    ->findAll()
                    ->getData();                    

                    if ($opt_array[0]['value']) {
                        $super_admin_email = $this->getAdminEmail();
                        $from_email = $super_admin_email;
                        if(!empty($option_arr['o_sender_email']))
                        {
                            $from_email = $option_arr['o_sender_email'];
                        }

                        $subject = 'Added credit to your account';
                        $message = str_replace (array('{Agent_Name}','{Credit}','{New_Credit}'), array($arr['name'],$added_credit,$credit), preg_replace("/\n/","<br>",$opt_array[0]['value']));

                        $Email
                        ->setTo($arr['email'])
                        ->setFrom($from_email)
                        ->setSubject($subject)
                        ->send($message);
                    }

                    if ($arr['phone']) {
                        $message = str_replace (array('{Agent_Name}','{Credit}','{New_Credit}'), array($arr['name'],$added_credit,$credit), $opt_array[0]['value']);

                        $params = array(
                            'text' => $message,
                            'type' => 'unicode',
                            'key' => md5($option_arr['private_key'] . PJ_SALT),
                            'number' => $arr['phone']
                        );
                   
                        $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
                    }
                
                    pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminUsers&action=pjActionIndex");
                } else {
                    pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminUsers&action=pjActionCredit&id=".$user_id."&&err=ACU02");
                }
            } else {
                pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminUsers&action=pjActionIndex");
            }
        }
		exit;
    }

    public function pjActionSubAgents()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
            $_SESSION['selected_super_agent'] = $super_agent = $_GET['id'];
            $super_agent_detail = pjUserModel::factory()->find($super_agent)->getData();

            $this->set('super_agent_detail', $super_agent_detail);

			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminSubAgents.js?'.PJ_CSS_JS_VERSION);
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
    
    public function pjActionGetSubAgent()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $pjUserModel = pjUserModel::factory();

                $super_agent = $_SESSION['selected_super_agent'];                
                
                $pjUserModel->where("added_by",$super_agent);
                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    // $pjUserModel->where('t1.email LIKE', "%$q%");
                    // $pjUserModel->orWhere('t1.name LIKE', "%$q%");
                    $pjUserModel->where("(t1.email LIKE '%$q%' OR t1.name LIKE' %$q%')");  
                }

                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
                {
                    $pjUserModel->where('t1.status', $_GET['status']);
                }
                    
                $column = 'name';
                $direction = 'ASC';
                if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
                {
                    $column = $_GET['column'];
                    $direction = strtoupper($_GET['direction']);
                }

                $total = $pjUserModel->findCount()->getData();
                $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                $pages = ceil($total / $rowCount);
                $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                $offset = ((int) $page - 1) * $rowCount;
                if ($page > $pages)
                {
                    $page = $pages;
                }

                $data = array();
                
                $pjUserModel = pjUserModel::factory();
                
                $pjUserModel->where("added_by",$super_agent);
                $data_object = $pjUserModel->select('t1.id, t1.email, t1.name, DATE(t1.created) AS created, t1.status, t1.is_active, t1.role_id, t2.role, t3.at_type')
                    ->join('pjRole', 't2.id=t1.role_id', 'left')
                    ->join('pjAgentType', 't3.at_id=t1.agent_type', 'left');

                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    // $pjUserModel->where('t1.email LIKE', "%$q%");
                    // $pjUserModel->orWhere('t1.name LIKE', "%$q%");
                    $data_object->where("(t1.email LIKE '%$q%' OR t1.name LIKE '%$q%')"); 
                }

                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
                {
                    $data_object->where('t1.status', $_GET['status']);
                }  
                
                $data = $data_object->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
        
                pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
            }
        }
		exit;
	}	
}
?>