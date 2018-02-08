<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 港股即时行情模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class HKQuotes_manage_mdl extends CI_Model 
{
	/* 港股股票报价 */
	const HK_P_STOCK_PRICE		= 'P550';
	/* 港股股票行业列表 */
	const HK_P_INDUSTRY_LIST	= 'P562';
	/* 港股股票分类列表 */
	const HK_P_STOCK_LIST		= 'P563';
	/* A+H 比价 */
	const HK_P_STOCK_AH_LIST	= 'P566';
	/* 港股分类排行列表 */
	const HK_P_STOCK_RANK		= 'P584';
	/* 股票排行终极版 */
	const HK_P_STOCK_RANK_FINAL = 'P586';
	/* 港股行业排行 */
	const HK_P_INDUSTRY_RANK    = 'P587';
	/* 港股板块排行 */
	const HK_P_BLOCK_RANK       = 'P588';

    public function __construct()
	{
        parent::__construct();

		$this->load->library(array('cnfol_hkfix', 'cnfol_mem'));
    }

   /**
     * 港股行业排行-P587
	 *
     * @param integer $cateid    分类标识
     * @param integer $userid    用户ID
	 * @param integer $offset    其实序号
     * @param integer $type      字段范围
	 * @param integer $limit     每页条数
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @return array
     */
    public function industryRank($cateid, $type, $orderby = 0, $ordertype = 0, $offset = 0, $limit = 10, $userid = 0)
    {
		$keys = get_keys(self::HK_P_INDUSTRY_RANK, $cateid, $type, $orderby, $ordertype, $offset, $limit, $userid);
        $data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
            $body[32008] = $cateid;
			$body[32019] = $type;
            $body[32025] = $ordertype;
            $body[32009] = $orderby;
			$body[32024] = $offset;
            $body[32016] = $limit;
			$body[32002] = $userid;

            $data = $this->cnfol_hkfix->getContext(self::HK_P_INDUSTRY_RANK, $body);

            if(!is_empty($data)) return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS, 'H'));
        }
        return $data;
    }

   /**
     * 板块排行-P588
	 *
	 * @param integer $orderby   排序字段 0:普通涨幅 1:加权涨幅
	 * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @param integer $limit     条数
	 * @return array
     */
    public function blockRank($orderby = 1, $ordertype = 0, $limit = 10)
    {
		$keys = get_keys(self::HK_P_BLOCK_RANK, $orderby, $ordertype, $limit);
        $data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
            $body[32009] = $orderby;
			$body[32025] = $ordertype;
			$body[32016] = $limit;

            $data = $this->cnfol_hkfix->getContext(self::HK_P_BLOCK_RANK, $body);

			if(!is_empty($data)) return array();

			$this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS, 'H'));
        }
        return $data;
    }

   /**
     * 行情报价查询-P550
	 *
     * @param mixed   $stockid 股票列表
     * @param integer $type    查询字段范围
	 * @param integer $userid  用户ID
     * @return array
     */
    public function stockPrice($stockid, $type = 6, $userid = 0) 
    {	
		$keys = '';
		$data = array();

		if(is_array($stockid))
		{
			$stockid = join(';', $stockid);
			$keys = get_keys(self::HK_P_STOCK_PRICE, md5($stockid), $type, $userid);
			$data = $this->cnfol_mem->get($keys);
		}
		else
		{
			$keys = get_keys(self::HK_P_STOCK_PRICE, $stockid, $type, $userid);
			$data = $this->cnfol_mem->get($keys);
		}

		if(!is_empty($data))
		{
			$body[95] = strlen($stockid);
			$body[96] = $stockid;
			$body[32019] = $type;
			$body[32002] = $userid;

			$data = $this->cnfol_hkfix->getContext(self::HK_P_STOCK_PRICE, $body);

            if(!is_empty($data)) return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS, 'H'));
		}
        return $data;
    }

   /**
     * 股票列表查询-P563
	 *
     * @param integer $cateid 分类代码
     * @param integer $type   返回类型 0:股票代码 1:股票代码+名称
	 * @return array
     */
    public function stockList($cateid, $type = 0)
    {
		$keys = get_keys(self::HK_P_STOCK_LIST, $cateid, $type);
        $data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
            $body[32056] = $cateid;
            $body[32019] = $type;

            $stockInfo = $this->cnfol_hkfix->getContext(self::HK_P_STOCK_LIST, $body);

			if(is_empty($stockInfo))
			{
				$data[32016] = $stockInfo[32016];

				foreach($stockInfo['list'] as $rs) $data['list'][] = $rs[32101];

				$this->cnfol_mem->set($keys, $data, ONE_DAYS);
			}
			unset($stockInfo);
        }
        return $data;
    }

   /**
     * A+H股-P566
	 *
     * @param integer $offset 页码
     * @param integer $limit  条数
     * @return array
     */
    public function ahList($offset = 0, $limit = 10)
    {
		$keys = get_keys(self::HK_P_STOCK_AH_LIST, $offset, $limit);
        $data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
			$body[32031] = $offset;
            $body[32032] = $limit;

            $data = $this->cnfol_hkfix->getContext(self::HK_P_STOCK_AH_LIST, $body);

            if(!is_empty($data)) return array();

			$this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS, 'H'));
        }
        return $data;
    }

   /**
     * 个股排行 v1.0-P584
	 *
     * @param integer $cateid 分类代码
     * @param integer $type   返回字段范围,参看文档
     * @param integer $offset 起始序号
     * @param integer $limit  条数
	 * @return array
     */
    public function stockRank($cateid, $type = 6, $offset = 0, $limit = 10)
    {
		$keys = get_keys(self::HK_P_STOCK_RANK, $cateid, $type, $offset, $limit);
        $data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
            $body[32008] = $cateid;
            $body[32019] = $type;
			$body[32024] = $offset;
            $body[32016] = $limit;

            $data = $this->cnfol_hkfix->getContext(self::HK_P_STOCK_RANK, $body);

			if(!is_empty($data)) return array();

			$this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS, 'H'));
        }
        return $data;
    }

   /**
     * 行情排行终极版-P586
	 *
     * @param integer $cateid    分类标识
	 * @param integer $type      返回字段范围,参看文档
	 * @param integer $offset    起始序号
	 * @param integer $limit     每页条数
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
     * @param integer $userid    用户ID
	 * @return array
     */
    public function stockRankFinal($cateid, $type = 6, $orderby = 0, $ordertype = 0, $offset = 0, $limit = 10, $userid = 0)
    {
		$keys = get_keys(self::HK_P_STOCK_RANK_FINAL, $cateid, $type, $orderby, $ordertype, $offset, $limit, $userid); 
        $data = $this->cnfol_mem->get($keys);
	
        if(!is_empty($data)) 
        {
            $body[32008] = $cateid;
			$body[32019] = $type;
            $body[32025] = $ordertype;
            $body[32009] = $orderby;
			$body[32024] = $offset;
            $body[32016] = $limit;
			$body[32002] = $userid;

            $data = $this->cnfol_hkfix->getContext(self::HK_P_STOCK_RANK_FINAL, $body);

            if(!is_empty($data)) return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS, 'H'));
        }
        return $data;
    }

   /**
     * 行业列表
	 *
     * @param integer $cateid 分类代码
	 * @return array
     */
    public function industryList($cateid)
    {
		$keys = get_keys(self::HK_P_INDUSTRY_LIST, $cateid);
        $data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
            $body[32033] = $cateid;

            $stockInfo = $this->cnfol_hkfix->getContext(self::HK_P_INDUSTRY_LIST, $body);

			if(is_empty($stockInfo))
			{
				$data[32016] = $stockInfo[32016];

				foreach($stockInfo['list'] as $rs) $data['list'][] = $rs[32101];

				$this->cnfol_mem->set($keys, $data, ONE_DAYS);
			}
			unset($stockInfo);
        }
        return $data;
    }
}

/* End of file hkquotes_manage_mdl.php */
/* Location: ./application/models/_hkstock/hkquotes_manage_mdl.php */