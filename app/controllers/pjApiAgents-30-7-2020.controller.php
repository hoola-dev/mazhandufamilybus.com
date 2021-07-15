<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
use ReallySimpleJWT\Token;

class pjApiAgents extends pjFront
{
    
    private $jwt_secret = 'maz!ReT423*&';
	public function pjActionAgents() {
        $district_id = (isset($_GET['district_id']) && !empty(trim($_GET['district_id']))) ? trim($_GET['district_id']) : 0;
        $districts = pjDistrictModel::factory()->orderBy('t1.dt_name ASC')->findAll()->getData();
        $data = array();
        if(isset($districts[0]['dt_id']) && !empty($districts[0]['dt_id'])){
            $district_id = (isset($_GET['district_id']) && !empty(trim($_GET['district_id']))) ? trim($_GET['district_id']) : $districts[0]['dt_id'];
            $data = pjUserModel::factory()
            ->select('id, name, email, phone, address, zipcode, district')
            ->where('role_id', 2)
            ->where('status','T')
            ->where('district',$district_id)
            ->limit(15)
            ->findAll()
            ->getData(); 
             if(!isset($data[0]['id'])){
                 $data = array(
                    'message' => 'No agents found'
                );
                $status = false; 
             }else{
                $status = true; 
             } 
             
        }else{
            $data = array(
                    'message' => 'No agents found'
                );
            $status = false;  
        }
        $response = array(
            'status' => $status,
            'data' => $data
        );    
        pjAppController::jsonResponse($response);
    }

    /*
     * Api for agent login
     */
    public function pjActionLogin(){
        $email = $_POST['email'];
        $password = $_POST['password'];
        $pjUserModel = pjUserModel::factory();
        $user = $pjUserModel
            ->select('t1.*,t2.role_name,t3.at_type')
            ->join('pjRole', 't2.id=t1.role_id', 'left')
            ->join('pjAgentType', 't3.at_id=t1.agent_type', 'left')
            ->where('t1.email', $email)
            ->where('t2.id', 2)
            ->where(sprintf("t1.password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($password), PJ_SALT))
            ->limit(1)
            ->findAll()
            ->getData();
        if(!empty($user[0])){
            $user = $user[0];
            $data = array();
            //generate jwt token for api authentication
            $userId = $user['id'];
            $expiration = time() + 3600;
            $issuer = 'mfb';
            try {
                $token = Token::create($userId, $this->jwt_secret, $expiration, $issuer);
            } catch (Exception $e) {
                $response = array(
                    'status' => 'false',
                    'message' => $e->getMessage()
                ); 
            }
            
            $data['token'] = $token;
            $data['agent_name'] = $user['name'];
            $sub_agent_code = $user['user_code'];
            $data['agent_id'] = "$sub_agent_code/" . $user['id'];
            $data['agent_user_id'] = $user['id'];
            $response = array(
               'status' => 'true',
               'data' => $data
            );
        }else{
           $response = array(
                'status' => 'false',
                'message' => 'Invalid login'
           );    
        } 
        pjAppController::jsonResponse($response); 

    }

    

    
}
?>