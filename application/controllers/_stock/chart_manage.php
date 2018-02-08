<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深行情图表数据(分时、K线) v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Chart_manage extends MY_Controller
{
	/* 数据容器 */
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
			$this->load->model(array('_stock/quotes_manage_mdl', '_stock/chart_manage_mdl'));
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

		if($days > 5 || !is_code($stockid))
			exit;

		$stockInfo = $this->quotes_manage_mdl->stockPrice($stockid);

		$rlineInfo = $this->chart_manage_mdl->rline($stockid, $days);

		if(is_empty($rlineInfo))
		{
			/* 计算均价之用 */
			$avgprice = $total = 0;
			/* 收盘价 */
			$refprice = $stockInfo['list'][0][32156] / 1000;

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
				$this->data['list'][$key][] = $nowTime;
				/* 成交价格 */
				$this->data['list'][$key][] = formats($nowPrice, 2) * 1;
				/* 均价 */
				$this->data['list'][$key][] = formats($avgprice, 2) * 1;
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
     * K线
	 *
	 * @param string  $stockid   股票代码
	 * @param integer $type      数据类别
	 * @param integer $startdate 开始日期
	 * @param integer $enddate   结束日期
	 * @param string  $callback  回调函数
	 * @return json
     */
    private function getKline() 
    {   
		$type	   = intval($this->input->get('type', TRUE));
		$stockid   = filter_slashes($this->input->get('stockid', TRUE));
		$startdate = intval($this->input->get('startdate', TRUE));
		$enddate   = intval($this->input->get('enddate', TRUE));
		$callback  = $this->input->get('callback', TRUE);
		
		$tmpid = array(1,2,3,4,5,6,10,11,12,13,14,15,16);

		if(!is_code($stockid) || !in_array($type,$tmpid) || $startdate <= 0 || $enddate <= 0 || ($enddate < $startdate))
			exit;

		$stockInfo = $this->chart_manage_mdl->kline($stockid, $type, $startdate, $enddate);

		$stockInfo = explode(';', $stockInfo);

		if(!empty($stockInfo))
		{
			$this->data['total'] = count($stockInfo);

			foreach($stockInfo as $key => $rs)
			{
				if(empty($rs)) continue;

				$kline = explode('|', $rs);

				/* 报价日期 */
                $this->data['list'][$key][] = strtotime($kline[0]);
				/* 开盘价 */
                $this->data['list'][$key][] = $kline[1] / 1000;
				/* 最高价 */
                $this->data['list'][$key][] = $kline[2] / 1000;
				/* 最低价 */
                $this->data['list'][$key][] = $kline[3] / 1000;
				/* 收盘价 */
                $this->data['list'][$key][] = $kline[4] / 1000;
				/* 成交量 */
                $this->data['list'][$key][] = formats($kline[6] / 10000, 2) * 1;
				/* 成交额 */
                $this->data['list'][$key][] = formats($kline[7] / 100000, 2) * 1;
				/* 涨跌幅 */
                $this->data['list'][$key][] = formats(($kline[4] - $kline[5]) / $kline[5] * 100, 2) * 1;
			}
			unset($stockInfo);
		}
        returnJson($this->data, $callback);
    }
} 

/* End of file chart_manage.php */
/* Location: ./application/controllers/_stock/chart_manage.php */