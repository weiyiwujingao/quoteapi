<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深即时行情模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Quotes_manage_mdl extends CI_Model 
{
	/* 个股列表 */
	const P_STOCK_LIST		 = 'P520';
	/* 板块排行 */
	const P_BLOCK_RANK		 = 'P521';
	/* 个股报价 */
	const P_STOCK_PRICE		 = 'P550';
	/* 个股排行 */
	const P_STOCK_RANK		 = 'P551';
	/* 个股五档 */
	const P_STOCK_FIVE		 = 'P556';
	/* 热门个股 */
	const P_STOCK_HOT		 = 'P567';
	/* 个股搜索 */
	const P_STOCK_SEARCH	 = 'P581';
	/* 个股分类联合查询 */
	const P_STOCK_LIST_UNION = 'P582';
	/* 全球指数 */
	const P_STOCK_GLOBAL     = 'P583';
	/* 新股搜索 */
	const P_NEWSTOCK_SEARCH  = 'P585';
	/* 个股分类排行终极版 */
	const P_STOCK_RANK_FINAL = 'P586';

    public function __construct()
	{
        parent::__construct();

		$this->load->library(array('cnfol_fix', 'cnfol_mem'));
    }

   /**
     * 个股报价-P550
	 *
     * @param mixed   $stockids 个股代码
     * @param integer $type     回复字段范围,参看文档
	 * @param integer $userid   用户ID
     * @return array
     */
    public function stockPrice($stockid, $type = 6, $userid = 0) 
    {	
		$keys = '';
		$data = array();

		if(is_array($stockid))
		{
			$stockid = join(';', $stockid);
			$keys = get_keys(self::P_STOCK_PRICE, md5($stockid), $type, $userid);
			$data = $this->cnfol_mem->get($keys);
		}
		else
		{
			$keys = get_keys(self::P_STOCK_PRICE, $stockid, $type, $userid);
			$data = $this->cnfol_mem->get($keys);
		}

		if(!is_empty($data))
		{
			$body[95] = strlen($stockid);
			$body[96] = $stockid;
			$body[32019] = $type;
			$body[32002] = $userid;

			$data = $this->cnfol_fix->getContext(self::P_STOCK_PRICE, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS));
		}
        return $data;
    }

   /**
     * 股票分类联合查询-P582
	 *
     * @param string  $cateid 分类ID,多个逗号分隔
     * @param integer $type   返回类型 0:代码 1:代码+名称
	 * @return array
     */
    public function stockListPlus($cateid, $type = 0)
    {
		$keys = get_keys(self::P_STOCK_LIST_UNION, md5($cateid), $type);
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $body[32056] = $cateid;
            $body[32019] = $type;

            $stockList = $this->cnfol_fix->getContext(self::P_STOCK_LIST_UNION, $body);

            if(!is_empty($stockList))
				return array();
			
			$data[32016] = $stockList[32016];

			foreach($stockList['list'] as $rs)
				$data['list'][] = $rs[32101];
				
            $this->cnfol_mem->set($keys, $data, ONE_DAYS);
		}
        return $data;
    }

   /**
     * 全球指数-P583
	 *
	 * @return array
     */
    public function globalStock()
    {
		$keys = get_keys(self::P_STOCK_GLOBAL);
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $data = $this->cnfol_fix->getContext(self::P_STOCK_GLOBAL);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS));
        }
        return $data;
    }

   /**
     * 新股搜索-P585
	 *
     * @param string  $keyword 关键字
	 * @param integer $offset  起始序号
	 * @param integer $limit   获取条数
     * @param integer $userid  用户ID
	 * @return array
     */
    public function newStockSearch($keyword, $offset = 0, $limit = 10, $userid = 0)
    {
		$keys = get_keys(self::P_NEWSTOCK_SEARCH, md5($keyword), $offset, $limit, $userid);
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $body[32064] = utf8_to_gbk($keyword);
            $body[32024] = $offset;
			$body[32016] = $limit;
			$body[32002] = $userid;

            $data = $this->cnfol_fix->getContext(self::P_NEWSTOCK_SEARCH, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, ONE_DAYS);
        }
        return $data;
    }

   /**
     * 热门个股-P567
	 *
	 * @param integer $offset  起始序号
	 * @param integer $limit   获取条数
	 * @param integer $userid  用户ID
	 * @return array
     */
    public function stockHot($offset = 0, $limit = 10, $userid = 0)
    {
		$keys = get_keys(self::P_STOCK_HOT, $offset, $limit, $userid); 
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
			$body[32024] = $offset;
			$body[32016] = $limit;
			$body[32002] = $userid;

            $data = $this->cnfol_fix->getContext(self::P_STOCK_HOT, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, ONE_DAYS);
		}
        return $data;
    }

   /**
     * 股票模糊搜索-P581
	 *
     * @param string  $keywork 关键字
     * @param integer $flag    股票类型标识 1:A股 2:港股
	 * @param integer $offset  起始序号
	 * @param integer $limit   获取条数
     * @param integer $userid  用户ID
	 * @return array
     */
    public function stockSearch($keyword , $flag = 0, $offset = 0, $limit = 10, $userid = 0)
    {
		$keys = get_keys(self::P_STOCK_SEARCH, md5($keyword, TRUE), $flag, $offset, $limit, $userid);
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $body[32064] = is_numeric($keyword) ? $keyword : utf8_to_gbk($keyword);
			$body[32008] = $flag;
			$body[32024] = $offset;
			$body[32016] = $limit;
			$body[32002] = $userid;

            $data = $this->cnfol_fix->getContext(self::P_STOCK_SEARCH, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, ONE_DAYS);
		}
        return $data;
    }

   /**
     * 个股排行-P551
	 *
     * @param integer $cateid 分类ID
     * @param integer $type   返回字段范围,参看文档
     * @param integer $userid 用户ID
	 * @return array
     */
    public function stockRank($cateid, $type = 6, $userid = 0)
    {
		$keys = get_keys(self::P_STOCK_RANK, $cateid, $type, $userid); 
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $body[32008] = $cateid;
            $body[32019] = $type;
			$body[32002] = $userid;

            $data = $this->cnfol_fix->getContext(self::P_STOCK_RANK, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS));
        }
        return $data;
    }
    
   /**
     * 个股排行终极版-P586
	 *
     * @param integer $cateid    分类标识 101:全部沪深 102:沪A 103:沪B 104:深A 105:深B 106:创业板 107:中小板 108:新股(已上市)
	 * @param integer $type      返回字段范围,参看文档
	 * @param integer $orderby   排序字段 0:涨跌幅 1:成交量 2:成交额 3:涨跌额 4:最新价
	 * @param integer $ordertype 排序方式 0:desc 1:asc
	 * @param integer $offset    起始序号
	 * @param integer $limit     获取条数
     * @param integer $userid    用户ID
	 * @return array
     */
    public function stockRankFinal($cateid, $type = 6, $orderby = 0, $ordertype = 0, $offset = 0, $limit = 10, $userid = 0)
    {
		$keys = get_keys(self::P_STOCK_RANK_FINAL, $cateid, $type, $orderby, $ordertype, $offset, $limit, $userid);
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

            $data = $this->cnfol_fix->getContext(self::P_STOCK_RANK_FINAL, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS));
        }
        return $data;
    }

   /**
     * 个股代码列表
	 *
     * @param integer $cateid 分类代码
     * @param integer $type   返回类型 0:股票代码 1:股票代码+名称
	 * @return array
     */
    public function stockList($cateid, $type = 0)
    {
		$keys = get_keys(self::P_STOCK_LIST, $cateid, $type);
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $body[32056] = $cateid;
            $body[32019] = $type;

            $stockList = $this->cnfol_fix->getContext(self::P_STOCK_LIST, $body);

            if(!is_empty($stockList))
				return array();

			$data[32016] = $stockList[32016];

			foreach($stockList['list'] as $rs)
				$data['list'][] = $rs[32101];

            $this->cnfol_mem->set($keys, $data, ONE_DAYS);
        }
        return $data;
    }

   /**
     * 个股买卖五档
	 *
     * @param string  $stockid 股票代码
     * @param integer $limit   档数 5:五档 10:十档 最多为10
	 * @return array
     */
    public function stockFive($stockid, $limit = 5)
    {
		$keys = get_keys(self::P_STOCK_FIVE, $stockid, $limit);
        $data = $this->cnfol_mem->get($keys);

		if(!is_empty($data))
        {
            $body[32101] = $stockid;
            $body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::P_STOCK_FIVE, $body);

            if(!is_empty($data))
				return array();

            $this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS));
        }
        return $data;
    }

   /**
     * 板块排名
	 *
     * @param integer $blockid   板块类别 0:行业板块 1:概念板块 2:地域板块
     * @param integer $orderby   排序字段,参看文档
     * @param integer $ordertype 排序方式 0:降序 1:升序
	 * @param integer $offset    起始序号
	 * @param integer $limit     获取条数
	 * @return array
     */
    public function blockRank($blockid, $orderby = 3, $ordertype = 0)
    {
		$keys = get_keys(self::P_BLOCK_RANK, $blockid, $orderby, $ordertype);
		$data = $this->cnfol_mem->get($keys);

        if(!is_empty($data)) 
        {
            $body[32100] = 'K';
            $body[32008] = $blockid;
			$body[32009] = $orderby;
            $body[32025] = $ordertype;
			//$body[32016] = $limit;

            $data = $this->cnfol_fix->getContext(self::P_BLOCK_RANK, $body);

            if(!is_empty($data))
				return array();

			$this->cnfol_mem->set($keys, $data, getOpenTime(TEN_SECONDS));
        }
        return $data;
    }
}

/* End of file quotes_manage_mdl.php */
/* Location: ./application/models/_thirdparty/quotes_manage_mdl.php */