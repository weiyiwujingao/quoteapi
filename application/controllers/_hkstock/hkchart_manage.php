<?php
/****************************************************************
 * 港股行情API - 分时,K线模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class HKChart_manage extends MY_Controller
{
	/* 传送视图数据 */
	private $data = array();

    public function __construct()
	{
        parent::__construct();
    }
    
   /**
     *
     * API入口
	 *
     * @param string $action 方法名称
	 * @return void
     *
    **/
    public function index() 
    {
        $func = $this->input->get('action', TRUE);

        if(TRUE === method_exists(__CLASS__, trim($func)))
		{
			$this->load->model(array('_hkstock/hkquotes_manage_mdl','_hkstock/hkchart_manage_mdl'));
            $this->$func();
		}
		else
		{
			exit;
		}
    }

   /**
     * 单日、多日分时
	 *
     * @param string  $stockid  股票代码
     * @param integer $days     天数
     * @param integer $lastTime 最后发送时间
	 * @param string  $callback 回调函数
	 * @return json
     */
    private function getRline() 
    {
		$days     = intval($this->input->get('days', TRUE));
		$lastTime = intval($this->input->get('lasttime', TRUE));
		$stockid  = filter_slashes($this->input->get('stockid', TRUE));
		$callback = $this->input->get('callback', TRUE);
		$stockid  = $stockid . HK_STOCK_EXT;

		if($days > 5 || !is_code($stockid, 'H'))
			exit;

		$stockInfo = $this->hkquotes_manage_mdl->stockPrice($stockid);

		$rlineInfo = $this->hkchart_manage_mdl->rline($stockid);

		if(is_empty($rlineInfo))
		{
			/* 计算均价之用 */
			$avgprice = $total = 0;

			$refprice = $stockInfo['list'][0][32156] / 1000;

			/* 收盘价 */
			$this->data['stock'] = $refprice;
			
			foreach($rlineInfo['list'] as $key => $rs)
			{
				$nowTime  = strtotime($rs[32150]);
				$nowPrice = $rs[32154] / 1000;

				$total += $nowPrice;
				$avgprice = $total / ($key + 1);

				/* 计算涨跌幅 (现价-昨收)/昨收*100 */
				$diffPriceRate = ($nowPrice - $refprice) / $refprice * 100;

				/* 根据指定的时间返回最新数据 */
				if($lastTime > 0 && $lastTime >= $nowTime)
					continue;
				
				/* 成交时间 */
				$this->data['list'][$key][] = $nowTime * 1;
				/* 成交价格 */
				$this->data['list'][$key][] = formats($nowPrice, 2) * 1;
				/* 均价 */
				$this->data['list'][$key][] = formats($avgprice) * 1;
				/* 涨跌幅 */
				$this->data['list'][$key][] = formats($diffPriceRate, 2) * 1;
				/* 成交量(手) */
				$this->data['list'][$key][] = $rs[32165] * 1;
				/* 成交额(万) */
				$this->data['list'][$key][] = formats($rs[32166]/10000, 2) * 1;
				/* 主买主卖标识 */
				$this->data['list'][$key][] = $rs[32008] * 1;
			}
			unset($stockInfo, $rlineInfo);
		}
        returnJson($this->data, $callback);
    }

   /**
     * 获取 1:日线 2:周线 3:月线 4:5分线 5:15分线 6:30分线 7:60分线
	 *
     * @param integer $stockid  股票代码
     * @param integer $type     类别
     * @param integer $limit    条数
	 * @param string  $callback 回调函数
	 * @return json
     */
    private function getKline() 
    {		
		$type     = intval($this->input->get('type', TRUE));
		$limit    = intval($this->input->get('limit', TRUE));
		$stockid  = filter_slashes($this->input->get('stockid', TRUE));
		$callback = $this->input->get('callback', TRUE);
		$stockid  = $stockid . HK_STOCK_EXT;

		if(!is_code($stockid, 'H'))
			exit;

		$kline = $this->hkchart_manage_mdl->kline($stockid, $type, $limit);
		
		if(!empty($kline))
		{
			foreach($kline as $key => $rs)
			{
				/* 报价时间 */
                $this->data[$key][] = strtotime($rs['PriceTime']) * 1000;
				/* 开盘价 */
                $this->data[$key][] = formats($rs['OpenPrice']/1000, 2) * 1;
				/* 最高价 */
                $this->data[$key][] = formats($rs['HighPrice']/1000, 2) * 1;
				/* 最低价 */
                $this->data[$key][] = formats($rs['LowPrice']/1000, 2) * 1;
				/* 收盘价 */
                $this->data[$key][] = formats($rs['ClosePrice']/1000, 2) * 1;
				/* 开盘价 */
                $this->data[$key][] = formats($rs['RefPrice']/1000, 2) * 1;
				/* 成交量 */
                $this->data[$key][] = formats($rs['Vol'], 2) * 1;
				/* 成交金额  */
                $this->data[$key][] = formats($rs['Amt']/10000, 2) * 1;
				/* 涨跌标识 */
                $this->data[$key][] = ($rs['ClosePrice'] > $rs['OpenPrice']) ? 1 : -1;
				/* 正常日期 */
				$this->data[$key][] = $rs['PriceTime'];
			}
			unset($kline);
		}
		returnJson(array_reverse($this->data), $callback);
    }
} 

/* End of file hkchart_manage.php */
/* Location: ./application/controllers/_hkstock/hkchart_manage.php */