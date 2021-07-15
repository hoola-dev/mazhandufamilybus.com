<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFrontTicket extends pjFront
{
	public function __construct()
	{
		parent::__construct();
	}

	public function pjActionPrintTicket()
	{
        require_once('app/lib/qr_code/qrlib.php');
        $print_ticket_id = $_GET['id'];
        $print_ticket_hash = $_GET['hash'];
        $qr_code_png_folder = PJ_INSTALL_PATH.'app/lib/qr_code/temp/';
        $qr_code_png_web_folder = PJ_INSTALL_URL.'app/lib/qr_code/temp/';
        $qr_code_filename = $qr_code_png_folder.'ticket_'.$print_ticket_id.'_'.time().'.png';
        $qr_error_correction_level = 'L';
        $qr_code_matrix_point_size = min(max((int)6, 1), 10);

		$this->setLayout('pjActionPrint');
	
		$pjBookingModel = pjBookingModel::factory();
	
		$arr = $pjBookingModel
			->select('t1.*, t2.content as from_location, t3.content as to_location')
			->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			->find($_GET['id'])
			->getData();
	
		if(!empty($arr))
		{
			if ($arr['is_return'] == 'T')
			{
				$arr['return_arr'] = $pjBookingModel
					->reset()
					->select('t1.*, t2.content as from_location, t3.content as to_location')
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->find($arr['back_id'])->getData();
			}
				
			$hash = sha1($arr['id'].$arr['created'].PJ_SALT);
			if($hash == $_GET['hash'])
			{
				if($arr['status'] == 'confirmed')
				{
					$arr['tickets'] = pjBookingTicketModel::factory()->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->join('pjTicket', "t3.id=t1.ticket_id", 'left')
						->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.pjPriceModel::factory()->getTable().'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$arr['bus_id'].' AND TP.from_location_id = '.$arr['pickup_id'].' AND TP.to_location_id= '.$arr['return_id']. ' AND is_return = "F") as price')
						->where('booking_id', $arr['id'])
						->findAll()->getData();
	
					$pjCityModel = pjCityModel::factory();
					$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['pickup_id'])->getData();
					$to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['return_id'])->getData();
					$arr['from_location'] = $pickup_location['name'];
					$arr['to_location'] = $to_location['name'];
	
					$pjMultiLangModel = pjMultiLangModel::factory();
					$lang_template = $pjMultiLangModel
						->reset()
						->select('t1.*')
						->where('t1.model','pjOption')
						->where('t1.locale', $this->getLocaleId())
						->where('t1.field', 'o_ticket_template')
						->limit(0, 1)
						->findAll()->getData();
					$template = '';
					if (count($lang_template) === 1)
					{
						$template = $lang_template[0]['content'];
					}

					$data = pjAppController::getTemplate($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());
                    
                    $qr_code_data = PJ_INSTALL_URL.'index.php?controller=pjFrontTicket&action=pjActionViewTicket&id='.$print_ticket_id.'&hash='.$print_ticket_hash;            

                    QRcode::png($qr_code_data, $qr_code_filename, $qr_error_correction_level, $qr_code_matrix_point_size, 2);  
                
                    $data['search'][] = '{QRCode}';
                    $data['replace'][] = '<img src="'.$qr_code_png_web_folder.basename($qr_code_filename).'" />';

                    $template_arr = str_replace($data['search'], $data['replace'], $template);
					$this->set('template_arr', $template_arr);
				}
			}elseif ($arr['status'] == 'pending'){
				$this->set('pending_booking', true);
			}
		} else {
			$this->set('status', 2);
		}
    }
    
    public function pjActionViewTicket()
	{		
        require_once('app/lib/qr_code/qrlib.php');
        $print_ticket_id = $_GET['id'];
        $print_ticket_hash = $_GET['hash'];
        $qr_code_png_folder = PJ_INSTALL_PATH.'app/lib/qr_code/temp/';
        $qr_code_png_web_folder = PJ_INSTALL_URL.'app/lib/qr_code/temp/';
        $qr_code_filename = $qr_code_png_folder.'ticket_'.$print_ticket_id.'_'.time().'.png';
        $qr_error_correction_level = 'L';
        $qr_code_matrix_point_size = min(max((int)6, 1), 10);              

        $this->setLayout('pjActionTicket');
        
        $pjBookingModel = pjBookingModel::factory();
                        
        $arr = $pjBookingModel->find($_GET['id'])->getData();
        if (empty($arr))
        {
            exit('No ticket');
        }
        $hash = sha1($arr['id'].$arr['created'].PJ_SALT);
        if($hash != $_GET['hash'])
        {
            exit('No ticket');
        }
        if($arr['status'] == 'confirmed')
        {
            $price_tbl = pjPriceModel::factory()->getTable();
            
            $pjBookingTicketModel = pjBookingTicketModel::factory();
            $tickets = $pjBookingTicketModel
                ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$arr['bus_id'].' AND TP.from_location_id = '.$arr['pickup_id'].' AND TP.to_location_id= '.$arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                ->where('booking_id', $arr['id'])
                ->findAll()->getData();

            $arr['tickets'] = $tickets;
            
            $pjCityModel = pjCityModel::factory();
            $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['pickup_id'])->getData();
            $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['return_id'])->getData();
            $arr['from_location'] = $pickup_location['name'];
            $arr['to_location'] = $to_location['name'];	
                
            $pjMultiLangModel = pjMultiLangModel::factory();
            $lang_template = $pjMultiLangModel
                ->reset()->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $this->getLocaleId())
                ->where('t1.field', 'o_ticket_template')
                ->limit(0, 1)
                ->findAll()->getData();
            $template = '';											 
            if (count($lang_template) === 1)
            {
                $template = $lang_template[0]['content'];
            }                          

            $data = pjAppController::getTemplate($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());       

            $qr_code_data = PJ_INSTALL_URL.'index.php?controller=pjFrontPublic&action=pjActionPrintTickets&id='.$print_ticket_id.'&hash='.$print_ticket_hash;            
            QRcode::png($qr_code_data, $qr_code_filename, $qr_error_correction_level, $qr_code_matrix_point_size, 2);   
            
            $data['search'][] = '{QRCode}';
            $data['replace'][] = '<img src="'.$qr_code_png_web_folder.basename($qr_code_filename).'" />';

            $template_arr = str_replace($data['search'], $data['replace'], $template);
            
            $this->set('template_arr', $template_arr);
        }elseif ($arr['status'] == 'pending'){
            exit('No ticket');
        }
	}	
}
?>