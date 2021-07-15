<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}

class pjApiPayment extends pjFront
{
    private $payment_gateway_url = 'http://41.175.8.69:8181/payments/lipila/payment/collection';
    private $identifier = 'paraz4m';
    private $key = 'Z3JlYXRlcmlzSEV3aG9pc21ldGhhbnRoZXdvcmxkMWpvaG40NA==';
    //private $request_id = '102322346gq4f19';
    private $request_type = 'collection';
    private $req_id_length = 8; 

    /*
     * Api to send request to payment gateway(Lipila)
     */
    public function pjActionPay(){
        $mobile = (isset($_POST['mobile'])) ? trim($_POST['mobile']) : '';
        $amount = (isset($_POST['amount'])) ? trim($_POST['amount']) : '';

        if(empty($mobile) || empty($amount)){
            $response = array(
                'status' => 'false',
                'message' => 'Please provide all required parameters'
           );
           pjAppController::jsonResponse($response); 
        }else{
            $rand_request_id = pjApiPayment::createRequestId($this->req_id_length);//create a random request id to identify the payment
            $post_params = array(
                'AUTHENTICATION' => array(
                    'IDENTIFIER' => $this->identifier,
                    'KEY' => $this->key
                ),
                'TRANSACTION' => array(
                    'AMOUNT' => $amount,
                    'MSISDN' => $mobile,
                    'REQUESTID' => $rand_request_id,
                    'REQUESTTYPE' => $this->request_type
                )
            );
            $curlResponse = pjApiPayment::sendCurlRequest($post_params);
            //echo $curlResponse;
            $data = array();
            $data['request_id'] = $rand_request_id;
            $respObj = json_decode($curlResponse);
            if(isset($respObj->RESPONSECODE) && ($respObj->RESPONSECODE == 200 || $respObj->RESPONSECODE == '200')){
                    $response = array(
                        'status' => 'true',
                        'data' => $data,
                        'message' => $respObj->DESCRIPTION
                    );
                    pjAppController::jsonResponse($response);
            }

            $response = array(
                    'status' => 'false',
                    'data' => $data,
                    'message' => 'payment failed'
            );

            pjAppController::jsonResponse($response);
        }
    }

