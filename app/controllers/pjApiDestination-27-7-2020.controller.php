<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiDestination extends pjFront
{
    

	public function pjActionDestination() {
        $pjCityModel = pjCityModel::factory();
        $pjRouteDetailModel = pjRouteDetailModel::factory();
        $destination_location_arr = $pjCityModel
                ->reset()
                ->select('t1.id, t2.content as name')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
                ->orderBy("t2.content ASC")
                ->findAll()
                ->getData();

        if(isset($destination_location_arr[0]['id']) && !empty($destination_location_arr[0]['id'])){
            $aDefaultOption=array("id"=>"",
                                  "name" => "--Choose--"
                              );
            array_unshift($destination_location_arr,$aDefaultOption);
            $response = array(
            'status' => 'true',
            'data' => $destination_location_arr);  
        }else{
            $response = array(
                'status' => 'false',
                'message' => 'No data found');  
        }    
        pjAppController::jsonResponse($response);
    }
    public function pjActionToLocation() {
        $source = $_GET['source'];
        $status = true; 
        if(isset($source) && !empty($source)){
            $pjCityModel = pjCityModel::factory();
            $pjRouteDetailModel = pjRouteDetailModel::factory();
            $destination_location_arr = $pjCityModel
                    ->reset()
                    ->select('t1.id, t2.content as name')
                    ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
                    ->orderBy("t2.content ASC")
                    ->findAll()
                    ->getData();

            if(isset($destination_location_arr[0]['id']) && !empty($destination_location_arr[0]['id'])){
                $aDefaultOption=array("id"=>"",
                                  "name" => "--Choose--"
                              );
                array_unshift($destination_location_arr,$aDefaultOption);
                $data = array(
                'data' => $destination_location_arr);  
            }else{
                $status = false;  
                $data = array(
                    'message' => 'No data found');  
            }     
        }else{
            $status = false; 
            $data = array(
                    'message' => 'Please provide source'
                );
        }
        $response = array(
            'status' => $status,
            'data' => $data
        );    

        
        pjAppController::jsonResponse($response);
    }

    
}
?>