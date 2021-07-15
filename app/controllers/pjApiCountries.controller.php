<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiCountries extends pjFront
{

	public function pjActionCountries() {
        $data = array();
        $countries_arr = pjCountryModel::factory()
            ->select('t1.id, t2.content AS name,t1.phone_code')
            ->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->orderBy('`name` ASC')->findAll()->getData();

        if(isset($countries_arr[0]['id']) && !empty($countries_arr[0]['id'])){
            $aDefaultOption=array("id"=>"248",
                                  "name" => "Zambia",
                                  "phone_code" => "+260"
                              );
            array_unshift($countries_arr,$aDefaultOption);
            $data = $countries_arr;
            $status = true;  
        }else{
            $data = array(
                    'message' => 'No countries found'
                );
            $status = false;  
        }
        $response = array(
            'status' => $status,
            'data' => $data
        );    
        pjAppController::jsonResponse($response);
    }	
   
}
?>