    /*
     * send request and get response
     */
    public function sendCurlRequest($post_params) {
        $post_data = json_encode($post_params);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_HTTPHEADER,array (
            "Accept: application/json"
        ));
        curl_setopt($ch, CURLOPT_URL, $this->payment_gateway_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //$fp = fopen(getcwd().'/app/controllers/test.txt', 'a');//debug file
        //fwrite($fp, "start curl request \n"); 
        $json_response = curl_exec($ch);
        //fwrite($fp, $json_response."\n"); 
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            //fwrite($fp, $error_msg."\n"); 
        }
        curl_close($ch);
        //fclose($fp); 
        return $json_response;
    }

    /*
     * Create random request id for lipila payment
     */
    public function createRequestId($length) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        return substr(str_shuffle($chars),0,$length);
    }

    /*
     * Callback for lipila payment gateway
     */
    public function pjCallback(){
        $post_data = json_decode(file_get_contents("php://input"), true);
        $updateFlag = 0;
        if(isset($post_data['RESPONSE']['RESPONSECODE']) && ($post_data['RESPONSE']['RESPONSECODE'] == 'successful' || $post_data['RESPONSE']['RESPONSECODE'] == '100' || $post_data['RESPONSE']['RESPONSECODE'] == 100)){
            //insert to payments table or update if already exists
            $request_id = $post_data['RESPONSE']['EXTID'];//unique request_id to identify payment
            $amount = $post_data['RESPONSE']['AMOUNT'];
            $pjPaymentModel = pjPaymentModel::factory();
            $paymentRes = $pjPaymentModel
            ->select('t1.id,t1.booking_ids')
            ->where('t1.transction_id', $request_id)
            ->limit(1)
            ->findAll()
            ->getData();
            if(count($paymentRes) > 0 && isset($paymentRes[0]) && !empty($paymentRes[0])){
                //already exists
                $p_res = $paymentRes[0];
                $id = $p_res['id'];
                $booking_ids = $p_res['booking_ids'];
                $pjPaymentModel = pjPaymentModel::factory();       
                try {
                    $pjPaymentModel->reset()->set('id', $id)->modify(
                        array(
                            'amount' => $amount,
                            'status' => 'T'
                        )
                    );
                    //update booking status to confirmed
                    if(strpos($booking_ids, ',') !== false) {
                       $booking_id_arr = explode(',', $booking_ids);
                       if(isset($booking_id_arr[0]) && !empty($booking_id_arr[0])){
                            $booking_id_val = trim($booking_id_arr[0]);
                            $pjBookingModel = pjBookingModel::factory();
                            $pjBookingModel->reset()->set('id', $booking_id_val)->modify(
                                array(
                                    'status' => 'confirmed'
                                )
                            );
                            $updateFlag = 1;
                       }
                       if(isset($booking_id_arr[1]) && !empty($booking_id_arr[1])){
                           $booking_id_val = trim($booking_id_arr[1]);
                           $pjBookingModel = pjBookingModel::factory();
                           $pjBookingModel->reset()->set('id', $booking_id_val)->modify(
                                array(
                                    'status' => 'confirmed'
                                )
                           ); 
                           $updateFlag = 1;
                       }

                    }else{
                       $booking_id_val = trim($booking_ids);
                       $pjBookingModel = pjBookingModel::factory();
                       $pjBookingModel->reset()->set('id', $booking_id_val)->modify(
                            array(
                                'status' => 'confirmed'
                            )
                       ); 
                       $updateFlag = 1;
                    }
                    if($updateFlag == 1){

                        // get phone number
                        $pjBookingModel = pjBookingModel::factory();
                        $bookingRes = $pjBookingModel
                        ->select('t1.c_phone')
                        ->where('t1.id', $booking_id_val)
                        ->limit(1)
                        ->findAll()
                        ->getData();
                        $book_res = $paymentRes[0];
                        $c_phone = $book_res['c_phone'];

                        //send sms to the user
                        $sms_message = 'Your trip in '.PJ_SITE_NAME.' is confirmed';
                        $params = array(
                                'text' => $sms_message,
                                'type' => 'unicode',
                                'key' => md5($this->option_arr['private_key'] . PJ_SALT)
                        );
                        $params['number'] = $c_phone;
                        $smsResult = $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));

                    }
                } catch (Exception $e) {
                    $response = array(
                        'status' => 'false',
                        'message' => $e->getMessage()
                    ); 
                    pjAppController::jsonResponse($response);
                }

                $response = array(
                    'status' => 'true',
                    'message' => 'successfully updated payment information'
                );
                pjAppController::jsonResponse($response);
            }

            $response = array(
                'status' => 'true',
                'message' => 'Payment information not updated'
            );
            pjAppController::jsonResponse($response);

        }
        

    }

    /*
     * Api to get status of payment(whether payment processing completed or not)
     */
    public function pjGetStatus(){
        $request_id = (isset($_POST['request_id'])) ? trim($_POST['request_id']) : '';
        if(empty($request_id)){
            $response = array(
                'status' => 'false',
                'message' => 'Please provide request id of payment'
           );
           pjAppController::jsonResponse($response); 
        }else{
            $pjPaymentModel = pjPaymentModel::factory();
            $paymentRes = $pjPaymentModel
            ->select('t1.status')
            ->where('t1.transction_id', $request_id)
            ->limit(1)
            ->findAll()
            ->getData();
            if(count($paymentRes) > 0 && isset($paymentRes[0]) && !empty($paymentRes[0])){
                $p_res = $paymentRes[0];
                $status = $p_res['status'];
                $pjPaymentModel = pjPaymentModel::factory();       
                if(isset($status) && ($status == 'T')){
                    $response = array(
                        'status' => 'true',
                        'message' => 'Payment processing completed'
                    ); 
                    pjAppController::jsonResponse($response);
                }
            }
            $response = array(
                'status' => 'false',
                'message' => 'Payment processing not yet completed'
            ); 
            pjAppController::jsonResponse($response);

        }

    }
        
}
?>