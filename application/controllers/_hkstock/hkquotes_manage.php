<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 港股即时行情 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class HKQuotes_manage extends MY_Controller
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
			$this->load->model(array('_stock/quotes_manage_mdl', '_hkstock/hkquotes_manage_mdl'));
            $this->$func();
		}
		else
		{
			exit;
		}
	}

   /**
     * 港股行业排行-P587
	 *
	 * @param integer $cateid    行业ID
	 * @param integer $userid    用户ID
	 * @param integer $page      页码
	 * @param integer $limit     条数
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getIndustryRank()
	{
		$cateid    = intval($this->input->get('cateid', TRUE));
		$userid    = intval($this->input->get('userid', TRUE));
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$page	   = intval($this->input->get('page', TRUE));
		$limit	   = intval($this->input->get('limit', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		$tmpid = array(
			/* 排序字段ID */
			'orderbyid'   => array(0,1,2,3,4),
			/* 排序方式ID */
			'ordertypeid' => array(0,1),
			/* 行业ID */
			'industryid'  => array(301,310,311,312,313,314,315,316,317,318,319,302,320,321,303,304,305,306,307,308,309));

		if(!checkRange(array($page, $limit)) || !in_array($orderby, $tmpid['orderbyid']) || !in_array($ordertype, $tmpid['ordertypeid']) || !in_array($cateid, $tmpid['industryid']))
			exit;
		
		$offset = ($page - 1) * $limit;

		$stockInfo = $this->hkquotes_manage_mdl->industryRank($cateid, 6, $orderby, $ordertype, $offset, $limit,  $userid);

		if(is_empty($stockInfo))
		{
			$this->data['total'] = $stockInfo[32016];

			foreach($stockInfo['list'] as $key => $rs)
			{
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']      = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']     = $rs[32102];
				/* 最新价 */
				$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 3);
				/* 涨跌额 */
				$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2);
				/* 涨跌幅 */
				$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2);
				/* 开盘价 */
				$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000, 3);
				/* 昨收价 */
				$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000, 3);
				/* 最高价 */
				$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000, 3);
				/* 最低价 */
				$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000, 3);
				/* 量比 */
				$this->data['list'][$key]['VolRate']	   = formats($rs[32174]/100, 2);
				/* 成交总额 */
				$this->data['list'][$key]['Amt']		   = formats($rs[32166]/10000, 2);
				/* 成交总量 */
				$this->data['list'][$key]['Vol']		   = formats($rs[32165], 0);
				/* 换手率 */
				$this->data['list'][$key]['VolChangeRate'] = formats($rs[32175], 2);
				/* 振幅 */
				$this->data['list'][$key]['HLDiffRate']	   = formats($rs[32170], 2);
				/* 市盈率 */
				$this->data['list'][$key]['EPS']		   = formats($rs[32204], 2);
				/* 自选股标识 */
				$this->data['list'][$key]['Flag']		   = $rs[32042];
			}
			unset($tmpid, $stockInfo);
		}		
		returnJson($this->data, $callback);
	}

   /**
     * 板块排行-P588
	 *
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param integer $limit     条数
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getBankRank()
	{
		$orderby   = intval($this->input->get('orderby',TRUE));
		$ordertype = intval($this->input->get('ordertype',TRUE));
		$limit	   = intval($this->input->get('limit',TRUE));
		$callback  = $this->input->get('callback',TRUE);

		if(!in_array($orderby, array(0,1)) || !in_array($ordertype, array(0,1)) || !checkRange($limit))
			exit;

		$stockInfo = $this->hkquotes_manage_mdl->blockRank($orderby, $ordertype, $limit);

		if(is_empty($stockInfo))
		{
			$this->data['total'] = 21;

			foreach($stockInfo['list'] as $key => $rs)
			{
				/* 板块ID */
				$this->data['list'][$key]['BlockID']    = $rs[32104];
				/* 板块名称 */
				$this->data['list'][$key]['BlockName']  = $rs[32105];
				/* 加权涨跌幅 */
				$this->data['list'][$key]['RDiffRate']  = formats($rs[32301], 2);
				/* 领涨/领跌股票代码(无后缀) */
                $this->data['list'][$key]['StockID']    = $ordertype ? substr($rs[32107], 0, strlen($rs[32107])-1) : substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 领涨/领跌股票代码(有后缀) */
                $this->data['list'][$key]['StockID2']   = $ordertype ? $rs[32107] : $rs[32101];
				/* 领涨/领跌股票名称 */
                $this->data['list'][$key]['StockName']  = $ordertype ? $rs[32108] : $rs[32102];
				/* 领涨/领跌股票报价 */
                $this->data['list'][$key]['ClosePrice'] = $ordertype ? formats($rs[32156]/1000, 2) : formats($rs[32154]/1000, 2);
				/* 领涨/领跌股票涨跌幅 */
                $this->data['list'][$key]['DiffPriceRate2'] = $ordertype ? formats($rs[32223], 2) : formats($rs[32208], 2);
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 个股报价-P550
	 *
     * @param string $sid      股票代码,多个以";"隔开"
	 * @param string $callback 回调函数
	 * @return json
     */
    private function getStockPrice() 
    {   
		$stockStr = filter_slashes($this->input->get('sid', TRUE));
		$callback = $this->input->get('callback', TRUE);

		$stockList = explode(';', rtrim($stockStr, ';'));

		if(empty($stockList))
			exit;

		/* 超过50支直接退回 */
		if(count($stockList) > 50) returnJson($this->data);

		$stockInfo = $this->hkquotes_manage_mdl->stockPrice($stockList);

		if(is_empty($stockInfo))
		{
			foreach($stockInfo['list'] as $key => $rs)
			{
				/* 序号 */
				$this->data[$key]['SeoNo']         = $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data[$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data[$key]['StockID2']      = $rs[32101];
				/* 股票名称 */
				$this->data[$key]['StockName']     = $rs[32102];
				/* 最新价 */
				$this->data[$key]['ClosePrice']    = formats($rs[32154]/1000, 2, '0.00');
				/* 涨跌额 */
				$this->data[$key]['DiffPrice']     = formats($rs[32168], 2, '0.00');
				/* 涨跌幅 */
				$this->data[$key]['DiffPriceRate'] = formats($rs[32169], 2, '0.00');
				/* 振幅 */
				$this->data[$key]['HLDiffRate']    = formats($rs[32170], 2, '0.00');
				/* 开盘价 */
				$this->data[$key]['OpenPrice']	   = formats($rs[32151]/1000, 2, '0.00');
				/* 昨收价 */
				$this->data[$key]['RefPrice']      = formats($rs[32156]/1000, 2, '0.00');
				/* 最高价 */
				$this->data[$key]['HighPrice']     = formats($rs[32152]/1000, 2, '0.00');
				/* 最低价 */
				$this->data[$key]['LowPrice']      = formats($rs[32153]/1000, 2, '0.00');
				/* 量比 */
				$this->data[$key]['VolRate']	   = formats($rs[32174]/100, 2, '0.00');
				/* 成交总额 */
				$this->data[$key]['Amt']		   = formats($rs[32165]/10000, 2, '0.00');
				/* 成交总量 */
				$this->data[$key]['Vol']		   = formats($rs[32165], 2, '0.00');
				/* 换手率 */
				$this->data[$key]['VolChangeRate'] = formats($rs[32175], 2, '0.00');
				/* 市盈率 */
				$this->data[$key]['EPS']		   = formats($rs[32204], 2, '0.00');
				/* 内盘 */
				$this->data[$key]['InVol']		   = formats($rs[32171]/10000, 2, '0.00');
				/* 外盘 */
				$this->data[$key]['OutVol']		   = formats($rs[32172]/10000, 2, '0.00');
			}
			unset($stockList, $stockInfo);
		}
        returnJson($this->data, $callback);
    }

   /**
     * 获取用户最近访问的股票
	 *
     * @param integer $type     是否返回报价 0:不返回 1:返回
	 * @param integer $page     页码
     * @param integer $limit    条数
	 * @param string  $callback 回调函数
	 * @return json
     */
    private function getZjfwg() 
    {   
		$type  = intval($this->input->get('type', TRUE));
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$stockid  = filter_slashes($this->input->cookie('hkzjfwglist', TRUE));
		$callback = $this->input->get('callback', TRUE);
		
		if(!checkRange(array($page, $limit)))
			exit;
		
		$offset = ($page - 1) * $limit;

		$stockList['list'] = empty($stockid) ? array() : explode(',', $stockid);

		if(is_empty($stockList))
		{
			$offset = ($page - 1) * $limit;

			$stockList = array_slice($stockList['list'], $offset, $limit);
			/* 判断是否返回报价 */
			if($type)
			{
				$stockInfo = $this->hkquotes_manage_mdl->stockPrice($stockList);

				if(is_empty($stockInfo))
				{
					foreach($stockInfo['list'] as $key => $rs)
					{
						/* 序号 */
						$this->data[$key]['SeqNo']         = $offset + $rs[32017];
						/* 股票代码 - 无后缀 */
						$this->data[$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
						/* 股票代码 - 有后缀 */
						$this->data[$key]['StockID2']      = $rs[32101];
						/* 股票名称 */
						$this->data[$key]['StockName']     = $rs[32102];
						/* 最新价 */
						$this->data[$key]['ClosePrice']    = formats($rs[32154]/1000, 2);
						/* 昨收价 */
						$this->data[$key]['RefPrice']      = formats($rs[32156]/1000, 2);
						/* 涨跌额 */
						$this->data[$key]['DiffPrice']     = formats($rs[32168], 2);
						/* 涨跌幅 */
						$this->data[$key]['DiffPriceRate'] = formats($rs[32169], 2);
					}
				}
				unset($stockList, $stockInfo);
			}
			else
			{
				$this->data = $stockList;
				unset($stockList);
			}
		}
        returnJson($this->data, $callback);
    }

   /**
     * 根据分类获取对应股票列表以及报价-P563
	 *
	 * @param integer $cateid    分类ID
	 * @param integer $type      是否返回报价
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getStockList()
	{
		$cateid    = intval($this->input->get('cateid', TRUE));
		$type      = intval($this->input->get('type', TRUE));
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$page	   = intval($this->input->get('page', TRUE));
		$limit	   = intval($this->input->get('limit', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		/* 排序字段序号 */
		$field = array('0'=>'ClosePrice','1'=>'DiffPrice','2'=>'DiffPriceRate','3'=>'OpenPrice','4'=>'RefPrice','5'=>'HighPrice','6'=>'LowPrice','7'=>'SVOL','8'=>'VolRate','9'=>'Amt','10'=>'Vol','11'=>'VolChangeRate','12'=>'HLDiffRate','13'=>'EPS');

		/* 获取分类ID及排序字段ID */
		$tmpid = array(
			'orderbyid'   => array(0,1,2,3,4,5,6,7,8,9,10,11,12,13),
			'ordertypeid' => array(0,1));

		if(!in_array($cateid, config_item('hk_classid')) || !in_array($orderby, $tmpid['orderbyid']) || !in_array($ordertype, $tmpid['ordertypeid']) || !checkRange(array($page, $limit)))
			exit;

		$stockList = $this->hkquotes_manage_mdl->stockList($cateid);

		if(is_empty($stockList))
		{
			$this->data['total'] = 300;
			$stockList['list']   = array_slice($stockList['list'], 0, 300);

			if($type)
			{
				$stockInfo = $this->hkquotes_manage_mdl->stockPrice($stockList['list']);
				
				if(is_empty($stockInfo))
				{
					foreach($stockInfo['list'] as $key => $rs)
					{
						/* 股票代码 - 无后缀 */
						$this->data['list'][$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
						/* 股票代码 - 有后缀 */
						$this->data['list'][$key]['StockID2']      = $rs[32101];
						/* 股票名称 */
						$this->data['list'][$key]['StockName']     = $rs[32102];
						/* 最新价 */
						$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 3);
						/* 涨跌额 */
						$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2);
						/* 涨跌幅 */
						$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2);
						/* 开盘价 */
						$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000, 3);
						/* 昨收价 */
						$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000, 3);
						/* 最高价 */
						$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000, 3);
						/* 最低价 */
						$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000, 3);
						/* 量比 */
						$this->data['list'][$key]['VolRate']	   = formats($rs[32174]/100, 2);
						/* 成交总额 */
						$this->data['list'][$key]['Amt']		   = formats($rs[32166]/10000, 2);
						/* 成交总量 */
						$this->data['list'][$key]['Vol']		   = formats($rs[32165]);
						/* 换手率 */
						$this->data['list'][$key]['VolChangeRate'] = formats($rs[32175], 2);
						/* 振幅 */
						$this->data['list'][$key]['HLDiffRate']	   = formats($rs[32170], 2);
						/* 市盈率 */
						$this->data['list'][$key]['EPS']		   = formats($rs[32204], 2);
					}
					$this->data['list'] = array_sort($this->data['list'], $field[$orderby], $ordertype, TRUE);
				}
				unset($stockList, $stockInfo);
			}
			else
			{
				$this->data['list'] = $stockList['list'];
				unset($stockList);
			}
			$offset = ($page - 1) * $limit;
			$this->data['list'] = array_slice($this->data['list'], $offset, $limit);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 个股排行 v1.0-P584
	 *
	 * @param integer $cateid   分类ID
	 * @param integer $page     排序字段
	 * @param integer $limit    排序方式
	 * @param string  $callback 回调函数
	 * @return json
     */
	private function getStockRank()
	{
		$cateid    = intval($this->input->get('cateid', TRUE));
		$page	   = intval($this->input->get('page', TRUE));
		$limit	   = intval($this->input->get('limit', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		if(!in_array($cateid, config_item('hk_rankid')) || !checkRange(array($page, $limit)))
			exit;

		$offset = ($page - 1) * $limit;

		$stockInfo = $this->hkquotes_manage_mdl->stockRank($cateid, 6, $offset, $limit);

		if(is_empty($stockInfo))
		{
			foreach($stockInfo['list'] as $key => $rs)
			{
				$key-=1;
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']      = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']     = $rs[32102];
				/* 最新价 */
				$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 3);
				/* 涨跌额 */
				$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2);
				/* 涨跌幅 */
				$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2);
				/* 开盘价 */
				$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000, 3);
				/* 昨收价 */
				$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000, 3);
				/* 最高价 */
				$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000, 3);
				/* 最低价 */
				$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000, 3);
				/* 量比 */
				$this->data['list'][$key]['VolRate']	   = formats($rs[32174]/100, 2);
				/* 成交总额 */
				$this->data['list'][$key]['Amt']		   = formats($rs[32166]/10000, 2);
				/* 成交总量 */
				$this->data['list'][$key]['Vol']		   = formats($rs[32165]);
				/* 换手率 */
				$this->data['list'][$key]['VolChangeRate'] = formats($rs[32175], 2);
				/* 振幅 */
				$this->data['list'][$key]['HLDiffRate']	   = formats($rs[32170], 2);
				/* 市盈率 */
				$this->data['list'][$key]['EPS']		   = formats($rs[32204], 2);
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 个股排行 v2.0-P586
	 *
     * @param integer $cateid    分类ID
     * @param integer $userid    用户ID
	 * @param integer $page      页码
	 * @param integer $limit     每页条数
	 * @param integer $orderby   排序字段
	 * @param string  $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getStockRankFinal()
	{  
		$cateid  = intval($this->input->get('cateid', TRUE));
		$userid  = intval($this->input->get('userid', TRUE));
		$page    = intval($this->input->get('page', TRUE));
		$limit   = intval($this->input->get('limit', TRUE));
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		/* 获取分类ID及排序字段ID */
		$tmpid = array(
			'cateid'      => array(201,202,203,204,205,206,207),
			'orderbyid'   => array(0,1,2,3,4),
			'ordertypeid' => array(0,1));

		if(!in_array($cateid, $tmpid['cateid']) || !in_array($orderby, $tmpid['orderbyid']) || !in_array($ordertype, $tmpid['ordertypeid']) || !checkRange(array($page, $limit)))
			exit;
		
		$offset = ($page - 1) * $limit;

		$stockInfo = $this->hkquotes_manage_mdl->stockRankFinal($cateid, 6, $orderby, $ordertype, $offset, $limit, $userid);

		if(is_empty($stockInfo))
		{
			$this->data['total'] = $stockInfo[32016];

			foreach($stockInfo['list'] as $key => $rs)
			{
				/* 序号 */
				$this->data['list'][$key]['SeqNo']         = $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']      = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']     = $rs[32102];
				/* 最新价 */
				$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 2, '0.00');
				/* 涨跌额 */
				$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2, '0.00');
				/* 涨跌幅 */
				$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2, '0.00');
				/* 开盘价 */
				$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000, 2, '0.00');
				/* 昨收价 */
				$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000, 2, '0.00');
				/* 最高价 */
				$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000, 2, '0.00');
				/* 最低价 */
				$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000, 2, '0.00');
				/* 量比 */
				$this->data['list'][$key]['VolRate']	   = formats($rs[32174]/100, 2, '0.00');
				/* 成交总额(亿) */
				$this->data['list'][$key]['Amt']		   = formats($rs[32166]/10000, 2, '0.00');
				/* 成交总量(万) */
				$this->data['list'][$key]['Vol']		   = formats($rs[32165], 2, '0.00');
				/* 换手率 */
				$this->data['list'][$key]['VolChangeRate'] = formats($rs[32175], 2, '0.00');
				/* 振幅 */
				$this->data['list'][$key]['HLDiffRate']	   = formats($rs[32170], 2, '0.00');
				/* 市盈率 */
				$this->data['list'][$key]['EPS']		   = formats($rs[32204], 2, '0.00');
				/* 内盘 */
				$this->data['list'][$key]['InVol']		   = $rs[32171];
				/* 外盘 */
				$this->data['list'][$key]['OutVol']		   = $rs[32172];
				/* 自选股标识 */
				$this->data['list'][$key]['Flag']		   = $rs[32042];
			}
			unset($tmpid, $stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 根据行业分类获取对应股票列表以及报价-P562
	 *
	 * @param integer $cateid    行业ID
	 * @param integer $type      是否返回报价
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getIndustryList()
	{
		$cateid    = intval($this->input->get('cateid', TRUE));
		$type      = intval($this->input->get('type', TRUE));
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$page	   = intval($this->input->get('page', TRUE));
		$limit	   = intval($this->input->get('limit', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		$config = config_item('hk_industryid');

		/* 获取分类ID及排序字段ID */
		$tmpid = array(
			'orderbyid'   => array(0,1,2,3,4,5,6,7,8,9,10,11,12,13),
			'ordertypeid' => array(0,1));

		/* 排序字段序号 */
		$field = array('0'=>'ClosePrice','1'=>'DiffPrice','2'=>'DiffPriceRate','3'=>'OpenPrice','4'=>'RefPrice','5'=>'HighPrice','6'=>'LowPrice','7'=>'SVOL','8'=>'VolRate','9'=>'Amt','10'=>'Vol','11'=>'VolChangeRate','12'=>'HLDiffRate','13'=>'EPS');

		if(!isset($config[$cateid]) || !in_array($orderby, $tmpid['orderbyid']) || !in_array($ordertype, $tmpid['ordertypeid']) || !checkRange(array($page, $limit)))
			exit;

		$stockList = $this->hkquotes_manage_mdl->industryList($cateid);

		if(is_empty($stockList))
		{
			$this->data['total'] = count($stockList[32016]);

			if($type)
			{
				$stockInfo = $this->hkquotes_manage_mdl->stockPrice($stockList['list']);
				
				if(is_empty($stockInfo))
				{
					foreach($stockInfo['list'] as $key => $rs)
					{
						/* 股票代码 - 无后缀 */
						$this->data['list'][$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
						/* 股票代码 - 有后缀 */
						$this->data['list'][$key]['StockID2']      = $rs[32101];
						/* 股票名称 */
						$this->data['list'][$key]['StockName']     = $rs[32102];
						/* 最新价 */
						$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 3);
						/* 涨跌额 */
						$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2);
						/* 涨跌幅 */
						$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2);
						/* 开盘价 */
						$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000, 3);
						/* 昨收价 */
						$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000, 3);
						/* 最高价 */
						$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000, 3);
						/* 最低价 */
						$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000, 3);
						/* 量比 */
						$this->data['list'][$key]['VolRate']	   = formats($rs[32174]/100, 2);
						/* 成交总额 */
						$this->data['list'][$key]['Amt']		   = formats($rs[32166]/10000, 2);
						/* 成交总量 */
						$this->data['list'][$key]['Vol']		   = formats($rs[32165]);
						/* 换手率 */
						$this->data['list'][$key]['VolChangeRate'] = formats($rs[32175], 2);
						/* 振幅 */
						$this->data['list'][$key]['HLDiffRate']	   = formats($rs[32170], 2);
						/* 市盈率 */
						$this->data['list'][$key]['EPS']		   = formats($rs[32204], 2);
					}
					unset($stockList, $stockInfo);
				}
			}
			else
			{
				$this->data['list'] = $stockList['list'];
				unset($stockList);
			}
			$offset = ($page - 1) * $limit;

			$this->data['list'] = array_sort($this->data['list'], $field[$orderby], $ordertype);
			$this->data['list'] = array_slice($this->data['list'], $offset, $limit);
		}
		returnJson($this->data, $callback);
	}

   /**
     * A+H股-P566
	 *
	 * @param integer $cateid    分类ID
	 * @param integer $page      页码
	 * @param integer $limit     条数
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
    private function getAhList() 
    {
		$cateid	   = intval($this->input->get('cateid', TRUE));
		$page	   = intval($this->input->get('page', TRUE));
		$limit     = intval($this->input->get('limit', TRUE));
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		/* 获取分类ID及排序字段ID */
		$tmpid = array(
			'orderbyid'   => array(0,1,2,3,4,5,6,7),
			'ordertypeid' => array(0,1));

		$field  = array('0'=>'ClosePrice','1'=>'DiffPrice','2'=>'DiffPriceRate','3'=>'HKClosePrice','4'=>'HKDiffPrice','5'=>'HKDiffPriceRate','6'=>'HKPriceRate','7'=>'HKVolRate');

		if(!in_array($orderby, $tmpid['orderbyid']) || !in_array($ordertype, $tmpid['ordertypeid']) || !checkRange(array($page, $limit)))
			exit;

		$stockInfo = $this->hkquotes_manage_mdl->ahList();

		if(is_empty($stockInfo))
		{
			$this->data['total'] = $stockInfo[32016]; 

            foreach($stockInfo['list'] as $key => $rs)
            {
				$stockName = gbk_to_utf8($rs[32102]);
				
				/* 应资讯银行频道数据要求,剥离出A+H股的银行类别来 */
				if($cateid == 200 && strpos(' '.$stockName,'银行') === FALSE)
					continue;
				
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 带后缀 */
				$this->data['list'][$key]['StockID2']      = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']     = gbk_to_utf8($rs[32102]);
				/* 最新价 */
				$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 2, '0.00');
				/* 涨跌额 */
				$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2, '0.00');
				/* 涨跌幅 */
				$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2, '0.00');
				/* 成交量 */
				$this->data['list'][$key]['Vol']		   = formats($rs[32163], 2, '0.00');

				/* 以下为H股字段 */

				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['HKStockID']       = substr($rs[32107], 0, strlen($rs[32107])-1);
				/* 股票代码 - 带后缀 */
				$this->data['list'][$key]['HKStockID2']      = $rs[32107];
				/* 股票名称 */
				$this->data['list'][$key]['HKStockName']     = gbk_to_utf8($rs[32103]);
				/* 最新价 */
				$this->data['list'][$key]['HKClosePrice']    = formats($rs[32182]/1000, 2, '0.00');
				/* 涨跌额 */
				$this->data['list'][$key]['HKDiffPrice']     = formats($rs[32177], 2, '0.00');
				/* 涨跌幅 */
				$this->data['list'][$key]['HKDiffPriceRate'] = formats($rs[32208], 2, '0.00');
				/* 成交量 */
				$this->data['list'][$key]['HKVol']			 = formats($rs[32165], 2, '0.00');
				/* AH比价 */
				$this->data['list'][$key]['HKPriceRate']     = formats($rs[32183], 2, '0.00');
				/* AH量比 */
				$this->data['list'][$key]['HKVolRate']       = formats($rs[32174], 2, '0.00');
            }
			unset($stockInfo);
		}
		$offset = ($page - 1) * $limit;

		$this->data['list'] = array_sort($this->data['list'], $field[$orderby], $ordertype);
		$this->data['list'] = array_slice($this->data['list'], $offset, $limit);

        returnJson($this->data, $callback);
     }

   /**
     * 指数列表获取
	 *
	 * @param integer $type      是否返回报价
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param integer $page      页码
     * @param integer $limit     条数
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getExponentList()
	{
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$type  = intval($this->input->get('type', TRUE));
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$callback = $this->input->get('callback', TRUE);
		
		/* 获取分类ID及排序字段ID */
		$tmpid = array(
			'orderbyid'   => array(0,1,2,3,4,5,6),
			'ordertypeid' => array(0,1));

		if(!in_array($orderby, $tmpid['orderbyid']) || !in_array($ordertype, $tmpid['ordertypeid']) || !checkRange(array($page, $limit)))
			exit;

		$stockList = config_item('hk_expid');

		/* 排序字段序号 */
		$field = array('0'=>'ClosePrice','1'=>'DiffPrice','2'=>'DiffPriceRate','3'=>'OpenPrice','4'=>'RefPrice','5'=>'HighPrice','6'=>'LowPrice');

		/* 判断是否返回报价 */
		if($type)
		{
			$stockInfo = $this->hkquotes_manage_mdl->stockPrice($stockList);
			
			if(is_empty($stockInfo))
			{
				foreach($stockInfo['list'] as $key => $rs)
				{
					/* 股票代码 */
					$this->data['list'][$key]['StockID']       = $rs[32101];
					/* 股票名称 */
					$this->data['list'][$key]['StockName']     = $rs[32102];
					/* 最新价 */
					$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 2);
					/* 涨跌额 */
					$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2);
					/* 涨跌幅 */
					$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2);
					/* 昨收价 */
					$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000, 2);
					/* 开盘价 */
					$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000, 2);
					/* 最高价 */
					$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000, 2);
					/* 最低价 */
					$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000, 2);
				}
				unset($stockInfo);
			}
		}
		else
		{
			$this->data['list'] = $stockList;
			unset($field,$stockList);
		}
		$offset = ($page - 1) * $limit;
		$this->data['total'] = count($this->data['list']); 
		$this->data['list']  = array_sort($this->data['list'], $field[$orderby], $ordertype);
		$this->data['list']  = array_slice($this->data['list'], $offset, $limit);

		returnJson($this->data, $callback);
	}
} 

/* End of file hkquotes_manage.php */
/* Location: ./application/controllers/_hkstock/hkquotes_manage.php */