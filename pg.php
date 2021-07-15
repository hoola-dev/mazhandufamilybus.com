<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);


class pjApiPayment
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
           echo json_encode($response); 
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

            $fp = fopen(getcwd().'/test.txt', 'a');//debug file
            
            fwrite($fp, "start curl request \n"); 

            $json_response = curl_exec($ch);

            fwrite($fp, $json_response."\n"); 

            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);

                fwrite($fp, $error_msg."\n"); 

                $response = array(
                        'status' => 'false',
                        'message' =>  $respObj->DESCRIPTION
                    );
                echo json_encode($error_msg);
            }else{
                $respObj = json_decode($json_response);

                fwrite($fp, $json_response."\n\n"); 

                /*if(isset($respObj->RESPONSECODE) && ($respObj->RESPONSECODE == 400 || $respObj->RESPONSECODE == 500)){
                    $response = array(
                        'status' => 'false',
                        'message' =>  $respObj->DESCRIPTION
                    );
                    pjAppController::jsonResponse($response);
                }elseif(isset($respObj->RESPONSECODE) && ($respObj->RESPONSECODE == 200)){
                    $response = array(
                            'status' => 'true',
                            'message' =>  $respObj->DESCRIPTION
                        );
                    pjAppController::jsonResponse($response);
                }*/
                if(isset($respObj->RESPONSE)){
                    if(isset($respObj->RESPONSE->RESPONSECODE) && ($respObj->RESPONSE->RESPONSECODE == 'successful' || $respObj->RESPONSE->RESPONSECODE == 100 || $respObj->RESPONSE->RESPONSECODE == '100')){

                            fwrite($fp, "success response from PG\n"); 
                             $response = array(
                            'status' => 'true',
                            'message' => 'payment successfull'
                            );
                            //echo $json_response;
                            echo json_encode($response);
                    }
                }
            }

            curl_close($ch);

            //close debug file
            fclose($fp); 


            $response = array(
                    'status' => 'false',
                    'message' => 'payment failed'
            );
            echo json_encode($response);
        }
    }

    /*
     * Create random request id for lipila payment
     */
    public function createRequestId($length) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        return substr(str_shuffle($chars),0,$length);
    }
}

$ob = new pjApiPayment();
$ob->pjActionPay();

?>