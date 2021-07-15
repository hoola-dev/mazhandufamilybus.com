<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
use ReallySimpleJWT\Token;

class pjApiPrint extends pjFrontMobile
{

	private $jwt_secret = 'maz!ReT423*&';
    /*
     * To update ticket print limit 
    */
    public function pjUpdatePrintLimit() {
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';
        $booking_id = (isset($_POST['booking_id']) && trim($_POST['booking_id'])) ? trim($_POST['booking_id']) : '';
        $group_identifier = (isset($_POST['group_identifier']) && trim($_POST['group_identifier'])) ? trim($_POST['group_identifier']) : '';
        if((empty($booking_id) && empty($group_identifier)) || empty($token)){
            $resp = array(
                        'status' => 'false',
                        'message' => 'Please provide parameters'
                    );
            pjAppController::jsonResponse($resp);  
        }

        $pjPrintSettingsModel = pjPrintSettingsModel::factory();
        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){

            if(!empty($booking_id)){
                $printResult = $pjPrintSettingsModel
                ->select('id,print_limit')
                ->where('booking_id', $booking_id)
                ->limit(1)
                ->findAll()
                ->getData();
                if(count($printResult) > 0){
                    $printRes = $printResult[0];
                    $printLimit = $printRes['print_limit'];
                    if($printLimit > 0){
                        $printLimit--;
                        $pjPrintSettingsModel->reset()->set('id', $printRes['id'])->modify(
                            array(
                                'print_limit' => $printLimit
                            )
                        );
                    }
                }
                $resp = array(
                        'status' => 'true',
                        'message' => 'Print limit updated'
                    );
                pjAppController::jsonResponse($resp); 
            }

            if(!empty($group_identifier)){
                $printResult = $pjPrintSettingsModel
                ->select('id,print_limit')
                ->where('group_identifier', $group_identifier)
                ->findAll()
                ->getData();
                if(count($printResult) > 0){
                    foreach($printResult as $printRes){
                        $printLimit = $printRes['print_limit'];
                        if($printLimit > 0){
                            $printLimit--;
                            $pjPrintSettingsModel->reset()->set('id', $printRes['id'])->modify(
                                array(
                                    'print_limit' => $printLimit
                                )
                            );
                        }

                    }
                }
                $resp = array(
                        'status' => 'true',
                        'message' => 'Print limit updated'
                    );
                pjAppController::jsonResponse($resp);
            }
            
        }else{
            $resp = array(
                        'status' => 'false',
                        'message' => 'Invalid token, please login and check bookings'
                    );
            pjAppController::jsonResponse($resp); 
        }
        
    }

    /*
     * To get ticket print limit 
    */
    public function pjGetPrintLimit() {
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';
        $booking_id = (isset($_POST['booking_id']) && trim($_POST['booking_id'])) ? trim($_POST['booking_id']) : '';
        $group_identifier = (isset($_POST['group_identifier']) && trim($_POST['group_identifier'])) ? trim($_POST['group_identifier']) : '';
        if((empty($booking_id) && empty($group_identifier)) || empty($token)){
            $resp = array(
                        'status' => 'false',
                        'message' => 'Please provide parameters'
                    );
            pjAppController::jsonResponse($resp);  
        }

        $pjPrintSettingsModel = pjPrintSettingsModel::factory();
        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){

            if(!empty($booking_id)){
                $printResult = $pjPrintSettingsModel
                ->select('id,print_limit')
                ->where('booking_id', $booking_id)
                ->limit(1)
                ->findAll()
                ->getData();
                if(count($printResult) > 0){
                    $printRes = $printResult[0];
                     $resp = array(
                        'status' => 'true',
                        'data' => array(
                            'booking_id' => $booking_id,
                            'print_limit' => $printRes['print_limit']
                        )
                    );
                    pjAppController::jsonResponse($resp); 
                }
            }

            $print_arr = array();
            if(!empty($group_identifier)){
                $printResult = $pjPrintSettingsModel
                ->select('id,booking_id,print_limit')
                ->where('group_identifier', $group_identifier)
                ->findAll()
                ->getData();
                if(count($printResult) > 0){
                    foreach($printResult as $printRes){
                        $printLimit = $printRes['print_limit'];
                        array_push($print_arr, array(
                            'booking_id' => $printRes['booking_id'],
                            'print_limit' => $printLimit
                        ));

                    }
                }
                $resp = array(
                        'status' => 'true',
                        'data' => $print_arr
                    );
                pjAppController::jsonResponse($resp);
            }
            
        }else{
            $resp = array(
                        'status' => 'false',
                        'message' => 'Invalid token, please login and check bookings'
                    );
            pjAppController::jsonResponse($resp); 
        }

        $resp = array(
                        'status' => 'false',
                        'message' => 'No data found'
                    );
        pjAppController::jsonResponse($resp); 
        
    }

   
}
?>