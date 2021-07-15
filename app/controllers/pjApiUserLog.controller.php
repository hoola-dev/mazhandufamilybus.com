<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiUserLog extends pjFront
{

	public function pjActionUserLog(){
        $name = (isset($_POST['name']) && trim($_POST['name'])) ? trim($_POST['name']) : '';
        $email = (isset($_POST['email']) && trim($_POST['email'])) ? trim($_POST['email']) : '';
        $mobile = (isset($_POST['mobile']) && trim($_POST['mobile'])) ? trim($_POST['mobile']) : '';

        if(empty($name) || empty($email) || empty($mobile)){
            $resp = array(
                            'status' => 'false',
                            'message' => 'Please provide all parameters'
                        );
            pjAppController::jsonResponse($resp);
        }
        //add to user log
        $log_data = array();
        $log_data['name'] = $name;
        $log_data['email'] = $email;
        $log_data['mobile'] = $mobile;
        $id = pjUserLogModel::factory($log_data)->insert()->getInsertId();
        
        if(isset($id) && ($id > 0)){
            $data = array('log_id' => $id);
            $resp = array(
                            'status' => 'true',
                            'data' => $data,
                            'message' => 'Added to log'
                        );
        }else{
           $resp = array(
                            'status' => 'false',
                            'message' => 'Could not add to log'
                        ); 
        }
       pjAppController::jsonResponse($resp);  
    }
   
}
?>