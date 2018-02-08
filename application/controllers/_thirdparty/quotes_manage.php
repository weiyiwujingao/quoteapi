<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深即时行情 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Quotes_manage extends MY_Controller
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
			$this->load->model('_thirdparty/quotes_manage_mdl');
            $this->$func();
		}
		else
		{
			exit;
		}
	}

   /**
     * 股票分类联合查询-P582
	 *
     * @param string  $cateid    关键字,支持代码,名称,简拼
     * @param integer $type      是否返回报价 0:不返回 1:返回
	 * @param integer $page      页码
     * @param integer $limit     条数
     * @param integer $orderby   排序字段
     * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @param string  $callback  回调函数
	 * @return json
     */
    private function getStockListPlus() 
    {
		$cateid = filter_slashes($this->input->get('cateid', TRUE));
		$type   = intval($this->input->get('type', TRUE));
		$page   = intval($this->input->get('page', TRUE));
		$limit  = intval($this->input->get('limit', TRUE));
		$orderby   = intval($this->input->get('orderby', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		/* 排序字段序号 */
		$field = array('0'=>'ClosePrice','1'=>'DiffPrice','2'=>'DiffPriceRate','3'=>'OpenPrice','4'=>'RefPrice','5'=>'HighPrice','6'=>'LowPrice','7'=>'SVOL','8'=>'VolRate','9'=>'Amt','10'=>'Vol','11'=>'VolChangeRate','12'=>'HLDiffRate','13'=>'EPS');

		$rep = '/^(\d[,]?)+$/';
		if(!preg_match($rep, $cateid))
			exit;

		$stockList = $this->quotes_manage_mdl->stockListPlus($cateid);

		if(is_empty($stockList))
		{
			$this->data['total'] = $stockList[32016];

			/* 判断是否返回报价 */
			if($type)
			{
				$stockInfo = $this->quotes_manage_mdl->stockPrice($stockList['list']);
				
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
						$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 2);
						/* 开盘价 */
						$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000, 2);
						/* 昨收价 */
						$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000, 2);
						/* 最高价 */
						$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000, 2);
						/* 最低价 */
						$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000, 2);
						/* 涨跌额 */
						$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2);
						/* 涨跌幅 */
						$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2);
						/* 成交总额 */
						$this->data['list'][$key]['Amt']		   = formats($rs[32166]/100000, 2);
						/* 成交总量 */
						$this->data['list'][$key]['Vol']		   = formats($rs[32165]/10000, 2);
						/* 量比 */
						$this->data['list'][$key]['VolRate']	   = formats($rs[32174]/100, 2);
						/* 换手率 */
						$this->data['list'][$key]['VolChangeRate'] = formats($rs[32175], 2);
						/* 振幅 */
						$this->data['list'][$key]['HLDiffRate']	   = formats($rs[32170], 2);
						/* 市盈率 */
						$this->data['list'][$key]['EPS']		   = formats($rs[32204], 2);
					}

					$offset  = ($page - 1) * $limit;
					$orderby = isset($field[$orderby]) ? $orderby : 0;
					$ordertype = in_array($ordertype, array(0,1)) ? $ordertype : 0;

					$this->data['list'] = array_sort($this->data['list'], $field[$orderby], $ordertype, TRUE);
					$this->data['list'] = array_slice($this->data['list'], $offset, $limit);

					unset($stockInfo);
				}
			}
			else
			{
				$this->data['list'] = $stockList['list'];
			}
			unset($stockList);
		}
        returnJson($this->data, $callback);
    }

   /**
     * 股票代码模糊搜索 v2.0-P581
	 *
     * @param string  $keyword  关键字,支持代码,名称,简拼
     * @param integer $userid   用户ID
     * @param integer $type     是否返回报价 0:不返回 1:返回
     * @param integer $flag     股票类型标识 0:全部 1:A股 2:港股
     * @param integer $page     页码
     * @param integer $limit    获取条数
	 * @param string  $callback 回调函数
	 * @return json
     */
    private function getStockSuggest() 
    {
		$type     = intval($this->input->get('type', TRUE));
		$flag     = intval($this->input->get('flag', TRUE));
		$page     = intval($this->input->get('page', TRUE));
		$limit    = intval($this->input->get('limit', TRUE));
		$userid   = filter_slashes($this->input->get('userid', TRUE));
		$keyword  = filter_slashes($this->input->get('keyword', TRUE));
		$callback = $this->input->get('callback', TRUE);

		if(strlen($keyword) > 20 || !in_array($flag, array(0,1,2)) || !checkRange(array($page, $limit)))
			exit;
		
		$offset = ($page - 1) * $limit;

		$stockList = $this->quotes_manage_mdl->stockSearch($keyword, $flag, $offset, $limit, $userid);

		if(is_empty($stockList))
		{
			$stocks = array();

			//$this->data['total'] = $stockList[32016];
			$this->data['total'] = 2800;
			
			foreach($stockList['list'] as $key => $rs)
			{
				$stocks['list'][] = $rs[32101];

				$stocks['stock'][$key]['StockID']   = substr($rs[32101], 0, strlen($rs[32101])-1);
				$stocks['stock'][$key]['StockID2']  = $rs[32101];
				$stocks['stock'][$key]['StockName'] = $rs[32102];
				$stocks['stock'][$key]['Flag'] = $rs[32042];
				$stocks['stock'][$key]['Mark'] = $rs[32110];
			}

			/* 判断是否返回报价 */
			if($type)
			{
				unset($stocks['stock']);

				$stockInfo = $this->quotes_manage_mdl->stockPrice($stocks['list'], 6, $userid);

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
						$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000, 2);
						/* 涨跌额 */
						$this->data['list'][$key]['DiffPrice']     = formats($rs[32168], 2);
						/* 涨跌幅 */
						$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169], 2);
						/* 自选股标识 */
						$this->data['list'][$key]['Flag']		   = $rs[32042];
					}
				}	
				unset($stockInfo);
			}
			else
			{
				unset($stocks['list']);
				$this->data['list'] = $stocks['stock'];
			}
			unset($stocks, $stockList);
		}
        returnJson($this->data, $callback);
    }

   /**
     * 个股排行 v2.0 -P586
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
		$cateid  = intval($this->input->get('cateid',TRUE));
		$userid  = filter_slashes($this->input->get('userid',TRUE));
		$page    = intval($this->input->get('page',TRUE));
		$limit   = intval($this->input->get('limit',TRUE));
		$orderby = intval($this->input->get('orderby',TRUE));
		$ordertype = intval($this->input->get('ordertype',TRUE));
		$callback  = $this->input->get('callback',TRUE);
		
		/* 获取分类ID及排序字段ID */
		$tmpid = array(
			'cateid'      => array(101,102,103,104,105,106,107,108),
			'orderbyid'   => array(0,1,2,3,4),
			'ordertypeid' => array(0,1));
		
		if(!in_array($cateid, $tmpid['cateid']) || !in_array($orderby, $tmpid['orderbyid']) || !in_array($ordertype, $tmpid['ordertypeid']) || !checkRange(array($page, $limit)))
			exit;
		
		$offset = ($page - 1) * $limit;

		$stockInfo = $this->quotes_manage_mdl->stockRankFinal($cateid, 6, $orderby, $ordertype, $offset, $limit, $userid);

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
				$this->data['list'][$key]['ClosePrice']    = formats($rs[32154]/1000,2);
				/* 涨跌额 */
				$this->data['list'][$key]['DiffPrice']     = formats($rs[32168],2);
				/* 涨跌幅 */
				$this->data['list'][$key]['DiffPriceRate'] = formats($rs[32169],2);
				/* 开盘价 */
				$this->data['list'][$key]['OpenPrice']	   = formats($rs[32151]/1000,2);
				/* 昨收价 */
				$this->data['list'][$key]['RefPrice']      = formats($rs[32156]/1000,2);
				/* 最高价 */
				$this->data['list'][$key]['HighPrice']     = formats($rs[32152]/1000,2);
				/* 最低价 */
				$this->data['list'][$key]['LowPrice']      = formats($rs[32153]/1000,2);
				/* 成交单量 */
				$this->data['list'][$key]['SVOL']		   = formats($rs[32163],0);
				/* 量比 */
				$this->data['list'][$key]['VolRate']	   = formats($rs[32174]/100,2);
				/* 成交总额(亿) */
				$this->data['list'][$key]['Amt']		   = formats($rs[32166]/100000,2);
				/* 成交总量(万) */
				$this->data['list'][$key]['Vol']		   = formats($rs[32165]/10000,2);
				/* 换手率 */
				$this->data['list'][$key]['VolChangeRate'] = formats($rs[32175],2);
				/* 振幅 */
				$this->data['list'][$key]['HLDiffRate']	   = formats($rs[32170],2);
				/* 市盈率 */
				$this->data['list'][$key]['EPS']		   = formats($rs[32204],2);
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
     * 股票报价
	 *
     * @param string  $sid      股票代码,多个以";"分隔
     * @param integer $userid   用户ID
	 * @param string  $callback 回调函数
	 * @return json
     */
    private function getStockPrice() 
    {   
		$userid   = filter_slashes($this->input->get('userid', TRUE));
		$stockStr = filter_slashes($this->input->get('sid', TRUE));
		$callback = $this->input->get('callback', TRUE);

		if(empty($stockStr))
			exit;

		/* 超过50支直接退回 */
		if(count($stockList) > 50) returnJson($this->data);

		$stockInfo = $this->quotes_manage_mdl->stockPrice($stockStr, 6, $userid);

		if(is_empty($stockInfo))
		{
			foreach($stockInfo['list'] as $key => $rs)
			{
				/* 序号 */
				$this->data[$key]['SeoNo']          = $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data[$key]['StockID']        = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data[$key]['StockID2']       = $rs[32101];
				/* 股票名称 */
				$this->data[$key]['StockName']      = $rs[32102];
				/* 最新价 */
				$this->data[$key]['ClosePrice']     = formats($rs[32154]/1000, 2);
				/* 涨跌额 */
				$this->data[$key]['DiffPrice']      = formats($rs[32168], 2);
				/* 涨跌幅 */
				$this->data[$key]['DiffPriceRate']  = formats($rs[32169], 2);
				/* 振幅 */
				$this->data[$key]['HLDiffRate']     = formats($rs[32170], 2);
				/* 开盘价 */
				$this->data[$key]['OpenPrice']	    = formats($rs[32151]/1000, 2);
				/* 昨收价 */
				$this->data[$key]['RefPrice']       = formats($rs[32156]/1000, 2);
				/* 最高价 */
				$this->data[$key]['HighPrice']      = formats($rs[32152]/1000, 2);
				/* 最低价 */
				$this->data[$key]['LowPrice']       = formats($rs[32153]/1000,2);
				/* 量比 */
				$this->data[$key]['VolRate']	    = formats($rs[32174]/100, 2);
				/* 成交总额 */
				$this->data[$key]['Amt']		    = formats($rs[32166]/100000, 2);
				/* 成交总量 */
				$this->data[$key]['Vol']		    = formats($rs[32165]/10000, 2);
				/* 换手率 */
				$this->data[$key]['VolChangeRate']  = formats($rs[32175], 2);
				/* 振幅 */
				$this->data[$key]['HLDiffRate']		= formats($rs[32170], 2);
				/* 市盈率 */
				$this->data[$key]['EPS']		    = formats($rs[32204], 2);
				/* 内盘 */
				$this->data[$key]['InVol']		    = $rs[32171];
				/* 外盘 */
				$this->data[$key]['OutVol']			= $rs[32172];
				/* 总市值 */
				$this->data[$key]['TotalValue']		= formats(($rs[32560] * $rs[32154]/1000)/10000, 2);
				/* 自选股标识 */
				$this->data[$key]['Flag']			= $rs[32042];
			}
			unset($stockList, $stockInfo);
		}
        returnJson($this->data, $callback);
    }

   /**
     * 概念,行业,地域板块排行
	 *
     * @param integer $blocktype 类别 0:行业板块 1:概念板块 2:地域板块
     * @param integer $ordertype 排序方式 0:倒序 1升序
     * @param integer $page      页码
     * @param integer $limit     获取条数
	 * @param string  $callback  回调函数
	 * @return json
     */
    private function getBlockRank() 
    {   
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$ordertype = intval($this->input->get('ordertype', TRUE));
		$blocktype = intval($this->input->get('blocktype', TRUE));
		$callback  = $this->input->get('callback', TRUE);
		
		$tmpid = array(
			'ordertypeid' => array(0,1),
			'blocktypeid' => array(0,1,2)
		);

		if(!in_array($ordertype, $tmpid['ordertypeid']) || !in_array($blocktype, $tmpid['blocktypeid']) || !checkRange(array($page, $limit)))
			exit;

		$stockInfo = $this->quotes_manage_mdl->blockRank($blocktype, 3, $ordertype);

		if(is_empty($stockInfo))
		{
            foreach($stockInfo['list'] as $key => $rs)
            {
				/* 序号 */
                $this->data[$key]['SeqNo']          = $rs[32017];
				/* 板块ID */
                $this->data[$key]['BlockID']        = $rs[32104];
				/* 板块名称 */
                $this->data[$key]['BlockName']      = $rs[32105];
				/* 加權平均漲跌幅 */
                $this->data[$key]['RDiffRate']      = formats($rs[32301], 2);	
				/* 成交量 */
                $this->data[$key]['Vol']            = formats($rs[32165]/10000, 2);
				/* 成交额 */
                $this->data[$key]['Amt']            = formats($rs[32166]/100000, 2);
				/* 领涨/领跌股票代码 */
                $this->data[$key]['StockID']        = substr($ordertype ? $rs[32107] : $rs[32101], 0, 6);
				/* 领涨/领跌股票代码 */
                $this->data[$key]['StockID2']       = $ordertype ? $rs[32107] : $rs[32101];
				/* 领涨/领跌股票名称 */
                $this->data[$key]['StockName']      = $ordertype ? $rs[32108] : $rs[32102];
				/* 领涨/领跌股票报价 */
                $this->data[$key]['ClosePrice']     = $ordertype ? formats($rs[32182]/1000, 2) : formats($rs[32154]/1000, 2);
				/* 领涨/领跌股票涨跌幅 */
                $this->data[$key]['DiffPriceRate2'] = $ordertype ? formats($rs[32223], 2) : formats($rs[32208], 2);
				/* 换手率 */
                $this->data[$key]['VolChangeRate']  = formats($rs[32175], 2);
				/* 上涨家数 */
                $this->data[$key]['UpCnt']          = formats($rs[32070], 0);
				/* 下跌家数 */
                $this->data[$key]['DownCnt']        = formats($rs[32071], 0);
            }

			$offset = ($page - 1) * $limit;
			$this->data = array_slice($this->data, $offset, $limit);

			unset($tmpid, $stockInfo);
		}
        returnJson($this->data, $callback);
    }
}

/* End of file quotes_manage.php */
/* Location: ./application/controllers/_thirdparty/quotes_manage.php */