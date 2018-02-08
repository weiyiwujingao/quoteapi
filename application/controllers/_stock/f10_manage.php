<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深行情F10 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class F10_manage extends MY_Controller
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
			$this->load->model('_stock/f10_manage_mdl');
            $this->$func();
		}
		else
		{
			exit;
		}
	}

   /**
     * 沪深公告批量查询-F106
	 *
     * @param string  $stockid 股票代码,多个";"分隔
	 * @param integer $limit   条数
	 * @return json
     */
	private function getNoticeBatch()
	{
		$limit    = intval($this->input->get('limit', TRUE));
		$stockid  = filter_slashes($this->input->get('stockid', TRUE));
		$callback = $this->input->get('callback', TRUE);
		
		if(!checkRange($limit)) exit;

		$stockInfo = $this->f10_manage_mdl->noticeList($stockid, $limit);

		if(is_empty($stockInfo))
		{
            foreach($stockInfo['list'] as $key => $rs)
            {
               $str = explode('.', $rs[96]);

               $this->data[$key]['SrcID']  = $str[0];
               $this->data[$key]['DataID'] = $str[1];
			   $this->data[$key]['Date']   = substr($str[7], 0, 22);
			   $this->data[$key]['Title']  = substr($str[7], 22);
            }
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 新股列表-F001
	 *
	 * @param integer $page     页码
     * @param integer $limit    条数
	 * @param integer $type     类别 0:全部股票 1:上海主板 2:中小板 3:创业板
	 * @param string  $callback 回调函数
	 * @return json
     */
	private function getNewStock()
	{
		$type  = intval($this->input->get('type', TRUE));
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$callback = $this->input->get('callback', TRUE);
		
		if(!checkRange(array($page, $limit)) || !in_array($type, array(0, 1, 2, 3)))
			exit;
		
		$stockInfo = $this->f10_manage_mdl->newStockList($type);
		
		if(is_empty($stockInfo))
		{
			$offset = ($page - 1) * $limit;

			$stockInfo = array_slice($stockInfo['list'], $offset, $limit);

			foreach($stockInfo as $key => $rs)
			{
				/* 序号 */
				$this->data[$key]['SeqNo']         = $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data[$key]['StockID']       = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data[$key]['StockID2']      = $rs[32101];
				/* 股票名称 */
				$this->data[$key]['StockName']     = gbk_to_utf8($rs[32102]);
				/* 网上发行数量(万股) */
				$this->data[$key]['Qty']			= $rs[32055];
				/* 发行价(元) */
				$this->data[$key]['Price']			= formats($rs[44], 2);
				/* 网上申购日期（网上发行日期） */
				$this->data[$key]['IssueDate']		= $rs[32501];
				/* 上市日期 */
				$this->data[$key]['ListDate']      = $rs[32500];
				/* 中签率公布日 */
				$this->data[$key]['DeclareDate']   = $rs[32502];
				/* 摇号结果公告日 中签号公布日 */
				$this->data[$key]['DeclareDate2']  = $rs[32505];
				/* 网上发行中签率 */
				$this->data[$key]['Rate']			= $rs[32525];
				/* 网下配售中签率 */
				$this->data[$key]['Rate2']			= $rs[32526];
				/* 上市地点（发行地区） */
				$this->data[$key]['ExchangeName']  = gbk_to_utf8($rs[32106]);
				/* 发行市盈率  */
				$this->data[$key]['EPS']			= formats($rs[32204], 2);
				/* (网上)超额认购倍数 */
				$this->data[$key]['Count']			= $rs[32070];
				/* 网下超额认购倍数(机构超额认购倍数) */
				$this->data[$key]['Count2']		= $rs[32071];
				/* 每中一签约(万元)= 发行价*(沪市1000 深市500）/中签率) ) */
				$this->data[$key]['Qty2']			= $rs[32091];
				/* 网上申购冻结资金(亿元) */
				$this->data[$key]['Qty3']			= $rs[32096];
				/* 网下配售冻结资金(亿元 */
				$this->data[$key]['Qty4']			= $rs[32097];
				/* 总冻结资金(亿元) */
				$this->data[$key]['Qty5']			= $rs[32098];
				/* 网上申购上限(万股) */
				$this->data[$key]['Qty6']			= $rs[32099];
				/* 打新收益 (%)=（开盘价-发行价)*中签率/发行价 */
				$this->data[$key]['Rate3']			= $rs[32527];
				/* 开盘溢价 (%)=(开盘价-发行价)/发行价*100 */
				$this->data[$key]['Rate4']			= $rs[32554];
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 发行上市明细-F010
	 *
	 * @param string $stockid  股票代码
	 * @param string $callback 回调函数
	 * @return json
     */
	private function getStockListedDetail()
	{
		$stockid  = filter_slashes($this->input->get('stockid', TRUE));
		$callback = $this->input->get('callback', TRUE);
		
		if(!is_code($stockid))
			exit;

		$stockInfo = $this->f10_manage_mdl->stockListedDetail($stockid);

		if(!empty($stockInfo))
		{
			/* 股票代码 - 无后缀 */
			$this->data['StockID']         = substr($stockInfo[32101], 0, strlen($stockInfo[32101])-1);
			/* 股票代码 - 有后缀 */
			$this->data['StockID2']		   = $stockInfo[32101];
			/* 网上发行日期 */
			$this->data['IssueDate']	   = $stockInfo[32501];
			/* 上市日期 */
			$this->data['ListDate']		   = $stockInfo[32500];
			/* 发行方式 */
			$this->data['IssueMode']	   = gbk_to_utf8($stockInfo[32530]);
			/* 每股面值(元) */
			$this->data['Value']		   = formats($stockInfo[32522], 2);
			/* 总发行数量(万股) */
			$this->data['Qty']             = formats($stockInfo[32055], 0);
			/* 每股发行价(元) */
			$this->data['Price']           = formats($stockInfo[44], 2);
			/* 发行费用(万元) */
			$this->data['Fee']             = formats($stockInfo[32523], 0);
			/* 募集资金总额(万元) */
			$this->data['TotalCapital']	   = formats($stockInfo[32700], 0);
			/* 募集资金净额(万元) */
			$this->data['NetCapital']	   = formats($stockInfo[32701], 0);
			/* 上市首日开盘价(元) */
			$this->data['OpenPrice']	   = formats($stockInfo[32151], 2);
			/* 上市首日收盘价(元) */
			$this->data['ClosePrice']	   = formats($stockInfo[32154], 2);
			/* 首日涨跌幅(%) */
			$this->data['DiffPriceRate']   = formats($stockInfo[32169], 2);
			/* 上市首日换手率 */
			$this->data['VolChangeRate']   = formats($stockInfo[32175], 2);
			/* 上网定价中签率 */
			$this->data['Rate']			   = formats($stockInfo[32525], 2);
			/* 二级市场配售中签率 */
			$this->data['Rate2']		   = formats($stockInfo[32526], 2);
			/* 加权平均发行市盈率 */
			$this->data['REPS']			   = formats($stockInfo[32302], 2);
			/* 摊薄发行市盈率 */
			$this->data['DPE']		       = formats($stockInfo[32304], 2);
			/* 主承销商 */
			$this->data['FConsignee']	   = gbk_to_utf8($stockInfo[32531]);
			/* 上市推荐人 */
			$this->data['Commender']	   = gbk_to_utf8($stockInfo[32532]);
			/* 发行前每股净资产(元) */
			$this->data['NetAssetsShare2'] = formats($stockInfo[32904], 2);
			/* 发行后每股净资产(元) */
			$this->data['NetAssetsShare']  = formats($stockInfo[32900], 2);
			/* 网下配售起始日 */
			$this->data['StartDate']	   = $stockInfo[32510];
			/* 网下配售截止日 */
			$this->data['EndDate']		   = $stockInfo[32511];
			/* 网下配售数量 (万股) */
			$this->data['Qty3']			   = formats($stockInfo[32096], 0);
			/* 网下申购数量上限(万股) */
			$this->data['Qty4']			   = formats($stockInfo[32097], 0);
			/* 网上申购上限(万股) */
			$this->data['Qty5']			   = formats($stockInfo[32098], 0);
			/* 网下冻结资金返还日期 */
			$this->data['ReturnDate']	   = $stockInfo[32507];
			/* 网上有效申购股数(亿股) */
			$this->data['Count']		   = formats($stockInfo[32070], 2);
			/* 网上有效申购户数(户) */
			$this->data['Count2']		   = formats($stockInfo[32071], 2);
			/* 网上有效申购户数(户) */
			$this->data['Count2']		   = formats($stockInfo[32071], 2);
			/* 网上有效申购资金(亿元) */
			$this->data['Count3']		   = formats($stockInfo[32072], 2);
			/* 网下有效申购股数(亿股) */
			$this->data['Count4']		   = formats($stockInfo[32073], 2);
			/* 网上有效申购户数(户) */
			$this->data['Count5']		   = formats($stockInfo[32074], 2);
			/* 网下有效申购资金(亿元) */
			$this->data['Count6']		   = formats($stockInfo[32075], 2);

			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 业绩报表-F071
	 *
	 * @param string  $stockid   股票代码
     * @param string  $datatype  分类 0:所有股票 1:沪A 2:深A 3:创业板 4:中小板 5:沪B 6:深B 7:三板
	 * @param string  $date      公告日期 YYYY-MM-DD
	 * @param integer $page      页码
	 * @param integer $limit     期数
	 * @param integer $flag      标识 0:全部 1:单支
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式 0:倒序 1:升序
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getYjbb()
	{
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$callback = $this->input->get('callback', TRUE);
		
		/* 初始化变量以防报警告 */
		$date = $datatype = $orderby = $ordertype = $stockid = '';

		/* 标识查询列表或是查单只股票的详情 TRUE:查全部股票业绩列表 FALSE:查询单只股票业绩详情 */
		$flag = (isset($_GET['flag']) && $_GET['flag']) ? FALSE : TRUE;
		
		if($flag)
		{
			$date = filter_slashes($this->input->get('date', TRUE));
			$datatype  = intval($this->input->get('datatype', TRUE));
			$orderby   = intval($this->input->get('orderby', TRUE));
			$ordertype = intval($this->input->get('ordertype', TRUE));
		}
		else
		{
			$stockid = filter_slashes($this->input->get('stockid', TRUE));
		}

		/* $flag = TRUE 检查查询列表的必要条件及合法性 */
		$tmpid1 = array(0,1);
		$tmpid2 = array(0,1,2,3,4,5,6,7);

		if($flag && (!in_array($ordertype, $tmpid2) || !is_date($date,'Y-m-d') || !checkRange(array($page, $limit)) || !in_array($orderby, $tmpid2) || !in_array($ordertype, $tmpid1)))
			exit;

		/* $flag = FALSE 检查查询股票详情的必要条件及合法性 */
		if(!$flag && !is_code($stockid || !checkRange(array($page, $limit))))
			exit;
		
		$stockInfo = $this->f10_manage_mdl->yjbb($datatype, $stockid, $date, $orderby, $ordertype);

		if(is_empty($stockInfo))
		{
			$offset = ($page - 1) * $limit;

			$this->data['total'] = $stockInfo[32016];

			$stockInfo = array_slice($stockInfo['list'], $offset, $limit);

			foreach($stockInfo as $key => $rs)
			{
				/* 序号 */
				$this->data['list'][$key]['SeqNo']			  = $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']		  = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']		  = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']		  = gbk_to_utf8($rs[32102]);
				/* 公告日期 */
				$this->data['list'][$key]['DeclareDate']	  = $rs[32502];
				/* 报告日期 */
				$this->data['list'][$key]['Date']			  = $rs[32018];
				/* 归属母公司净利润(万元) */
				$this->data['list'][$key]['NetProfit']		  = formats($rs[32761]);
				/* 基本每股收益(元) */
				$this->data['list'][$key]['ProfitShare']      = formats($rs[32901]);
				/* 净资产收益率(%) */
				$this->data['list'][$key]['NetProfitRate']    = formats($rs[33001]);
				/* 每股净资产(元) */
				$this->data['list'][$key]['NetAssetsShare']   = formats($rs[32900]);
				/* 主营业务收入增长率(%) */
				$this->data['list'][$key]['MBIncomeAddRate']  = formats($rs[33034]);
				/* 营业利润增长率(%) */
				$this->data['list'][$key]['BusiProfAddRate']  = formats($rs[33033]);
				/* 每股现金净流量(元) */
				$this->data['list'][$key]['NetCashFlowShare'] = formats($rs[32921]);
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     *
     * 业绩快报-F072
	 *
	 * @param string  $stockid   股票代码
	 * @param string  $date      公告日期 YYYY-MM-DD
	 * @param integer $page      页码
	 * @param integer $limit     期数
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getYjkb()
	{
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$callback = $this->input->get('callback', TRUE);

		/* 初始化变量以防报警告 */
		$date = $orderby = $ordertype = $stockid = '';

		/* 标识查询列表或是查单只股票的详情 TRUE:查全部股票业绩列表 FALSE:查询单只股票业绩详情 */
		$flag = (isset($_GET['flag']) && $_GET['flag']) ? FALSE : TRUE;
		
		if($flag)
		{
			$date = filter_slashes($this->input->get('date', TRUE));
			$orderby   = intval($this->input->get('orderby', TRUE));
			$ordertype = intval($this->input->get('ordertype', TRUE));
		}
		else
		{
			$stockid = filter_slashes($this->input->get('stockid', TRUE));
		}

		/* $flag = TRUE 检查查询列表的必要条件及合法性 */
		$tmpid1 = array(0,1);
		$tmpid2 = array(0,1,2,3,4,5,6,7);
		if($flag && (!is_date($date, 'Y-m-d') || !in_array($orderby, $tmpid2) || !in_array($ordertype, $tmpid1) || !checkRange(array($page, $limit))))
			exit;

		/* $flag = FALSE 检查查询股票详情的必要条件及合法性 */
		if(!$flag && (!is_code($stockid) || !checkRange(array($page, $limit))))
			exit;
		
		$stockInfo = $this->f10_manage_mdl->yjkb($stockid, $date, $orderby, $ordertype);

		if(is_empty($stockInfo))
		{
			$offset = ($page - 1) * $limit;

			$this->data['total'] = $stockInfo[32016];

			$stockInfo = array_slice($stockInfo['list'], $offset, $limit);
	
			foreach($stockInfo as $key => $rs)
			{
				/* 序号 */
				$this->data['list'][$key]['SeqNo']			= $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']		= substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']		= $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']		= gbk_to_utf8($rs[32102]);
				/* 公告日期 */
				$this->data['list'][$key]['DeclareDate']	= $rs[32502];
				/* 报告日期 */
				$this->data['list'][$key]['Date']			= $rs[32018];
				/* 基本每股收益(元) */
				$this->data['list'][$key]['ProfitShare']	= formats($rs[32901]);
				/* 营业收入 */
				$this->data['list'][$key]['BusiIncome']		= formats($rs[32732]);
				/* 去年同期营业收入 */
				$this->data['list'][$key]['BusiIncome1']	= formats($rs[32754]);
				/* 营业收入同比增长(%) */
				$this->data['list'][$key]['IncomeRate']		= formats($rs[33070]);
				/* 净利润 */
				$this->data['list'][$key]['NetProfit']		= formats($rs[32761]);
				/* 去年同期净利润 */
				$this->data['list'][$key]['NetProfit1']     = formats($rs[32773]);
				/* 净利润同比增长(%) */
				$this->data['list'][$key]['ProfitRate']     = formats($rs[33071]);
				/* 每股净资产(元)  */
				$this->data['list'][$key]['NetAssetsShare'] = formats($rs[32900]);
				/* 净资产增长率(%) */
				$this->data['list'][$key]['NetProfitRate']  = formats($rs[33031]);
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 业绩预告-F073
	 *
	 * @param string  $stockid    股票代码
     * @param string  $datatype   分类 0:全部 1:预增 2:预减 3:预盈 4:预降 5:预升 6:减亏 7:其他
	 * @param string  $date       公告日期 YYYY-MM-DD
	 * @param integer $page       页码
	 * @param integer $limit      期数
	 * @param integer $flag       标识 0:全部 1:单支
	 * @param integer $orderby    排序字段
	 * @param integer $ordertype  排序方式
	 * @param string  $callback   回调函数
	 * @return json
     */
	private function getYjyg()
	{
		$page  = intval($this->input->get('page',TRUE));
		$limit = intval($this->input->get('limit',TRUE));
		$datatype  = intval($this->input->get('datatype',TRUE));
		$callback  = $this->input->get('callback',TRUE);
		
		/* 初始化变量以防报警告 */
		$date = $stockid = $orderby = $ordertype = '';

		/* 标识查询列表或是查单只股票的详情 TRUE:查全部股票业绩列表 FALSE:查询单只股票业绩详情 */
		$flag = (isset($_GET['flag']) && $_GET['flag']) ? FALSE : TRUE;
		
		if($flag)
		{
			$date = $this->input->get('date',TRUE);
			$orderby   = filter_slashes($this->input->get('orderby',TRUE));
			$ordertype = intval($this->input->get('ordertype',TRUE));
		}
		else
		{
			$stockid = filter_slashes($this->input->get('stockid',TRUE));
		}

		/* $flag = TRUE 检查查询列表的必要条件及合法性 */
		$tmpid1 = array(0,1);
		$tmpid2 = array(0,1,2,3,4,5,6,7);
		if($flag && (!in_array($datatype, $tmpid2) || !is_date($date, 'Y-m-d') || !checkRange(array($page, $limit)) || !in_array($orderby, $tmpid1) || !in_array($ordertype, $tmpid1)))
			exit;

		/* $flag = FALSE 检查查询股票详情的必要条件及合法性 */
		if(!$flag && (!in_array($datatype, $tmpid2) || !is_code($stockid) || !checkRange(array($page, $limit))))
			exit;
		
		$stockInfo = $this->f10_manage_mdl->yjyg($datatype, $stockid, $date, $orderby, $ordertype);

		if(is_empty($stockInfo))
		{
			$offset = ($page - 1) * $limit;

			$this->data['total'] = $stockInfo[32016];

			$stockInfo = array_slice($stockInfo['list'], $offset, $limit);

			foreach($stockInfo as $key => $rs)
			{
				/* 序号 */
				$this->data['list'][$key]['SeqNo']       = $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']     = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']    = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']   = gbk_to_utf8($rs[32102]);
				/* 公告日期 */
				$this->data['list'][$key]['DeclareDate'] = $rs[32502];
				/* 报告日期 */
				$this->data['list'][$key]['Date']        = $rs[32018];
				/* 标题 */
				$this->data['list'][$key]['Title']		 = gbk_to_utf8($rs[32058]);
				/* 类别 */
				$this->data['list'][$key]['Type']		 = gbk_to_utf8($rs[32076]);
				/* 基本每股收益(元) */
				$this->data['list'][$key]['ProfitShare'] = formats($rs[32901]);
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 分配预告-F074
	 *
	 * @param string  $stockid   股票代码
	 * @param string  $date      公告日期 YYYY-MM-DD
	 * @param integer $page      页码
	 * @param integer $limit     期数
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getFpyg()
	{
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		/* 初始化变量以防报警告 */
		$date = $stockid = $ordertype = '';

		/* 标识查询列表或是查单只股票的详情 TRUE:查全部股票业绩列表 FALSE:查询单只股票业绩详情 */
		$flag = (isset($_GET['flag']) && $_GET['flag']) ? FALSE : TRUE;
		
		if($flag)
		{
			$date = filter_slashes($this->input->get('date', TRUE));
			$ordertype = intval($this->input->get('ordertype', TRUE));
		}
		else
		{
			$stockid = filter_slashes($this->input->get('stockid', TRUE));
		}

		/* $flag = TRUE 检查查询列表的必要条件及合法性 */
		$tmpid = array(0,1);
		if($flag && (!is_date($date, 'Y-m-d') || !in_array($ordertype, $tmpid) || !checkRange(array($page, $limit))))
			exit;

		/* $flag = FALSE 检查查询股票详情的必要条件及合法性 */
		if(!$flag && (!is_code($stockid) || $page < 1 || $limit < 1))
			exit;

		$stockInfo = $this->f10_manage_mdl->fpyg($stockid, $date, $ordertype);

		if(is_empty($stockInfo))
		{
			$this->data['total'] = $stockInfo[32016];

			$offset = ($page - 1) * $limit;

			$stockInfo = array_slice($stockInfo['list'], $offset, $limit);

			foreach($stockInfo as $key => $rs)
			{
				/* 序号 */
				$this->data['list'][$key]['SeqNo']       = $rs[32017];
				/* 股票代码 - 无后缀 */
				$this->data['list'][$key]['StockID']     = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']    = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']   = gbk_to_utf8($rs[32102]);
				/* 报告日期 */
				$this->data['list'][$key]['Date']        = $rs[32018];
				/* 发布日期 */
				$this->data['list'][$key]['DeclareDate'] = $rs[32502];
				/* 分配预案 */
				$this->data['list'][$key]['DivPlan']     = gbk_to_utf8($rs[32616]);
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 预批露时间-F047
	 *
	 * @param string  $stockid   股票代码
	 * @param string  $date      公告日期 YYYY-MM-DD
	 * @param integer $page      页码
	 * @param integer $limit     期数
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getYplsj()
	{
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$callback  = $this->input->get('callback', TRUE);

		/* 初始化变量以防报警告 */
		$date = $stockid = $orderby = $ordertype = '';

		/* 标识查询列表或是查单只股票的详情 TRUE:查全部股票业绩列表 FALSE:查询单只股票业绩详情 */
		$flag = (isset($_GET['flag']) && $_GET['flag']) ? FALSE : TRUE;
		
		if($flag)
		{
			$date = filter_slashes($this->input->get('date', TRUE));
			$orderby   = intval($this->input->get('orderby', TRUE));
			$ordertype = intval($this->input->get('ordertype', TRUE));
		}
		else
		{
			$stockid = filter_slashes($this->input->get('stockid', TRUE));
		}

		/* $flag = TRUE 检查查询列表的必要条件及合法性 */
		$tmpid1 = array(0,1);
		$tmpid2 = array(0,1,2,3,4,5,6,7);
		if($flag && (!is_date($date, 'Y-m-d') || !in_array($orderby, $tmpid2) || !in_array($ordertype, $tmpid1) || !checkRange(array($page, $limit))))
			exit;

		/* $flag = FALSE 检查查询股票详情的必要条件及合法性 */
		if(!$flag && (!is_code($stockid) || !checkRange(array($page, $limit))))
			exit;

		$stockInfo = $this->f10_manage_mdl->yplsj($stockid, $date, $orderby, $ordertype);

		if(is_empty($stockInfo))
		{
			$this->data['total'] = $stockInfo[32016];

			$offset = ($page - 1) * $limit;

			$stockInfo = array_slice($stockInfo['list'], $offset, $limit);

			foreach($stockInfo as $key => $rs)
			{
				/* 序号 */
				$this->data['list'][$key]['SeqNo']        = $rs[32017];
				/* 股票代码 - 无后缀 */ 
				$this->data['list'][$key]['StockID']      = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']     = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']    = gbk_to_utf8($rs[32102]);
				/* 报告日期 */
				$this->data['list'][$key]['Date']         = $rs[32018];
				/* 首次预约时间 */
				$this->data['list'][$key]['DeclareDate']  = $rs[32502];
				/* 一次变更日期 */
				$this->data['list'][$key]['DeclareDate2'] = $rs[32505];
				/* 二次变更日期 */
				$this->data['list'][$key]['DeclareDate3'] = $rs[32557];
				/* 三次变更日期 */
				$this->data['list'][$key]['DeclareDate4'] = $rs[32558];
				/* 实际披露时间 */
				$this->data['list'][$key]['DeclareDate5'] = $rs[32559];
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}

   /**
     * 送转比例排行-F049
	 *
	 * @param string  $stockid   股票代码
	 * @param string  $date      公告日期 YYYY-MM-DD
	 * @param integer $type      类别 0-送转比例排行 1-现金分红排行
	 * @param integer $page      页码
	 * @param integer $limit     条数
	 * @param integer $orderby   排序字段
	 * @param integer $ordertype 排序方式
	 * @param string  $callback  回调函数
	 * @return json
     */
	private function getSzbl()
	{
		$type  = intval($this->input->get('type', TRUE));
		$page  = intval($this->input->get('page', TRUE));
		$limit = intval($this->input->get('limit', TRUE));
		$callback = $this->input->get('callback', TRUE);

		/* 初始化变量以防报警告 */
		$date = $stockid = $orderby = $ordertype = '';

		/* 标识查询列表或是查单只股票的详情 TRUE:查全部股票业绩列表 FALSE:查询单只股票业绩详情 */
		$flag = (isset($_GET['flag']) && $_GET['flag']) ? FALSE : TRUE;
		
		if($flag)
		{
			$date = filter_slashes($this->input->get('date', TRUE));
			$orderby   = intval($this->input->get('orderby', TRUE));
			$ordertype = intval($this->input->get('ordertype', TRUE));
		}
		else
		{
			$stockid = filter_slashes($this->input->get('stockid', TRUE));
		}

		/* $flag = TRUE 检查查询列表的必要条件及合法性 */
		$tmpid1 = array(0,1);
		$tmpid2 = array(0,1,2,3,4,5,6,7,8,9,10,11,12);
		if($flag && (!is_date($date, 'Y-m-d') || !in_array($type, $tmpid1) || !in_array($orderby, $tmpid2) || !in_array($ordertype, $tmpid1) || !checkRange(array($page, $limit))))
			exit;

		/* $flag = FALSE 检查查询股票详情的必要条件及合法性 */
		if(!$flag && (!is_code($stockid) || !in_array($type,$tmpid1) || !checkRange(array($page, $limit))))
			exit;

		$stockInfo = $this->f10_manage_mdl->szbl($type, $stockid, $date, $orderby, $ordertype);

		if(is_empty($stockInfo))
		{
			$this->data['total'] = $stockInfo[32016];

			$offset = ($page - 1) * $limit;

			$stockInfo = array_slice($stockInfo['list'], $offset, $limit);

			foreach($stockInfo as $key => $rs)
			{
				/* 序号 */
				$this->data['list'][$key]['SeqNo']        = $rs[32017];
				/* 股票代码 - 无后缀 */ 
				$this->data['list'][$key]['StockID']      = substr($rs[32101], 0, strlen($rs[32101])-1);
				/* 股票代码 - 有后缀 */
				$this->data['list'][$key]['StockID2']     = $rs[32101];
				/* 股票名称 */
				$this->data['list'][$key]['StockName']    = gbk_to_utf8($rs[32102]);
				/* 报告日期 */
				$this->data['list'][$key]['Date']         = $rs[32018];
				/* 股权登记日 */
				$this->data['list'][$key]['DeclareDate']  = $rs[32502];
				/* 公告日期 */
				$this->data['list'][$key]['DeclareDate2'] = $rs[32505];
				/* 分配股本基数(万股) */
				$this->data['list'][$key]['BasedStocks']  = $rs[32620];
				/* 利润分配 */
				$this->data['list'][$key]['DivPlan']	  = gbk_to_utf8($rs[32616]);
				/* 现金分红,派息比例(每10派X) */
				$this->data['list'][$key]['DivCash']	  = $rs[32601];
				/* 现金分红总额（万元） */
				$this->data['list'][$key]['TotalDivCash'] = formats($rs[32617]);
				/* 股息率(%) */
				$this->data['list'][$key]['DivRate']	  = $rs[32618];
				/* 送股比例(每10股送XX股) */
				$this->data['list'][$key]['DivBStkRate']  = $rs[32602];
				/* 转增比例(每10股转增赠XX股) */
				$this->data['list'][$key]['DivTStkRate']  = $rs[32603];
				/* 每股收益(元) */
				$this->data['list'][$key]['ProfitShare']  = formats($rs[32901]);
				/* 每股未分配利润(元) */
				$this->data['list'][$key]['UnShareProfShare']  = formats($rs[32909]);
				/* 每股资本公积金(元) */
				$this->data['list'][$key]['AssetFundShare']    = formats($rs[32908]);
				/* 上期每股未分配利润(元) */
				$this->data['list'][$key]['UnShareProfShare2'] = formats($rs[32913]);
				/* 上期每股资本公积金（元） */
				$this->data['list'][$key]['AssetFundShare2']   = formats($rs[32912]);
			}
			unset($stockInfo);
		}
		returnJson($this->data, $callback);
	}
}

/* End of file f10_manage.php */
/* Location: ./application/controllers/_stock/f10_manage.php */