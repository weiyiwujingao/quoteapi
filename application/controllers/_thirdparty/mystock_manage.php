<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深行情自选股(财经头条第三方app调用) v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Mystock_manage extends MY_Controller
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
			$this->load->model(array('_thirdparty/mystock_manage_mdl', '_thirdparty/quotes_manage_mdl', '_thirdparty/hkquotes_manage_mdl'));
            $this->$func();
		}
		else
		{
			exit;
		}
    }

   /**
     * 自选股查询-U715
	 *
     * @param integer $uid     用户ID
     * @param integer $type    股票类型 0:A股 1:港股
     * @param integer $page    页码
     * @param integer $limit   条数
	 * @param mixed   $otherid 指数ID
     * @return json
     */
    private function getMyStock() 
    {  
		$uid   = filter_slashes($this->input->get('uid', TRUE));
		$type  = intval($this->input->get('type', TRUE));
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$callback = $this->input->get('callback', TRUE);

		/* 排序字段序号 */
		$field = array('0' => 'ClosePrice', '1' => 'DiffPrice', '2' => 'DiffPriceRate');

		if(!checkRange(array($page, $limit)) || strlen($uid) != 32)
			exit;

		$myStock = $this->mystock_manage_mdl->get_stock(array('UserID' => $uid, 'Type' => $type));

		if(!empty($myStock))
		{
			$stockList = array();

			foreach($myStock as $rs) $stockList[] = $rs['StockCode'];
				
			$offset = ($page - 1) * $limit;

			$stockList = array_slice($stockList, $offset, $limit);
			
			if(!$type)
				$stockInfo = $this->quotes_manage_mdl->stockPrice($stockList);
			else
				$stockInfo = $this->hkquotes_manage_mdl->stockPrice($stockList);

			if(is_empty($stockInfo))
			{
				foreach($stockInfo['list'] as $key => $rs)
				{
					/* 序号 */
					$this->data[$key]['SeqNo']         = $rs[32017] + $offset;
					/* 股票代码 - 无后缀 */
					$this->data[$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
					/* 股票代码 - 有后缀 */
					$this->data[$key]['StockID2']      = $rs[32101];
					/* 股票名称 */
					$this->data[$key]['StockName']     = $rs[32102];
					/* 最新价 */
					$this->data[$key]['ClosePrice']    = formats($rs[32154]/1000, 2);
					/* 涨跌额 */
					$this->data[$key]['DiffPrice']     = formats($rs[32168], 2);
					/* 涨跌幅 */
					$this->data[$key]['DiffPriceRate'] = formats($rs[32169], 2);
					/* 市盈率 */
					$this->data[$key]['EPS']		   = formats($rs[32204], 2);
					/* 换手率 */
					$this->data[$key]['VolChangeRate'] = formats($rs[32175], 2);
				}
				$orderby    = isset($field[$orderby]) ? $orderby : 0;
				$ordertype  = in_array($ordertype, array(0,1)) ? $ordertype : 0;

				$this->data = array_sort($this->data, $field[$orderby], $ordertype, TRUE);
			}
			unset($stockList, $stockInfo);
		}
		returnJson($this->data, $callback);        
    }

   /**
     * 自选股操作-U713,U714
	 *
     * @param string  $sid  股票ID,多个ID逗号分隔
     * @param integer $uid  用户ID
	 * @param integer $type 操作类型 1:添加 2:删除
	 * @return json
     */
    private function operMyStock()
    {
		$uid  = filter_slashes($this->input->get('uid', TRUE));
		$sid  = filter_slashes($this->input->get('sid', TRUE));
		$type = intval($this->input->get('type', TRUE));
		$callback = $this->input->get('callback', TRUE);

		$stockid = (strlen($sid) > 7) ? explode(';', rtrim($sid, ';')) : append_suf($sid);

		if(!in_array($type, array(1,2)) || !is_code($stockid) || strlen($uid) != 32)
			exit;

		$errno = '00';

		/* 操作频率判断 */
		if($this->mystock_manage_mdl->limit_time($uid))
		{
			$errno = '01';

			$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));

			returnJson($this->data, $callback);
		}
		else
		{
			$data['Type']   = 0;
			$data['UserID'] = $uid;

			switch($type)
			{
				case '1':
					/* 支持批量添加 */
					$data['AvgBidPrice'] = 0;
					$data['GroupID']	 = 1;
					$data['ClassID']	 = 1;
					$data['MaxCnts']     = 100;
					$data['StockCodes']  = is_array($stockid) ? join(';', $stockid) : $stockid;
					$data['CostPrice']   = 0;
					$data['HoldCnts']    = 0;
					$data['Commission']  = 0;
					$data['Handling']	 = 0;

					$errno = $this->mystock_manage_mdl->insert_stock($data) ? '00' : '02';
				
					$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));
				break;
				case '2':
					/* 不支持批量删除 */
					$data['StockCode'] = $stockid;

					$errno = $this->mystock_manage_mdl->delete_stock($data) ? '00' : '04';

					$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));
				break;
			}
			unset($data);
		}
		returnJson($this->data,$callback);
    }

   /**
     * 提示信息
	 *
     * @param string $errno 错误码
	 * @return array
     */
	private function getErrorMsg($errno = '00')
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

/* End of file mystock_manage.php */
/* Location: ./application/controllers/_thirdparty/mystock_manage.php */