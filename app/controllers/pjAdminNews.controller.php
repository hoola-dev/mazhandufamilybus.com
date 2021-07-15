<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminNews extends pjAdmin
{
    public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminNews.js?'.PJ_CSS_JS_VERSION);
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
    }	
    
	public function pjActionGetNews()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $pjNewsModel = pjNewsModel::factory();    
                    
                $column = 'nw_date';
                $direction = 'DESC';
                if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
                {
                    $column = $_GET['column'];
                    $direction = strtoupper($_GET['direction']);
                }

                $total = $pjNewsModel->findCount()->getData();
                $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                $pages = ceil($total / $rowCount);
                $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                $offset = ((int) $page - 1) * $rowCount;
                if ($page > $pages)
                {
                    $page = $pages;
                }

                $data = array();
                
                $pjNewsModel = pjNewsModel::factory(); 
                
                $data_object = $pjNewsModel->select('t1.*');                    

                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    $data_object->where("(t1.nw_title LIKE '%$q%' OR t1.nw_link LIKE' %$q%' OR t1.nw_description LIKE' %$q%')"); 
                }

                if (isset($_GET['status']) && in_array($_GET['status'], array('1', '0')))
                {
                    $data_object->where('t1.nw_is_active', $_GET['status']);
                }                
                
                $data = $data_object->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->debug('true')->getData();
                if ($data) {
                    foreach ($data as $k=>$v) {
                        $data[$k]['nw_image'] = '<img src="'.PJ_INSTALL_URL.'app/uploads/news/'.$v['nw_image'].'" style="width:100px;height:100px"/>';
                        $data[$k]['nw_is_active'] = ($v['nw_is_active'] == 1) ? 'Yes' : 'No';
                        $data[$k]['nw_description'] = preg_replace('/\n/','<br>',$v['nw_description']);
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
            $this->appendJs('pjAdminNews.js?'.PJ_CSS_JS_VERSION);
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionAdd() {
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin()) {
            $nw_title = $_POST['nw_title'];
            $nw_date = $_POST['nw_date']; 
            $nw_description = $_POST['nw_description'];
            $nw_link = $_POST['nw_link']; 
            $nw_is_active = $_POST['nw_is_active'];  

            $pjNewsModel = pjNewsModel::factory()->where('t1.nw_is_active', 1);				
            $count =  $pjNewsModel->findCount()->getData();
            
            if (isset($_FILES["nw_image"]["tmp_name"])) {
                $image_detail = getimagesize($_FILES["nw_image"]["tmp_name"]);
            } else {
                $image_detail = '';
            }

             if (!$nw_title || !$nw_date || !$nw_description) {
                $data['success'] = 0;
                $data['message'] = 'Please enter Title, Date and Description';
            } else if (!$image_detail) {
                $data['success'] = 0;
                $data['message'] = 'Please upload an image'; 
            //} else if ($image_detail[0] != 600 || $image_detail[1] != 300) {
            } else if ($image_detail[0] > 600 || $image_detail[1] > 300) {
                $data['success'] = 0;
                $data['message'] = 'Please upload an image with in 600 x 300px dimension'; 
            // } else if ($nw_is_active == 1 && $count == 3) {
            //     $data['success'] = 0;
            //     $data['message'] = 'Already three News are active';
            } else {
                $file_name = time().'-'.str_replace(' ','',basename($_FILES["nw_image"]["name"]));
                $target_file = 'app/uploads/news/'.$file_name;

                if (move_uploaded_file($_FILES["nw_image"]["tmp_name"], $target_file)) {
                    if (strpos($nw_date,'-')) {
                        $date_explode = explode('-',$nw_date);
                    } else {
                        $date_explode = explode('/',$nw_date);
                    } 

                    $item_sa['nw_title'] = $nw_title;
                    $item_sa['nw_date'] = $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0];
                    $item_sa['nw_description'] = $nw_description;
                    $item_sa['nw_link'] = $nw_link;
                    $item_sa['nw_is_active'] = $nw_is_active;
                    $item_sa['nw_image'] = $file_name;
                    $item_sa['nw_added_by'] = $this->getUserId();
                    $item_sa['nw_added_on'] = date("Y-m-d H:i:s");

                    $result = pjNewsModel::factory($item_sa)->insert();
                    if ($result) {
                        $data['success'] = 1;
                        $data['message'] = 'News added';
                    } else {
                        $data['success'] = 0;
                        $data['message'] = 'Sorry, some technical problem occured';
                    }
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
            $news = pjNewsModel::factory()->where('nw_id',$id)->findAll()->getData();	
            if (count($news) === 0) {
				pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminNews&action=pjActionIndex");
            } else {
                $explode = $news[0]['nw_date'];
                $this->set('news', $news[0]);
                $this->appendJs('pjAdminNews.js?'.PJ_CSS_JS_VERSION);
            }
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionEdit() {
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin()) {
            $nw_id = $_POST['nw_id'];
            $nw_title = $_POST['nw_title'];
            $nw_date = $_POST['nw_date']; 
            $nw_description = $_POST['nw_description'];
            $nw_link = $_POST['nw_link']; 
            $nw_is_active = $_POST['nw_is_active'];  

            $news = pjNewsModel::factory()->where('nw_id',$nw_id)->findAll()->getData();

            $pjNewsModel = pjNewsModel::factory()->where('t1.nw_is_active', 1)->where('t1.nw_id !=',$nw_id);				
            $count =  $pjNewsModel->findCount()->getData();

            if (isset($_FILES["nw_image"]["tmp_name"])) {
                $image_detail = getimagesize($_FILES["nw_image"]["tmp_name"]);
            } else {
                $image_detail = '';
            }

             if (!$nw_title || !$nw_date || !$nw_description) {
                $data['success'] = 0;
                $data['message'] = 'Please enter Title, Date and Description';            
            // } else if ($image_detail && ($image_detail[0] != 600 || $image_detail[1] != 300)) {
            } else if ($image_detail && ($image_detail[0] > 600 || $image_detail[1] > 300)) {
                $data['success'] = 0;
                $data['message'] = 'Please upload an image with 600 x 300px dimension'; 
            // } else if ($nw_is_active == 1 && $count == 3) {
            //     $data['success'] = 0;
            //     $data['message'] = 'Already three News are active';
            } else {
                if ($image_detail) {
                    $file_name = time().'-'.str_replace(' ','',basename($_FILES["nw_image"]["name"]));
                    $target_file = 'app/uploads/news/'.$file_name;
                    $image_result = move_uploaded_file($_FILES["nw_image"]["tmp_name"], $target_file);
                    if (!$image_result) {
                        $file_name = $news[0]['nw_image'];    
                    }
                } else {
                    $file_name = $news[0]['nw_image'];
                }

                if (strpos($nw_date,'-')) {
                    $date_explode = explode('-',$nw_date);
                } else {
                    $date_explode = explode('/',$nw_date);
                }               

                $result = pjNewsModel::factory()->whereIn('nw_id', $nw_id)->modifyAll(array(
                    'nw_title' => $nw_title,
                    'nw_date' => $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0],
                    'nw_description' => $nw_description,
                    'nw_link' => $nw_link,
                    'nw_is_active' => $nw_is_active,
                    'nw_image' => $file_name
                ));
                if ($result) {
                    if ($image_detail && $image_result && $file_name != $news[0]['nw_image']) {
                        @unlink('app/uploads/news/'.$news[0]['nw_image']);
                    }

                    $data['success'] = 1;
                    $data['message'] = 'News edited';
                } else {
                    $data['success'] = 0;
                    $data['message'] = 'Sorry, some technical problem occured';
                }
            }
            echo json_encode($data);
        }
		exit;
    }

    public function pjActionDeleteNews()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $response = array();
                if ($_GET['id'] != $this->getUserId() && $_GET['id'] != 1)
                {
                    $pjNewsModel = pjNewsModel::factory();
                    $pjNewsModel->where('t1.nw_id =',$_GET['id']);
                    
                    $arr1 = $pjNewsModel->findAll()->getData();
                    $arr = (count($arr1)) ? $arr1[0] : array();
                
                    if (count($arr) && pjNewsModel::factory()->setAttributes(array('nw_id' => $_GET['id']))->erase()->getAffectedRows() == 1)
                    {
                        @unlink('app/uploads/news/'.$arr1[0]['nw_image']);
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