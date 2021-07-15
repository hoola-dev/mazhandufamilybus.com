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
    
    private $pwd_length = 4; 
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
                ->select('t1.id,t1.status,t1.name,t1.email')
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
                $data['name'] = $user['name'];
                $data['email'] = $user['email'];
                $data['phone'] = $mobile;
                //if status of user is F(inactive), send error message
                if(isset($user['status']) && ($user['status'] == 'F')){
                    $response = array(
                        'status' => 'false',
                        'message' => 'Your account is blocked. Please contact Administrator.'
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

            //check if user with this mobile number already exists
            $pjUserModelChk = pjUserModel::factory();
            $userCheckResult = $pjUserModelChk
            ->select('t1.id,t1.status,t1.email,t1.phone')
            ->join('pjRole', 't2.id=t1.role_id', 'left')
            ->where('t1.phone', $mobile)
            ->orWhere('t1.email', $email)
            ->limit(1)
            ->findAll()
            ->getData();

            if(count($userCheckResult) > 0){
                $userChk = $userCheckResult[0];
                $err_msg = '';
                if($userChk['email'] == $email && ($userChk['phone'] == $mobile)){
                    $err_msg = 'User with this mobile number and email already exists';
                }elseif($userChk['email'] == $email){
                    $err_msg = 'User with this email already exists';
                }elseif($userChk['phone'] == $mobile){
                    $err_msg = 'User with this mobile number already exists';
                }
                $response = array(
                    'status' => 'false',
                    'message' => $err_msg
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

            
            try {
                $id = pjUserModel::factory($userData)->insert()->getInsertId();
            } catch (Exception $e) {
                $response = array(
                    'status' => 'false',
                    'message' => "Couldn't create user"
                ); 
                pjAppController::jsonResponse($response);
            }
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
            //send email to the user
            pjApiNormalUser::sendEmail($name, $mobile, $email, $password);

            //send sms to the user
            $sms_message = 'Your Password with '.PJ_SITE_NAME.' is '.$password;
            $params = array(
                    'text' => $sms_message,
                    'type' => 'unicode',
                    'key' => md5($this->option_arr['private_key'] . PJ_SALT)
            );
            $params['number'] = $mobile;
            $smsResult = $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));

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
        $chars = "0123456789";
        return substr(str_shuffle($chars),0,$length);
    }

    /*
     * send email
     */
    public function sendEmail($name, $mobile, $email, $password, $msg = '') {
        $Email = new pjEmail();
        if ($this->option_arr['o_send_email'] == 'smtp')
        {
            $Email
                ->setTransport('smtp')
                ->setSmtpHost($this->option_arr['o_smtp_host'])
                ->setSmtpPort($this->option_arr['o_smtp_port'])
                ->setSmtpUser($this->option_arr['o_smtp_user'])
                ->setSmtpPass($this->option_arr['o_smtp_pass'])
                ->setSender($this->option_arr['o_smtp_user'])
            ;
        }
        $Email->setContentType('text/html');
        if(!empty($msg)){
            $message = $msg;
        }else{
            $message = "Hi ".$name.",<br/><br/>";
            $message .= "Please use following credentials to login.<br/>";
            $message .= "Mobile: $mobile<br/>";
            $message .= "Password: $password<br/><br/>";
            $message .= "Best Regards,<br/>";
            $message .= "mazhandufamilybus.com<br/>";
        }

        $message = pjUtil::textToHtml($message);
        $subject = "Mazhandu Family Bus: credentials";
        $from_email = $this->option_arr['o_sender_email'];
        
        $Email->setTo($email)
        ->setFrom($from_email)
        ->setSubject($subject)
        ->send($message);
    }

    /*
     * create new password for the mobile number, send email and sms
     */
    public function forgotPwd(){
        $mobile = trim($_POST['mobile']);
        if(empty($mobile)){
            //error if mobile not provided
            $response = array(
                'status' => 'false',
                'message' => 'Please provide mobile number'
           );
           pjAppController::jsonResponse($response); 
        }else{
            //check if user with the given mobile number exists
            $pjUserModel = pjUserModel::factory();
            $userResult = $pjUserModel
            ->select('t1.id,t1.name,t1.email')
            ->join('pjRole', 't2.id=t1.role_id', 'left')
            ->where('t1.phone', $mobile)
            ->limit(1)
            ->findAll()
            ->getData();
            if(count($userResult) == 0){
                $response = array(
                    'status' => 'false',
                    'message' => "This mobile number is not registered"
                ); 
                pjAppController::jsonResponse($response);
            }else{
                $user = $userResult[0];
                //update new password for the given mobile number
                $new_pwd = pjApiNormalUser::createPassword($this->pwd_length);
                pjApiNormalUser::updatePassword($user['id'], $mobile, $new_pwd);

                //email new password
                $message = "Hi " . $user['name'] . ",<br/><br/>";
                $message .= 'Your new password with ' . PJ_SITE_NAME . ' is ' . $new_pwd . "<br/><br/>";
                $message .= "Best Regards,<br/>";
                $message .= "mazhandufamilybus.com<br/>";
                pjApiNormalUser::sendEmail($user['name'], $mobile, $user['email'], $new_pwd, $message);

                //send sms to the mobile number
                $sms_message = 'Your new password with ' . PJ_SITE_NAME . ' is ' . $new_pwd;
                $params = array(
                        'text' => $sms_message,
                        'type' => 'unicode',
                        'key' => md5($this->option_arr['private_key'] . PJ_SALT)
                );
                $params['number'] = $mobile;
                $smsResult = $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));

                //send success message
                $data = array('new_password' => $new_pwd);
                $response = array(
                                'status' => 'true',
                                'data' => $data,
                                'message' => 'Please use the new password'
                            );
                pjAppController::jsonResponse($response);
            }
        }
    }

    /*
     * update password for the given mobile number
     */
    public function updatePassword($id, $mobile, $new_pwd){
        $pjUsrModel = pjUserModel::factory();       
        $pjUsrModel->reset()->set('id', $id)->modify(
            array(
                'password' => $new_pwd
            )
        );
    }

    /*
     * List bookings of given user id
     */
    public function myBookings(){
        $user_id = (isset($_POST['user_id']) && trim($_POST['user_id'])) ? trim($_POST['user_id']) : '';
        //filters
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $status = (isset($status) && $status == 'all') ? '' : $status;
        $srch_departure_on = (isset($_POST['departure_on']) && trim($_POST['departure_on'])) ? trim($_POST['departure_on']) : '';
        $srch_booking_on = (isset($_POST['booked_on']) && trim($_POST['booked_on'])) ? trim($_POST['booked_on']) : '';

        if(empty($user_id) || empty($user_id)){
            $resp = array(
                        'status' => 'false',
                        'message' => 'Please provide all parameters'
                    );
            pjAppController::jsonResponse($resp);  
        }
        //get selected user's bookings
        $pjBookingModel = pjBookingModel::factory()                
            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer');
        if (isset($status) && !empty($status) && in_array($status, array('confirmed','cancelled','pending')))
        {
            $pjBookingModel->where('t1.status', $status);
        }

        if (isset($srch_departure_on) && !empty($srch_departure_on))
        {
            $pjBookingModel->where('t1.booking_date', $srch_departure_on);
        }

        if (isset($srch_booking_on) && !empty($srch_booking_on))
        {
            $pjBookingModel->where('DATE(t1.created)', $srch_booking_on);
        }

        $pjUserBookingsModel = pjUserBookingsModel::factory();
        $userBookings = $pjBookingModel->where('(t1.id IN (SELECT booking_id FROM '.$pjUserBookingsModel->getTable().' WHERE user_id = '.$user_id.'))')
        ->orderBy("t1.id DESC")
        ->findAll()
        ->getData();
        $respArr = array();
        if(count($userBookings)){
            foreach($userBookings as $key => $booking){
                $arr = array();
                $arr['booking_id'] = $userBookings[$key]['id'];
                $arr['c_title'] = $userBookings[$key]['c_title'];
                $arr['c_fname'] = $userBookings[$key]['c_fname'];
                $arr['c_lname'] = $userBookings[$key]['c_lname'];
                $arr['c_phone'] = $userBookings[$key]['c_phone'];
                $arr['c_email'] = $userBookings[$key]['c_email'];

                $booking_route = strip_tags($userBookings[$key]['booking_route']);
                $bus_name_arr = explode(",",$booking_route);
                $arr['bus_name'] = $bus_name_arr[0];

                $bookingArr = explode("from",$booking_route);
                $locFromTo = $bookingArr[1];
                $locNameArr = explode("to",$locFromTo);
                $arr['to_name'] = trim($locNameArr[0]);
                $arr['from_name'] = trim($locNameArr[1]);

                $arr['status'] = $userBookings[$key]['status'];
                $arr['total_amount'] = $userBookings[$key]['total'];
                $booking_datetime_arr = explode(" ", $userBookings[$key]['booking_datetime']);
                $arr['booking_date'] = $booking_datetime_arr[0];
                $arr['booking_time'] = $userBookings[$key]['booking_time'];
                array_push($respArr, $arr);
            }
        }   
        $resp = array(
                    'status' => 'true',
                    'data' => $respArr
                );
        pjAppController::jsonResponse($resp); 
    }

        
}
?>