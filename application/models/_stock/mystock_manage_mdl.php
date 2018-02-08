<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深行情自选股模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Mystock_manage_mdl extends CI_Model
{
	/* 添加自选股 */
	const U_INSERT_STOCK = 'U713';
	/* 删除自选股 */
	const U_DELETE_STOCK = 'U714';	
	/* 查询自选股 */
	const U_SELECT_STOCK = 'U715';

    public function __construct()
	{
        parent::__construct();

        $this->load->library(array('cnfol_socket', 'cnfol_mem'));
    }

   /**
     * 获取自选股-U715
	 *
     * @param integer $where['UserID'] 用户ID
     * @param integer $where['Type']   类别 0:沪深 1:港股
	 * @return array
     */
    public function get_stock($where)
    {
		$keys = get_keys(self::U_SELECT_STOCK, $where['UserID'], $where['Type']);
        $data = $this->cnfol_mem->get($keys);
//$data = array();
        if(empty($data))
        {
            $data = $this->cnfol_socket->senddata(self::U_SELECT_STOCK, $where);

            if($data['Code'] == '00' && $data['TtlRecords'] > 0)
			{
				$data = $data['Record'];
                $this->cnfol_mem->set($keys, $data, SEVEN_DAYS);
			}
			else
			{
				$data = array();
			}
        }
        return $data;
	}

   /**
     * 增加自选股-U713
	 *
     * @param integer $data['UserID']    用户ID
     * @param integer $data['Type']      类别 0:沪深 1:港股
     * @param string  $data['StockCode'] 股票代码
     * @param float   $data['CostPrice'] 现价
     * @param integer $data['HoldCnts']  持有股数
	 * @return boolean   
     */
    public function insert_stock($data)
    {
		$result = $this->cnfol_socket->senddata(self::U_INSERT_STOCK, $data);

		/* 记录当前操作时间,限制下次操作的时间,原则上1秒内的操作全部拒绝 */
		$keys = get_keys('mystock_limit_time', $data['UserID']);

		$this->cnfol_mem->set($keys, time(), 1);

		/* 100301:已存在 */
		if($result['Code'] == '00' && $result['TtlRecords'] > 0)
        {
			$keys = get_keys(self::U_SELECT_STOCK, $data['UserID'], $data['Type']);

			$this->cnfol_mem->delete($keys);

			return TRUE;
		}
        else
        {
			return FALSE;
		}
	}

   /**
     * 修改自选股-U713
	 *
     * @param integer $data['MsgID']       记录ID
     * @param integer $data['UserID']      用户ID
     * @param integer $data['Type']        类别 0:A股 1:港股
	 * @param string  $data['StockCodes']  股票代码串,用于客户端拖拽排序
     * @param string  $data['StockIndexs'] 排序号串,用于客户端拖拽排序
     * @param string  $data['StockCode']   股票代码
     * @param float   $data['CostPrice']   现价
     * @param integer $data['HoldCnts']    持有股数
	 * @return boolean    
     */
    public function update_stock($data)
    {
		$result = $this->cnfol_socket->senddata(self::U_INSERT_STOCK,$data);

		/* 记录当前操作时间,限制下次操作的时间,原则上1秒内的操作全部拒绝 */
		$keys = get_keys('mystock_limit_time', $data['UserID']);

		$this->cnfol_mem->set($keys,time(), 1);

		/* 100301:已存在 */
		if($result['Code'] == '00' && $result['TtlRecords'] > 0)
        {
			$keys = get_keys(self::U_SELECT_STOCK, $data['UserID'], $data['Type']);
			
			$this->cnfol_mem->delete($keys);

			return TRUE;
		}
        else
        {
			return FALSE;
		}
	}

   /**
     * 删除自选股-U714
	 *
     * @param integer $where['UserID']    用户ID
	 * @param integer $where['Type']	  股票类别 0:A股 1:港股
     * @param string  $where['StockCode'] 股票代码
	 * @return boolean 
     */
    public function delete_stock($where)
    {
		$result = $this->cnfol_socket->senddata(self::U_DELETE_STOCK, $where);

		if($result['Code'] == '00' && $result['TtlRecords'] > 0)
        {
			$keys = get_keys(self::U_SELECT_STOCK, $where['UserID'], $where['Type']);
			
			$this->cnfol_mem->delete($keys);

			return TRUE;
		}
        else
        {
			return FALSE;
		}
	}

   /**
     * 根据最后一次操作时间限制操作次数
	 *
     * @param integer $userid 用户ID
	 * @return boolean 
     */
    public function limit_time($userid)
    {
		$keys = get_keys('mystock_limit_time', $userid);

        $lastTime = $this->cnfol_mem->get($keys);

        return ((time() - intval($lastTime)) <= 1) ? TRUE : FALSE;
	}
}

/* End of file mystock_manage_mdl.php */
/* Location: ./application/models/_stock/mystock_manage_mdl.php */