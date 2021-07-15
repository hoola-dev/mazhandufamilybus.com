<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjPriceModel extends pjAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'prices';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'bus_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'ticket_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'from_location_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'to_location_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'price', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'is_return', 'type' => 'enum', 'default' => 'F')
	);
	
	public static function factory($attr=array())
	{
		return new pjPriceModel($attr);
	}
	
	public function getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $option_arr, $locale_id, $is_return)
	{
		$sub_total = 0;
		$tax = 0;
		$total = 0;
		$deposit = 0;
		
		$sub_total_format = '';
		$tax_format = '';
		$total_format = '';
		$deposit_format = '';
		
		$ticket_arr = $this
			->reset()
			->join('pjTicket', 't1.ticket_id = t2.id', 'left')
			->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$locale_id."'", 'left outer')
			->join('pjBus', 't1.bus_id = t4.id', 'left')
			->select("t1.*, t2.seats_count, t3.content as ticket, t4.discount")
			->where('t1.bus_id', $bus_id)
			->where('t1.from_location_id', $pickup_id)
			->where('t1.to_location_id', $return_id)
			->where('is_return = "F"')
			->index("FORCE KEY (`ticket_id`)")
			->orderBy("ticket ASC")
			->findAll()
			->getData();
		
		foreach($ticket_arr as $k => $v)
		{
			$return_str = $is_return == 'T' ? 'return_' : '';
			  
			if(isset($booked_data[$return_str . 'ticket_cnt_' . $v['ticket_id']]) && (int) $booked_data[$return_str . 'ticket_cnt_' . $v['ticket_id']] > 0)
			{
				$discount = 0;
				if(isset($v['discount']) && (float) $v['discount'] > 0 && $is_return == 'T')
				{
					$discount = $v['discount'];
				}
				$price = $v['price'] - ($v['price'] * $discount / 100);
				$sub_total += (int) $booked_data[$return_str . 'ticket_cnt_' . $v['ticket_id']] * $price;
			}
		}
		
		if(!empty($option_arr['o_tax_payment']) && $sub_total > 0)
		{
			$tax = ($option_arr['o_tax_payment'] * $sub_total) / 100;
		}
		$total = $sub_total + $tax;
		if(!empty($option_arr['o_deposit_payment']) && $total > 0)
		{
			$deposit = ($option_arr['o_deposit_payment'] * $total) / 100;
		}
		
		$sub_total_format = pjUtil::formatCurrencySign(number_format($sub_total, 2), $option_arr['o_currency']);
		$tax_format = pjUtil::formatCurrencySign(number_format($tax, 2), $option_arr['o_currency']);
		$total_format = pjUtil::formatCurrencySign(number_format($total, 2), $option_arr['o_currency']);
		$deposit_format = pjUtil::formatCurrencySign(number_format($deposit, 2), $option_arr['o_currency']);
		
		return compact('ticket_arr', 'sub_total', 'tax', 'total', 'deposit', 'sub_total_format', 'tax_format', 'total_format', 'deposit_format');
	}
}
?>