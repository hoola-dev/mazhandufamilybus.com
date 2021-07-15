<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiSource extends pjFront
{
    

	public function pjActionSource() {
        $pjCityModel = pjCityModel::factory();
        $pjRouteDetailModel = pjRouteDetailModel::factory();
        $source_location_arr = $pjCityModel
                ->reset()
                ->select('t1.id, t2.content as name')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
                ->orderBy("t2.content ASC")
                ->findAll()
                ->getData();
        if(isset($source_location_arr[0]['id']) && !empty($source_location_arr[0]['id'])){
            $aDefaultOption=array("id"=>"",
                                  "name" => "--Choose--"
                              );
            array_unshift($source_location_arr,$aDefaultOption);
            
            $response = array(
            'status' => 'true',
            'data' => $source_location_arr);  
        }else{
            $response = array(
                'status' => 'false',
                'message' => 'No data found');  
        }
           
        pjAppController::jsonResponse($response);
    }

    
}
?>