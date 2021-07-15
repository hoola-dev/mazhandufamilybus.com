<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
use ReallySimpleJWT\Token;

class pjApiNormalUser extends pjFront
{
    
    private $pwd_length = 8; 
    private $normal_user_role_id = 6; 
    private $jwt_secret = 'maz!ReT423*&';
    private $issuer = 'mfb';

    /*
     * Api to create normal user
     */
    public function pjActionCreate(){
        $mobile = trim($_POST['mobile']);//required for login & register
        $name = (isset($_POST['name'])) ? trim($_POST['name']) : '';//required for register
        $email = (isset($_POST['email'])) ? trim($_POST['email']) : '';//required for register

        $address = (isset($_POST['address'])) ? trim($_POST['address']) : '';//optional
        $zipcode = (isset($_POST['zipcode'])) ? trim($_POST['zipcode']) : '';//optional
        $district_id = (isset($_POST['district_id'])) ? trim($_POST['district_id']) : '';//optional
        $ip_addr = $_SERVER['REMOTE_ADDR'];

        if(empty($mobile)){
            $response = array(
                'status' => 'false',
                'message' => 'Please provide all required parameters'
           );
           pjAppController::jsonResponse($response); 
        }else{
            //check if user already exists
            $pwd = (isset($_POST['password'])) ? trim($_POST['password']) : '';//mandatory for login
            $userResult = array();
            if(!empty($pwd)){
                $pjUserModel = pjUserModel::factory();
                $userResult = $pjUserModel
                ->select('t1.id,t1.status')
                ->join('pjRole', 't2.id=t1.role_id', 'left')
                ->where('t1.phone', $mobile)
                ->where(sprintf("t1.password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($pwd), PJ_SALT))
                ->limit(1)
                ->findAll()
                ->getData();
            }
            

            $expiration = time() + 3600;//expiration time for token
            if((count($userResult) > 0) && isset($userResult[0]) && !empty($userResult[0])){
                //user already exists
                $user = $userResult[0];
                $data = array();
                $data['id'] = $user['id'];
                //if status of user is F(inactive), send error message
                if(isset($user['status']) && ($user['status'] == 'F')){
                    $response = array(
                        'status' => 'false',
                        'message' => 'Your status is inactive.'
                    ); 
                    pjAppController::jsonResponse($response);
                }
                //generate jwt token for api authentication
                try {
                    $token = Token::create($user['id'], $this->jwt_secret, $expiration, $this->issuer);
                } catch (Exception $e) {
                    $response = array(
                        'status' => 'false',
                        'message' => $e->getMessage()
                    ); 
                    pjAppController::jsonResponse($response);
                }
                $data['token'] = $token;
                $response = array(
                   'status' => 'true',
                   'data' => $data
                );
                pjAppController::jsonResponse($response);
            }else{
                //invalid login
                if(!empty($mobile) && !empty($pwd)){
                    $response = array(
                        'status' => 'false',
                        'message' => 'Invalid login'
                    ); 
                    pjAppController::jsonResponse($response);
                }
            }  

            //create user
            if(empty($name) || empty($email)){
                $missing_fields = '';
                if(empty($name)){
                    $missing_fields .= 'name,';
                }
                if(empty($email)){
                    $missing_fields .= 'email';
                }
                $missing_fields = rtrim($missing_fields, ',');
                $response = array(
                    'status' => 'false',
                    'message' => "Please provide $missing_fields"
                );
               pjAppController::jsonResponse($response); 
            }
            $password = pjApiNormalUser::createPassword($this->pwd_length);
            $userData = array(
                'role_id' => $this->normal_user_role_id,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => $mobile,
                'is_active' => 'T',
                'ip' => $ip_addr
            );
        
            if(!empty($address)){
                $userData['address'] = $address;
            }
            if(!empty($zipcode)){
                $userData['zipcode'] = $zipcode;
            }
            if(!empty($district_id)){
                $userData['district'] = $district_id;
            }

            $id = pjUserModel::factory($userData)->insert()->getInsertId();
            $data = array();
            //generate jwt token for api authentication
            try {
                $token = Token::create($id, $this->jwt_secret, $expiration, $this->issuer);
            } catch (Exception $e) {
                $response = array(
                    'status' => 'false',
                    'message' => $e->getMessage()
                ); 
                pjAppController::jsonResponse($response);
            }
            $data['id'] = $id;
            $data['token'] = $token;
            $data['password'] = $password;
            $response = array(
               'status' => 'true',
               'data' => $data
            ); 
            pjAppController::jsonResponse($response);
        }
    }

    /*
     * Create random password
     */
    public function createPassword($length) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        return substr(str_shuffle($chars),0,$length);
    }

        
}
?>