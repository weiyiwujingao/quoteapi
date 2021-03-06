<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深行情F10模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class F10_manage_mdl extends CI_Model 
{
	/* 新股上市列表 */
	const F_XGLB  = 'F001';
	/* 发行上市 */
	const F_FXSS  = 'F010';
	/* 预批露时间 */
	const F_YPLSJ = 'F047';
	/* 送转比例排行 */
	const F_SZBL  = 'F049';
	/* 业绩报表 */
	const F_YJBB  = 'F071';
	/* 业绩快报 */
	const F_YJKB  = 'F072';
	/* 业绩预告 */
	const F_YJYG  = 'F073';
	/* 分配预告 */
	const F_FPYG  = 'F074';
	/* 沪深公告列表 */
	const F_GGLB  = 'F106';

    public function __construct()
	{
        parent::__construct();

		$this->load->library(array('cnfol_fix', 'cnfol_file'));

		$this->cnfol_fix->setOptions(config_item('cnfolf10'));
    }

   /**
     * 沪深公告列表-F106
	 *
     * @param mixed  $stockid 股票代码
     * @param string $limit   获取条数
	 * @return array
     */
    public function noticeList($stockid, $limit = 10)
    {
		$stockid = is_array($stockid) ? join(';', $stockid) : $stockid;

		$keys = get_keys(self::F_GGLB, md5($stockid), $limit);
        $data = $this->cnfol_file->get($keys, 'noticeList');

		if(!is_empty($data))
        {
            $body[32101] = $stockid;
            $body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_GGLB, $body);

            if(!is_empty($data))
				return array();
			
            $this->cnfol_file->set($keys, $data, 'noticeList', ONE_MONTHS);
        }
        return $data;
    }

   /**
     * 发行上市明细-F010
	 *
     * @param string $stockid 股票代码
	 * @return array
     */
    public function stockListedDetail($stockid)
    {
		$keys = get_keys(self::F_FXSS, $stockid); 
        $data = $this->cnfol_file->get($keys, 'stockListedDetail');

        if(empty($data)) 
        {
            $body[32101] = $stockid;

            $data = $this->cnfol_fix->getContext(self::F_FXSS, $body);

            if(empty($data)) return array();

            $this->cnfol_file->set($keys, $data, 'stockListedDetail', ONE_MONTHS);
        }
        return $data;
    }

   /**
     * 新股上市列表-F001
	 *
     * @param integer $datatype  类别 0:全部 1:上海主板 2:中小板 3:创业板
     * @param string  $stockid   股票代码
     * @param integer $orderby   排序字段,参看文档
     * @param integer $ordertype 排序方式 0:倒序 1:升序
     * @param integer $offset    起始序号
	 * @param integer $limit     获取条数
	 * @return array
     */
    public function newStockList($datatype, $stockid = '', $orderby = 0, $ordertype = 0, $offset = 0, $limit = 200)
    {
		$keys = get_keys(self::F_XGLB, $datatype, $orderby, $ordertype, $offset, $limit, $stockid);
        $data = $this->cnfol_file->get($keys, 'newStockList');

        if(!is_empty($data)) 
        {
            $body[32008] = $datatype;
			$body[32101] = $stockid;
			$body[32009] = $orderby;
			$body[32025] = $ordertype;
			$body[32024] = $offset;
			$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_XGLB, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_file->set($keys, $data, 'newStockList', ONE_MONTHS);
        }
        return $data;
    }

   /**
     * 业绩报表-F071
	 *
	 * @param integer $datatype  类别 0:所有股票 1:沪A 2:深A 3:创业板 4:中小板 5:沪B 6:深B 7:三板
     * @param string  $stockid   股票代码
     * @param string  $date      报告日期 YYYY-MM-DD
     * @param integer $orderby   排序字段,0:公告日期 1:每股收益 2:每股净资产 3:净利润 4:净资产收益率 5:主营业务收入增长 6:营业利润增长 7:每股现金流
     * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @return array
     */
    public function yjbb($datatype, $stockid = '', $date = '', $orderby = 0, $ordertype = 0)
    {
		$keys = get_keys(self::F_YJBB, $datatype, $orderby, $ordertype, $date, $stockid);
        $data = $this->cnfol_file->get($keys, 'yjbb');

        if(!is_empty($data)) 
        {
			$body[32008] = $datatype;
			$body[32010] = $stockid;
			$body[32011] = $date;
			$body[32009] = $orderby;
			$body[32025] = $ordertype;
			//$body[32024] = $offset;
			//$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_YJBB, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_file->set($keys, $data, 'yjbb', ONE_MONTHS);
        }
        return $data;
    }

   /**
     * 业绩快报-F072
	 *
     * @param string  $stockid   股票代码
     * @param string  $date      报告日期 YYYY-MM-DD
     * @param integer $orderby   排序字段,0:公告日期 1:每股收益 2:营业收入 3:去年同期营业收入 4:净利润 5:去年同期净利润 6:每股净资产 7:净资产收益率
     * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @return array
     */
    public function yjkb($stockid = '', $date = '', $orderby = 0, $ordertype = 0)
    {
		$keys = get_keys(self::F_YJKB, $orderby, $ordertype, $date, $stockid);
        $data = $this->cnfol_file->get($keys, 'yjbb');

        if(!is_empty($data)) 
        {
			$body[32010] = $stockid;
			$body[32011] = $date;
			$body[32009] = $orderby;
			$body[32025] = $ordertype;
			//$body[32024] = $offset;
			//$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_YJKB, $body);

            if(!is_empty($data))
				return array();
                
			$this->cnfol_file->set($keys, $data, 'yjkb', ONE_MONTHS);
        }
        return $data;
    }

   /**
     * 业绩预告-F073
	 *
	 * @param integer $datatype  类别 0:全部 1:预增 2:预减 3:预盈 4:预降 5:预升 6:减亏 7:其他
     * @param string  $stockid   股票代码
     * @param string  $date      报告日期 YYYY-MM-DD
     * @param integer $orderby   排序字段,0:公告日期 1:上年同期每股收益
     * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @return array
     */
    public function yjyg($datatype = 0, $stockid = '', $date = '', $orderby = 0, $ordertype = 0)
    {
		$keys = get_keys(self::F_YJYG, $datatype, $orderby, $ordertype, $date, $stockid);
        $data = $this->cnfol_file->get($keys, 'yjyg');

        if(!is_empty($data)) 
        {
			$body[32008] = $datatype;
			$body[32010] = $stockid;
			$body[32011] = $date;
			$body[32009] = $orderby;
			$body[32025] = $ordertype;
			//$body[32024] = $offset;
			//$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_YJYG, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_file->set($keys, $data, 'yjyg', HALF_DAYS);
        }
        return $data;
    }

   /**
     * 分配预告-F074
	 *
     * @param string  $stockid   股票代码
     * @param string  $date      报告日期 YYYY-MM-DD
     * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @return array
     */
    public function fpyg($stockid = '', $date = '', $ordertype = 0)
    {
		$keys = get_keys(self::F_FPYG, $ordertype, $date, $stockid);
        $data = $this->cnfol_file->get($keys, 'fpyg');

        if(!is_empty($data)) 
        {
			$body[32010] = $stockid;
			$body[32011] = $date;
			$body[32025] = $ordertype;
			//$body[32024] = $offset;
			//$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_FPYG, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_file->set($keys, $data, 'fpyg', ONE_MONTHS);
        }
        return $data;
    }

   /**
     * 预批露时间-F047
	 *
     * @param string  $stockid   股票代码
     * @param string  $date      报告日期 YYYY-MM-DD
     * @param integer $orderby   排序字段,0:首次预约披露日期 1:股票名称 2:首次预约披露日期 3:一次变更日 4:二次变更日 5:三次变更日 6:实际披露日期 7:股票代码
     * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @return array
     */
    public function yplsj($stockid = '',$date = '', $orderby = 0, $ordertype = 0)
    {
		$keys = get_keys(self::F_YPLSJ, $orderby, $ordertype, $date, $stockid);
        $data = $this->cnfol_file->get($keys, 'yplsj');

        if(!is_empty($data)) 
        {
			$body[32101] = $stockid;
			$body[32018] = $date;
			$body[32009] = $orderby;
			$body[32025] = $ordertype;
			//$body[32024] = $offset;
			//$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_YPLSJ, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_file->set($keys, $data, 'yplsj', ONE_MONTHS);
        }
        return $data;
    }

   /**
     * 送转比例排行-F049
	 *
	 * @param integer $type      类别 0:送转比例排行 1:现金分红排行
     * @param string  $stockid   股票代码
     * @param string  $date      报告日期 YYYY-MM-DD
     * @param integer $orderby   排序字段 0:公告日期 1:股票名称 2:分配股本基数 3:利润分配 4:现金分红总额 5:送股比例 省略...
     * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @return array
    **/
    public function szbl($type, $stockid = '', $date = '', $orderby = 0, $ordertype = 0)
    {
		$keys = get_keys(self::F_SZBL, $type, $orderby, $ordertype, $date, $stockid);
        $data = $this->cnfol_file->get($keys, 'szbl');

        if(!is_empty($data)) 
        {
			$body[32019] = $type;
			$body[32101] = $stockid;
			$body[32018] = $date;
			$body[32009] = $orderby;
			$body[32025] = $ordertype;
			//$body[32024] = $offset;
			//$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::F_SZBL, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_file->set($keys, $data, 'szbl', ONE_MONTHS);
        }
        return $data;
    }
}

/* End of file f10_manage_mdl.php */
/* Location: ./application/models/_stock/f10_manage_mdl.php */