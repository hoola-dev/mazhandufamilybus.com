<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminFAQ extends pjAdmin
{
    public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminFAQ.js?'.PJ_CSS_JS_VERSION);
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
    }	
    
	public function pjActionGetFAQ()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $pjFAQModel = pjFAQModel::factory();    
                    
                $column = 'fq_title';
                $direction = 'ASC';
                if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
                {
                    $column = $_GET['column'];
                    $direction = strtoupper($_GET['direction']);
                }

                $total = $pjFAQModel->findCount()->getData();
                $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                $pages = ceil($total / $rowCount);
                $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                $offset = ((int) $page - 1) * $rowCount;
                if ($page > $pages)
                {
                    $page = $pages;
                }

                $data = array();
                
                $pjFAQModel = pjFAQModel::factory(); 
                
                $data_object = $pjFAQModel->select('t1.*');                    

                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    $data_object->where("(t1.fq_title LIKE '%$q%' OR t1.fq_description LIKE' %$q%')"); 
                }

                if (isset($_GET['status']) && in_array($_GET['status'], array('1', '0')))
                {
                    $data_object->where('t1.fq_is_active', $_GET['status']);
                }                
                
                $data = $data_object->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->debug('true')->getData();
                if ($data) {
                    foreach ($data as $k=>$v) {                        
                        $data[$k]['fq_is_active'] = ($v['fq_is_active'] == 1) ? 'Yes' : 'No';
                        $data[$k]['fq_description'] = preg_replace('/\n/','<br>',$v['fq_description']);
                    }
                }
                pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
            }
        }
		exit;
    }
    
    public function pjActionCreate()
	{
        $this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin()) {   			
            $this->appendJs('pjAdminFAQ.js?'.PJ_CSS_JS_VERSION);
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionAdd() {
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin()) {
            $fq_title = $_POST['fq_title'];
            $fq_description = $_POST['fq_description'];
            $fq_is_active = $_POST['fq_is_active'];  

            $pjFAQModel = pjFAQModel::factory()->where('t1.fq_is_active', 1);				
            $count =  $pjFAQModel->findCount()->getData();            

             if (!$fq_title || !$fq_description) {
                $data['success'] = 0;
                $data['message'] = 'Please enter Title and Description';
            } else {
                $item_sa['fq_title'] = $fq_title;                
                $item_sa['fq_description'] = $fq_description;                
                $item_sa['fq_is_active'] = $fq_is_active;                
                $item_sa['fq_added_by'] = $this->getUserId();
                $item_sa['fq_added_on'] = date("Y-m-d H:i:s");

                $result = pjFAQModel::factory($item_sa)->insert();
                if ($result) {
                    $data['success'] = 1;
                    $data['message'] = 'FAQ added';
                } else {
                    $data['success'] = 0;
                    $data['message'] = 'Sorry, some technical problem occured';
                }
            }
            echo json_encode($data);
        }
		exit;
    }

    public function pjActionUpdate()
	{
        $this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin()) {  
            $id = $_GET['id'];
            $faq = pjFAQModel::factory()->where('fq_id',$id)->findAll()->getData();	
            if (count($faq) === 0) {
				pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminFAQ&action=pjActionIndex");
            } else {
                $this->set('faq', $faq[0]);
                $this->appendJs('pjAdminFAQ.js?'.PJ_CSS_JS_VERSION);
            }
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionEdit() {
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin()) {
            $fq_id = $_POST['fq_id'];
            $fq_title = $_POST['fq_title'];            
            $fq_description = $_POST['fq_description'];
            $fq_is_active = $_POST['fq_is_active'];  

            $faq = pjFAQModel::factory()->where('fq_id',$fq_id)->findAll()->getData();

            $pjFAQModel = pjFAQModel::factory()->where('t1.fq_is_active', 1)->where('t1.fq_id !=',$fq_id);				
            $count =  $pjFAQModel->findCount()->getData();

             if (!$fq_title || !$fq_description) {
                $data['success'] = 0;
                $data['message'] = 'Please enter Title and Description';
            } else {
                $result = pjFAQModel::factory()->whereIn('fq_id', $fq_id)->modifyAll(array(
                    'fq_title' => $fq_title,                    
                    'fq_description' => $fq_description,                    
                    'fq_is_active' => $fq_is_active
                ));
                if ($result) {
                    $data['success'] = 1;
                    $data['message'] = 'FAQ edited';
                } else {
                    $data['success'] = 0;
                    $data['message'] = 'Sorry, some technical problem occured';
                }
            }
            echo json_encode($data);
        }
		exit;
    }

    public function pjActionDeleteFAQ()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $response = array();
                if ($_GET['id'] != $this->getUserId() && $_GET['id'] != 1)
                {
                    $pjFAQModel = pjFAQModel::factory();
                    $pjFAQModel->where('t1.fq_id =',$_GET['id']);
                    
                    $arr1 = $pjFAQModel->findAll()->getData();
                    $arr = (count($arr1)) ? $arr1[0] : array();
                
                    if (count($arr) && pjFAQModel::factory()->setAttributes(array('fq_id' => $_GET['id']))->erase()->getAffectedRows() == 1)
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
}
?>