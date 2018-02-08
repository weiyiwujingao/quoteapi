<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 港股行情分时,K线模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class HKChart_manage_mdl extends CI_Model 
{
	const HK_P_STOCK_RLINE = 'P558';
	/* 表名集合 1:日线 2:周线 3:月线 4:5分线 5:15分线 6:30分线 7:60分线 */
	private $_db_name = array(
								'1' => 'SDAY.',
								'2' => 'WEEK.',
								'3' => 'MONTH.',
								'4' => 'SMIN5.',
								'5' => 'SMIN15.',
								'6' => 'SMIN30.',
								'7' => 'SMIN60.');

    public function __construct()
	{
        parent::__construct();

		$this->load->library(array('cnfol_hkfix', 'cnfol_mem'));
    }

   /**
     * 分时数据
	 *
     * @param integer $stockid 股票代码
	 * @param integer $limit   条数
	 * @return array
     */
    public function rline($stockid, $days = 1)
    {
		$keys = get_keys(self::HK_P_STOCK_RLINE, $stockid, $days);
        $data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
            $body[32101] = $stockid;
			$body[32008] = $days;

            $data = $this->cnfol_hkfix->getContext(self::HK_P_STOCK_RLINE, $body);

            if(!is_empty($data)) return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS, 'H'));
		}
        return $data;
    }

   /**
     * K线数据
	 *
     * @param integer $stockid 股票代码
     * @param integer $type    类别 1:日线 2:周线 3:月线 4:5分线 5:15分线 6:30分线 7:60分线
	 * @param integer $limit   条数
	 * @return array
     */
    public function kline($stockid, $type = 1, $limit = 100)
    {
		$keys = get_keys($this->_db_name[$type], $stockid, $type, $limit);
		$data = $this->cnfol_mem->get($keys);

		if(empty($data))
		{
			$this->load->database();

			$this->db->select('TD as PriceTime,
							   DIVREF as RefPrice,
							   OPENP as OpenPrice,
							   CLOSEP as ClosePrice,
							   HIGHP as HighPrice,
							   LOWP as LowPrice,
							   VOL as Vol,
							   AMT as Amt')
					 ->from($this->_db_name[$type] . $stockid)
					 ->order_by('TD','DESC')
					 ->limit($limit);

			$query = $this->db->get();

			if($query->num_rows() > 0)
			{
				$data = $query->result_array();

				$this->cnfol_mem->set($keys, $data, THREE_HOURS);
			}
		}
		return $data;
    }
}

/* End of file hkchart_manage_mdl.php */
/* Location: ./application/models/_hkstock/hkchart_manage_mdl.php */