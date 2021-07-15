<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCheckout extends pjFront {
	
	public function pjActionSave($foreign_id, $data=array())
	{
		$this->setLayout('pjActionEmpty');
		
		$params = $this->getParams();
		if (!isset($params['key']) || $params['key'] != md5($this->option_arr['private_key'] . PJ_SALT))
		{
			return FALSE;
		}
		
		return $this->pjActionSaveIpn($params['foreign_id'], $params['data']);
	}
	
	private function pjActionSaveIpn($foreign_id, $data)
	{
		return pjPaypalModel::factory()
			->setAttributes(array(
				'foreign_id' => $foreign_id,
				'subscr_id' => @$data['subscr_id'],
				'txn_id' => @$data['txn_id'],
				'txn_type' => @$data['txn_type'],
				'mc_gross' => @$data['mc_gross'],
				'mc_currency' => @$data['mc_currency'],
				'payer_email' => @$data['payer_email'],
				'dt' => date("Y-m-d H:i:s", strtotime(@$data['payment_date']))
			))
			->insert()
			->getInsertId();
	}
	
	public function pjActionForm()
	{
		$this->setLayout('pjActionEmpty');
		
		$this->setAjax(true);
		//KEYS:
		//-------------
		//name
		//id
		//business
		//item_name
		//custom
		//amount
		//currency_code
		//return
		//notify_url
		//submit
		//submit_class
		//target
		$this->set('arr', $this->getParams());
	}
/**
 * @link https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/#id08A6HI00JQU
 */
	public function pjActionSubscribe()
	{
		$this->setAjax(true);
		// KEYS:
		//-------------
		//name
		//id
		//class
		//target
		//business
		//item_name => 127 chars
		//currency_code => 3 chars
		//custom => 255 chars
		//a1_price
		//p1_duration => 1-90 or 1-52 or 1-24 or 1-5 (depend of duration_unit)
		//t1_duration_unit => D,W,M,Y
		//a2_price
		//p2_duration => 1-90 or 1-52 or 1-24 or 1-5 (depend of duration_unit)
		//t2_duration_unit => D,W,M,Y
		//a3_price
		//p3_duration => 1-90 or 1-52 or 1-24 or 1-5 (depend of duration_unit)
		//t3_duration_unit => D,W,M,Y
		//recurring_payments => 0,1
		//recurring_times => 2-52
		//reattempt_on_failure => 0,1
		//return
		//cancel_return
		//notify_url
		//submit
		//submit_class
		$this->set('arr', $this->getParams());
	}

	public function pjActionGetDetails()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged() && $this->isAdmin())
		{
			if (isset($_GET['id']) && (int) $_GET['id'] > 0)
			{
				$this->set('arr', pjPaypalModel::factory()->find($_GET['id'])->getData());
			}
		}
	}
	
	public function pjActionGetPaypal()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged() && $this->isAdmin())
		{
			$pjPaypalModel = pjPaypalModel::factory();
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = $pjPaypalModel->escapeStr($_GET['q']);
				$q = str_replace(array('%', '_'), array('\%', '\_'), $q);
				$pjPaypalModel->where('t1.filename LIKE', "%$q%");
			}
				
			$column = 'dt';
			$direction = 'DESC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjPaypalModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $pjPaypalModel->select('t1.*')
				->orderBy("`$column` $direction")->limit($rowCount, $offset)->findAll()->getData();
						
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function pjActionIndex()
	{
		$this->checkLogin();

		if ($this->isAdmin())
		{
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjPaypal.js', $this->getConst('PLUGIN_JS_PATH'));
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
}
?>