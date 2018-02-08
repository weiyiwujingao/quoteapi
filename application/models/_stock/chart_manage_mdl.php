<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深分时、K线模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Chart_manage_mdl extends CI_Model 
{
	/* 个股/指数多日分时 */
	const P_STOCK_RLINE = 'P558';
	/* 个股/指数K线 */
	const P_STOCK_KLINE = 'P510';

    public function __construct()
	{
        parent::__construct();

		$this->load->library(array('cnfol_fix', 'cnfol_mem'));
    }

   /**
     * 个股/指数多日分时-P558
	 *
     * @param string  $stockid 股票代码
     * @param integer $days    天数
	 * @return array
     */
    public function rline($stockid, $days = 1)
    {
		$keys = get_keys(self::P_STOCK_RLINE, $stockid, $days);
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $body[32101] = $stockid;
            $body[32008] = $days;

            $data = $this->cnfol_fix->getContext(self::P_STOCK_RLINE, $body);

			if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS));
		}
        return $data;
    }

   /**
     * 个股/指数K线-P510
	 *
	 * @param string  $stockid   股票代码
	 * @param integer $type      数据类别 1:日线 2:周线 3:月线 4:季线 5:半年线 6:年线 11:5分线 13:15分线 15:30分线 16:60分线
	 * @param integer $startdate 开始日期 YYYYMMDD或0
	 * @param integer $enddate   结束日期 YYYYMMDD或0
	 * @param integer $limit     获取条数
	 * @return array
     */
    public function kline($stockid, $type = 1, $startdate = 0, $enddate = 0, $limit = 10)
    {
		$keys = get_keys(self::P_STOCK_KLINE, $stockid, $type, $startdate, $enddate, $limit);
        $data = $this->cnfol_mem->get($keys);

        if(empty($data)) 
        {
            $body[32101] = $stockid;
            $body[32008] = $type;
            $body[32006] = $startdate;
            $body[32007] = $enddate;
			$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::P_STOCK_KLINE, $body);

            if(!isset($data[96]) || empty($data[96]))
				return array();

			$data = $data[96];

            $this->cnfol_mem->set($keys, $data, THREE_HOURS);
        }
        return $data;
    }
}

/* End of file chart_manage_mdl.php */
/* Location: ./application/models/_stock/chart_manage_mdl.php */