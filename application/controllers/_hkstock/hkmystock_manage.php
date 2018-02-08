<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 港股行情自选股 v2.0 
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class HKMystock_manage extends MY_Controller
{
	/* 数据容器 */
	private $data = array();

    public function __construct()
	{
        parent::__construct();
    }
    
   /**
     * API入口
	 *
     * @param string $action 方法名称
	 * @return void
     */
    public function index() 
    {
        $func = $this->input->get('action', TRUE);

        if(TRUE === method_exists(__CLASS__, trim($func)))
		{
			$this->load->model('_hkstock/hkmystock_manage_mdl');
            $this->$func();
		}
		else
		{
			exit;
		}
    }

   /**
     * 自选股操作
	 *
     * @param integer $sid  股票ID
     * @param integer $uid  用户ID
	 * @param integer $type 操作类型 1:添加 2:删除 3:修改
	 * @return json
     */
    function operMyStock()
    {
		/* 如果用户ID不存在说明还未登录,直接操作cookie */
		//empty($this->_uid) ? $this->operMyStock2() : '';
		$uid  = intval($this->input->get('uid', TRUE));
		$sid  = filter_slashes($this->input->get('sid', TRUE));
		$type = intval($this->input->get('type', TRUE));
		$callback = $this->input->get('callback',TRUE);
		$stockid  = $sid . HK_STOCK_EXT;

		if(!in_array($type, array(1,2,3)) || !is_code($stockid, 'H'))
			exit;
		
		$errno = '00';

		/* 操作频率判断 */
		if($this->hkmystock_manage_mdl->limit_time($uid))
		{
			$errno = '01';

			$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));

			returnJson($this->data, $callback);
		}
		else
		{
			$data['Type']   = 1;
			$data['UserID'] = $uid;

			switch($type)
			{
				case '1':
					$data['AvgBidPrice'] = 0;
					$data['GroupID']	 = 1;
					$data['ClassID']	 = 1;
					$data['MaxCnts']     = 100;
					$data['StockCodes']  = $stockid;
					$data['CostPrice']   = 0;
					$data['HoldCnts']    = 0;
					$data['Commission']  = 0;
					$data['Handling']	 = 0;

					$errno = $this->hkmystock_manage_mdl->insert_stock($data) ? '00' : '02';

					$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));
				break;
				case '2':
					$data['StockCode'] = $stockid;

					$errno = $this->hkmystock_manage_mdl->delete_stock($data) ? '00' : '04';

					$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));
				break;
				case '3':/* 待重构 */
					$holdCnts = intval($this->input->get('stCnts', TRUE));
					$handling = floatval($this->input->get('stTax', TRUE));
					$avgBidPrice = floatval($this->input->get('stAvgPrice', TRUE));
					
					$data['StockCode'] = $stockid;
					
					/* 获取当前要修改的自选股记录ID */
					$row = $this->hkmystock_manage_mdl->get_stock_by_id($data);

					if(isset($row['Record'][0]['MsID']) && !empty($row['Record']))
					{
						$modifyid = $row['Record'][0]['MsID'];

						/* 买入成本 = 买入均价+(买入均价*数量*(佣金+印花税) */
						$costPrice = $avgBidPrice * $holdCnts * (0.003 + 0.001+ $handling);
						
						$data['MsID']		 = $modifyid;
						$data['HoldCnts']    = $holdCnts;
						$data['Handling']    = $handling;
						$data['CostPrice']   = $costPrice;
						$data['AvgBidPrice'] = $avgBidPrice;

						$errno = $this->hkmystock_manage_mdl->update_stock($data) ? '00' : '01';

						$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));
					}
				break;
			}
		}
		returnJson($this->data, $callback);
    }

   /**
     * 自选股操作 by cookie
	 *
     * @param string  $sid  股票代码
	 * @param integer $type 操作类型 1:添加 2:删除 3:修改
	 * @return json
     */
    function operMyStock2()
    {
		$type     = intval($this->input->get('type', TRUE));
		$stockid  = filter_slashes($this->input->get('sid', TRUE));
		$callback = $this->input->get('callback', TRUE);
		$stockid .= HK_STOCK_EXT;

		if(!in_array($type, array(1,2,3)) || !is_code($stockid, 'H'))
			exit;
		
		$errno = '00';
		
		switch($type)
		{
			case '1':
				/* 初始化cookie各节点 */
				$cookie = array(
					'name'   => 'hkmystocklist',
					'value'  => $stockid,
					'expire' => $this->config->item('cookie_expire'),
					'domain' => $this->config->item('cookie_domain'),
					'path'   => $this->config->item('cookie_path'),
					'prefix' => $this->config->item('cookie_prefix'),
					'secure' => $this->config->item('cookie_secure')
				);
				
				$stockStr = filter_slashes($this->input->cookie('hkmystocklist', TRUE));

				/* cookie中不存在自选股添加记录,就将当前添加的股票存入 */
				if(empty($stockStr))
				{
					$this->input->set_cookie($cookie);
				}
				else
				{
					$stockList = explode(',', $stockStr);

					if(!in_array($stockid, $stockList))
					{
						/* 将当前添加的股票添加到cookie最前面 */
						array_unshift($stockList, $stockid);

						/* 将打包好的股票列表转为字符串形式重写cookie */
						$cookie['value'] = join(',', $stockList);

						$this->input->set_cookie($cookie);
					}
					unset($stockList);
				}
				unset($cookie);
			break;
			/* 删除 */
			case '2':
				/* 获取cookie中股票列表 */
				$stockStr = $this->input->cookie('hkmystocklist', TRUE);

				/* 获取cookie中对应的股票自选股信息 */
				$stockQue = $this->input->cookie('hkmystockquote_'.$stockid, TRUE);

				/* 初始化cookie各节点 */
				$cookie = array(
					'name'   => 'hkmystockquote_'.$stockid,
					'value'  => '',
					'expire' => 1,
					'domain' => $this->config->item('cookie_domain'),
					'path'   => $this->config->item('cookie_path'),
					'prefix' => $this->config->item('cookie_prefix'),
					'secure' => $this->config->item('cookie_secure')
				);

				/* 如果cookie中存在股票对应的浮动盈亏数据记录,则清空之 */
				if(!empty($stockQue))
				{
					$this->input->set_cookie($cookie);
				}

				/* 如果cookie中存在当前要删除的股票代码,则删除之 */
				if(!empty($stockStr))
				{
					$stockList = explode(',', $stockStr);

					$key = array_search($stockid,$stockList);
					
					if($key >= 0) unset($stockList[$key]);
					
					$cookie['name']   = 'hkmystocklist';
					$cookie['value']  = join(',', $stockList);
					$cookie['expire'] = $this->config->item('cookie_expire');

					$this->input->set_cookie($cookie);
				}
				unset($cookie, $stockList);
			break;
			/* 修改 */
			case '3':
				$holdCnts = intval($this->input->get('stCnts', TRUE));
				$handling = floatval($this->input->get('stTax', TRUE));
				$avgBidPrice = floatval($this->input->get('stAvgPrice', TRUE));

				/* 初始化cookie各节点 */
				$cookie = array(
					'name'   => 'hkmystockquote_'.$stockid,
					'value'  => $holdCnts.','.$avgBidPrice.','.$handling,
					'expire' => $this->config->item('cookie_expire'),
					'domain' => $this->config->item('cookie_domain'),
					'path'   => $this->config->item('cookie_path'),
					'prefix' => $this->config->item('cookie_prefix'),
					'secure' => $this->config->item('cookie_secure')
				);

				/* 更新cookie中对应自选股数据 */
				$this->input->set_cookie($cookie);

				unset($cookie);
			break;
		}

		$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));

		returnJson($this->data, $callback);
    }

   /**
     * 提示信息
	 *
     * @param string $errno 错误码
	 * @return array
     */
	private function getErrorMsg($errno='00')
	{
		$lang = array
		(
			'00' => '操作成功',
			'01' => '您操作过于频繁,请稍后再试',
			'02' => '您已添加过该股票',
			'03' => '您已添加过该股票',
			'04' => '该股票不存在',
			'90' => '您没有权限访问'
		);
		return $lang[$errno];
	}
}

/* End of file hkmystock_manage.php */
/* Location: ./application/controllers/_hkstock/hkmystock_manage.php */