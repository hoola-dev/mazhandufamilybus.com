<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminAgents extends pjAdmin
{
    public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent() || $this->isInvestor())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminAgents.js?'.PJ_CSS_JS_VERSION);
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
    }	
    
	public function pjActionGetAgent()
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
                    $pjUserModel->where('(id = '.$this->getUserId().' OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }

                $pjUserModel->where('role_id',2);
                    
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
                
                $data_object = $pjUserModel->select('t1.id, t1.email, t1.name, t1.user_code, DATE(t1.created) AS created, t1.status, t1.is_active, t1.role_id, t2.role, t3.credit, t3.commission_percent, t3.total_commission')
                    ->join('pjRole', 't2.id=t1.role_id', 'left')
                    ->join('pjCredit', 't3.user_id=t1.id', 'left outer');

                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    // $pjUserModel->where('t1.email LIKE', "%$q%");
                    // $pjUserModel->orWhere('t1.name LIKE', "%$q%");
                    $data_object->where("(t1.email LIKE '%$q%' OR t1.name LIKE' %$q%')"); 
                }

                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
                {
                    $data_object->where('t1.status', $_GET['status']);
                }                
                    
                if ($this->isBusAdmin()) {
                    $data_object->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))  OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                }

                if ($this->isSuperAgent()) {
                    $data_object->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }

                $data_object->where('role_id',2);
                
                $data = $data_object->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
        
                pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
            }
        }
		exit;
    }
    
    public function pjActionCredit()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || ($this->isSuperAgent() && $this->getUserId() != $_GET['id'])) {
            $pjUserModel = pjUserModel::factory();            

			$arr_obj = $pjUserModel->select ('t1.*,t2.credit,t2.created,t2.commission_percent,t2.total_commission,t3.name AS last_added_by')
            ->join('pjCredit', 't2.user_id = t1.id', 'left outer')
            ->join('pjUser', 't3.id = t2.added_by', 'left outer')
            ->where('t1.id = '.$_GET['id']);

            if ($this->isBusAdmin()) {                
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))  OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
            }

            if ($this->isSuperAgent()) {
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
            }

            $arr1 = $arr_obj->findAll()->getData();
            $arr = (count($arr1)) ? $arr1[0] : array();

			if (count($arr) === 0)
			{
				pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminAgents&action=pjActionIndex&err=AU08");
            }
			$this->set('arr', $arr);			
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
            $this->appendJs('pjAdminAgents.js?'.PJ_CSS_JS_VERSION);
            $this->appendJs('pjAdmin.js?'.PJ_CSS_JS_VERSION);
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionCreditUpdate() {
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent()) {
            $user_id = $_POST['user_id'];
            $credit = $_POST['credit'];            

            $pjUserModel = pjUserModel::factory();            

            $arr_obj = $pjUserModel->select ('t1.*,t2.credit,t2.commission_percent,t2.total_commission')
            ->join('pjCredit', 't2.user_id = t1.id', 'left outer')
            ->where('t1.id = '.$user_id);            
            
            if ($this->isBusAdmin()) {                
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))  OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
            }

            if ($this->isSuperAgent()) {
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
            }

            $arr1 = $arr_obj->findAll()->getData();
            $arr = (count($arr1)) ? $arr1[0] : array();
            
            if (count($arr) === 0)
            {
                pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminAgents&action=pjActionIndex&err=AU08");
            } else {
                $user = pjUserModel::factory()
                    ->where('t1.id', $this->getUserId())
                    ->where(sprintf("t1.credit_password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($_POST['credit_password']), PJ_SALT))
                    ->limit(1)
                    ->findAll()
                    ->getData();

                    if (count($user) != 1) {
                        pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminAgents&action=pjActionCredit&id=".$user_id."&&err=ACU01");
                    }
            }
        
            if ($user_id) {
                $commission_percent = $arr['commission_percent'];

                $pjCreditModel = pjCreditModel::factory()->where('t1.user_id', $user_id);				
                $count =  $pjCreditModel->findCount()->getData();

                $current_credit = (isset($arr['credit'])) ? $arr['credit'] : 0;
                $current_total_commission = (isset($arr['total_commission'])) ? $arr['total_commission'] : 0;
                
                $added_credit = $credit;
                $new_credit = $current_credit + $credit;

                if ($added_credit > 0) {
                    $commission = $added_credit * ($commission_percent/100);
                } else {
                    $commission = 0;
                }
                $total_commission = $current_total_commission + $commission;
                $credit_with_commission = $new_credit + $commission;                

                if ($this->isSuperAgent()) {
                    $super_agent_id = $this->getUserId();

                    $arr_obj_sa = pjCreditModel::factory()
                    ->where('user_id = '.$super_agent_id)
                    ->findAll()
                    ->getData();

                    $super_agent_credit = (count($arr_obj_sa)) ? $arr_obj_sa[0]['credit'] : 0;
                    
                    if ($added_credit > 0 && $super_agent_credit < $credit_with_commission) {
                        pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminAgents&action=pjActionCredit&id=".$user_id."&&err=ACU06");    
                    }
                }                

                if ($count) {
                    $result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
                        'credit' => $credit_with_commission,
                        'total_commission' => $total_commission,
                        'added_by' => $this->getUserId(),
                        'created' => date("Y-m-d H:i:s")
                    ));
                } else {
                    $item['user_id'] = $user_id;
                    $item['credit'] = $credit_with_commission;
                    $item['total_commission'] = $total_commission;
                    $item['added_by'] = $this->getUserId();
                    $item['created'] = date("Y-m-d H:i:s");

                    $result = pjCreditModel::factory($item)->insert()->getInsertId();	
                }
                if ($result) {    
                    if ($this->isSuperAgent()) {                        

                        if ($added_credit > 0) {
                            $super_agent_new_credit = $super_agent_credit - ($added_credit+$commission);
                        } else {
                            $super_agent_new_credit = $super_agent_credit + ($added_credit * -1);
                        }

                        //update super-agent credit
                        $result = pjCreditModel::factory()->whereIn('user_id', $super_agent_id)->modifyAll(array(
                            'credit' => $super_agent_new_credit,
                            'created' => date("Y-m-d H:i:s")
                        ));

                        $item_sa['ca_sub_agent_id'] = $user_id;
                        $item_sa['ca_credit_added'] = $added_credit;
                        $item_sa['ca_commission_percent'] = $commission_percent;
                        $item_sa['ca_commission'] = $commission;
                        $item_sa['ca_total_commission'] = $total_commission;
                        $item_sa['ca_added_by'] = $this->getUserId();
                        $item_sa['ca_added_on'] = date("Y-m-d H:i:s");

                        $result = pjCreditAllocationModel::factory($item_sa)->insert();


                        if ($added_credit > 0) {
                            $super_agent_entry = ($added_credit+$commission) * -1;
                        } else {
                            $super_agent_entry = $added_credit * -1;
                        }

                        $item_commission['cm_user_id'] = $super_agent_id;
                        $item_commission['cm_credit_added'] = $super_agent_entry;  
                        $item_commission['cm_commission_percent'] = 0;                      
                        $item_commission['cm_total_commission'] = 0;
                        $item_commission['cm_added_by'] = $this->getUserId();
                        $item_commission['cm_added_on'] = date("Y-m-d H:i:s");

                        $result = pjCommissionModel::factory($item_commission)->insert();
                    }

                    //if ($commission) {
                        $item_commission['cm_user_id'] = $user_id;
                        $item_commission['cm_credit_added'] = $added_credit;
                        $item_commission['cm_commission_percent'] = $commission_percent;
                        $item_commission['cm_commission'] = $commission;
                        $item_commission['cm_total_commission'] = $total_commission;
                        $item_commission['cm_added_by'] = $this->getUserId();
                        $item_commission['cm_added_on'] = date("Y-m-d H:i:s");

                        $result = pjCommissionModel::factory($item_commission)->insert();
                    //}

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
                        $message = str_replace (array('{Agent_Name}','{Credit}','{Commission_Percent}','{Commission}','{New_Credit}'), array($arr['name'],$added_credit,$commission_percent,$commission,$credit_with_commission), preg_replace("/\n/","<br>",$opt_array[0]['value']));

                        $Email
                        ->setTo($arr['email'])
                        ->setFrom($from_email)
                        ->setSubject($subject)
                        ->send($message);
                    }

                    if ($arr['phone']) {
                        $message = str_replace (array('{Agent_Name}','{Credit}','{Commission_Percent}','{Commission}','{New_Credit}'), array($arr['name'],$added_credit,$commission_percent,$commission,$credit_with_commission), $opt_array[0]['value']);

                        $params = array(
                            'text' => $message,
                            'type' => 'unicode',
                            'key' => md5($option_arr['private_key'] . PJ_SALT),
                            'number' => $arr['phone']
                        );
                   
                        $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
                    }
                
                    pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminAgents&action=pjActionIndex");
                } else {
                    pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminAgents&action=pjActionCredit&id=".$user_id."&&err=ACU02");
                }
            } else {
                pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminAgents&action=pjActionIndex");
            }
        }
		exit;
    }
    
    public function pjActionCommission()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor())
		{
            $user_id = $_GET['id'];
            if ($this->isEditor() && !$this->isSuperAgent() && $user_id != $this->getUserId()) {
                $this->set('status', 2);    
            }

            $pjUserModel = pjUserModel::factory(); 

            $arr_obj = $pjUserModel->select ('t1.*,t2.credit,t2.created,t2.commission_percent,t2.total_commission,t3.name AS last_added_by')
            ->join('pjCredit', 't2.user_id = t1.id', 'left outer')
            ->join('pjUser', 't3.id = t2.added_by', 'left outer')
            ->where('t1.id = '.$user_id);

            if ($this->isBusAdmin()) {                
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))  OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
            }

            if ($this->isSuperAgent()) {
                $arr_obj->where('(t1.id = '.$this->getUserId().' OR t1.id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
            }

            $arr1 = $arr_obj->findAll()->getData();
            $arr = (count($arr1)) ? $arr1[0] : array();

			if (count($arr) === 0)
			{
				pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminAgents&action=pjActionIndex&err=AU08");
            }

            $agent_detail = pjUserModel::factory()
                        ->find($user_id)
                        ->getData();

            $agent_name = $agent_detail['name'];
            $agent_code = $agent_detail['user_code'];

            $this->set('user_id', $user_id); 
            $this->set('agent_name', $agent_name); 
            $this->set('agent_code', $agent_code); 

			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminCommission.js?'.PJ_CSS_JS_VERSION);
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
    }	
    
	public function pjActionGetCommission()
	{
        $this->setAjax(true);
        
        if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor())
		{
            if ($this->isXHR())
            {
                $user_id = $_GET['id'];
                if ($this->isEditor() && !$this->isSuperAgent() && $user_id != $this->getUserId()) {
                    exit;   
                } else {
                    $pjUserModel = pjUserModel::factory();

                    $pjCommissionModel = pjCommissionModel::factory();

                    $pjCommissionModel->select('t1.*,t1.cm_added_on,t2.name')
                    ->join('pjUser', 't2.id=t1.cm_added_by', 'left');
                    
                    if (isset($_GET['q']) && !empty($_GET['q']))
                    {
                        $q = pjObject::escapeString($_GET['q']);
                        $pjCommissionModel->where("(t2.name LIKE '%$q%' OR t1.cm_credit_added LIKE '%$q%' OR t1.cm_commission_percent LIKE '%$q%' OR t1.cm_commission LIKE '%$q%' OR t1.cm_total_commission LIKE '%$q%')");  
                    }
                    
                    if ($this->isBusAdmin()) {
                        $pjCommissionModel->where('(cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')) OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                        
                    }

                    if ($this->isSuperAgent()) {
                        $pjCommissionModel->where('(cm_user_id = '.$this->getUserId().' OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                    }

                    $pjCommissionModel->where('cm_user_id',$user_id);
                        
                    $column = 'cm_added_on';
                    $direction = 'DESC';
                    if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
                    {
                        $column = $_GET['column'];
                        $direction = strtoupper($_GET['direction']);
                    }

                    $total = $pjCommissionModel->findCount()->getData();
                    $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                    $pages = ceil($total / $rowCount);
                    $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                    $offset = ((int) $page - 1) * $rowCount;
                    if ($page > $pages)
                    {
                        $page = $pages;
                    }

                    $data = array();
                    
                    $pjCommissionModel = pjCommissionModel::factory();
                    
                    $data_object = $pjCommissionModel->select('t1.*,t1.cm_added_on,t2.name')
                        ->join('pjUser', 't2.id=t1.cm_added_by', 'left');

                    if (isset($_GET['q']) && !empty($_GET['q']))
                    {
                        $q = pjObject::escapeString($_GET['q']);
                        $data_object->where("(t2.name LIKE '%$q%' OR t1.cm_credit_added LIKE '%$q%' OR t1.cm_commission_percent LIKE '%$q%' OR t1.cm_commission LIKE '%$q%' OR t1.cm_total_commission LIKE '%$q%')"); 
                    }                        
                    
                    if ($this->isBusAdmin()) {
                        $data_object->where('(cm_user_id = '.$this->getUserId().' OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')) OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3)))');	
                    }

                    if ($this->isSuperAgent()) {
                        $data_object->where('(cm_user_id = '.$this->getUserId().' OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                    }

                    $data_object->where('cm_user_id',$user_id);
                    
                    $data = $data_object->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
            
                    pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
                }
            }
        }
		exit;
    }

    public function pjActionCreditAllocation()
	{
		$this->checkLogin();
		
		if ($this->isSuperAgent())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminCreditAllocation.js?'.PJ_CSS_JS_VERSION);
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
    }	
    
	public function pjActionGetCreditAllocation()
	{
		$this->setAjax(true);
    
        if ($this->isSuperAgent())
		{
            if ($this->isXHR())
            {
                $pjCreditAllocationModel = pjCreditAllocationModel::factory();

                $pjCreditAllocationModel->join('pjUser', 't2.id=t1.ca_sub_agent_id', 'left');
                
                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    // $pjUserModel->where('t1.email LIKE', "%$q%");
                    // $pjUserModel->orWhere('t1.name LIKE', "%$q%");
                    $pjCreditAllocationModel->where("(t2.email LIKE '%$q%' OR t2.name LIKE' %$q%')");  
                }

                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
                {
                    $pjCreditAllocationModel->where('t2.status', $_GET['status']);
                }

                $pjCreditAllocationModel->where('t1.ca_added_by', $this->getUserId());
                     
                $column = 'ca_added_on';
                $direction = 'DESC';
                if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
                {
                    $column = $_GET['column'];
                    $direction = strtoupper($_GET['direction']);
                }

                $total = $pjCreditAllocationModel->findCount()->getData();
                $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                $pages = ceil($total / $rowCount);
                $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                $offset = ((int) $page - 1) * $rowCount;
                if ($page > $pages)
                {
                    $page = $pages;
                }

                $data = array();
                
                $pjCreditAllocationModel = pjCreditAllocationModel::factory();
                
                $data_object = $pjCreditAllocationModel->select('t1.*, t2.email, t2.name, t2.user_code, DATE(t1.ca_added_on) AS created')
                        ->join('pjUser', 't2.id=t1.ca_sub_agent_id', 'left');

                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    // $pjUserModel->where('t1.email LIKE', "%$q%");
                    // $pjUserModel->orWhere('t1.name LIKE', "%$q%");
                    $data_object->where("(t2.email LIKE '%$q%' OR t2.name LIKE' %$q%')"); 
                }

                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('T', 'F')))
                {
                    $data_object->where('t2.status', $_GET['status']);
                } 
                
                $pjCreditAllocationModel->where('t1.ca_added_by', $this->getUserId());               
                
                $data = $data_object->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
        
                pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
            }
        }
		exit;
    }
}
?>