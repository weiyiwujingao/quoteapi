<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深即时行情 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Qiangu_quotes_manage extends MY_Controller
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
			$this->load->model('_cms/qiangu_quotes_manage_mdl');//qiangu_quotes_manage_mdl.php
            $this->$func();
		}
		else
		{
			exit;
		}
	}
   /**
     * 千股千评个股列表所属
	 *
     * @param integer $page		    页码
     * @param integer $limit        获取条数
	 * @param string  $blocktype    行业
	 * @param string  $storegion    地域
	 * @param string  $stconcept    概念
	 * @param string  $stocktype    股票类型
	 * @param string  $stsuggest    增减持建议
	 * @param string  $callback     回调函数
	 * @return json
     */
    private function getStockQuotesList() 
    {
		$page      = intval($this->input->get('page', TRUE));
		$limit     = intval($this->input->get('limit', TRUE));
		$blocktype = filter_slashes($this->input->get('blocktype', TRUE));
		$storegion = intval($this->input->get('storegion', TRUE));
		$stconcept = intval($this->input->get('stconcept', TRUE));
		$stocktype = intval($this->input->get('stocktype', TRUE));
		$stsuggest = intval($this->input->get('stsuggest', TRUE));

		$callback  = $this->input->get('callback', TRUE);

		$blocktypearray = empty($blocktype)?array():array('bankuai'=>$blocktype);
		$storegionarray = empty($storegion)?array():array('diyu' => $storegion);
		$stconceptarray = empty($stconcept)?array():array('gainian like '=>"%$stconcept%");
		$stocktypearray = empty($stocktype)?array():array('stocktype'=>$stocktype);
		$stsuggestarray = empty($stsuggest)?array():array('jianyi'=>$stsuggest);
		//$recentlysarray = array('time'=>$time);

		$wherearray = array_merge($blocktypearray,$storegionarray,$stconceptarray,$stocktypearray,$stsuggestarray/*,$recentlysarray*/);
		$wherearray = empty($wherearray)?array('id >'=>'0'):$wherearray;

		$offset = ($page-1)*$limit;

		$stockList = $this->qiangu_quotes_manage_mdl->getStockQuotesList($blocktype,$storegion,$stconcept,$stocktype,$stsuggest,$page,$limit );
		
		//$stockListceshi = $this->qiangu_quotes_manage_mdl->getStockQuotesListceshi($wherearray,$offset,$limit );
		
		if(is_empty($stockList))
		{
			foreach($stockList['list'] as $key => $rs)
			{
				/* 个股代码 */
				$this->data['list'][$key]['stockcode'] = $rs['gegudaima'];
				/* 个股名称 */
				$this->data['list'][$key]['stockname'] = $rs['gegumingcheng'];
				/* 走势点评 */
				$this->data['list'][$key]['stockidea'] = $rs['ggdp'];
				/* 涨跌幅 */
				$this->data['list'][$key]['stockchan'] = formats(floatval($rs['zdf']),2);
				/* 主力成本 */
				$this->data['list'][$key]['stockmain'] = $rs['zlcb'];
				/* 时间日期 */
				$this->data['list'][$key]['stocktime'] = $rs['sjrq'];
			}
			$this->data['count'] = $stockList['count'];
		}
        returnJson($this->data, $callback);
    }
	/**
     * 千股千评个股行情所属
	 *
     * @param integer $page		    页码
     * @param integer $limit        获取条数
     * @param string  $stockid      个股代码
	 * @param string  $callback     回调函数
	 * @return json
     */
    private function getStockQuotes() 
    {   
		$page      = intval($this->input->get('page', TRUE));
		$limit     = intval($this->input->get('limit', TRUE));
		$stockid  = filter_slashes($this->input->get('stockid', TRUE));
		$callback = $this->input->get('callback', TRUE);

		if(!is_code($stockid))
			exit;
        
		$stockList = $this->qiangu_quotes_manage_mdl->getStockQuotes(intval($stockid),$limit,$page);

		if(is_empty($stockList))
		{
			foreach($stockList['list'] as $key => $rs)
			{
				/* 走势点评*/
				$this->data['list'][$key]['stockcomment'] = $rs['ggdp'];
				/* 机构参与率*/
				$this->data['list'][$key]['stockorganiz'] = $rs['jgcyd'];
				/* 资金流向 */
				$this->data['list'][$key]['stockcapital'] = $rs['zjlx'];
				/* 主力成本 */
				$this->data['list'][$key]['stockmaincos'] = $rs['zlcb'];
				/* 涨跌幅 */
				$this->data['list'][$key]['stockchanges'] = formats(floatval($rs['zdf']),2);
				/* 成交量 */
				$this->data['list'][$key]['stockvolumes'] = $rs['cjl'];
				/* 时间日期 */
				$this->data['list'][$key]['stockdatimes'] = $rs['sjrq'];
			}
			$this->data['count'] = $stockList['count'];
		}
		//var_dump($this->data);exit;
        returnJson($this->data, $callback);
    }

	 private function getStockQuotesListceshi() 
    {
		$page      = intval($this->input->get('page', TRUE));
		$limit     = intval($this->input->get('limit', TRUE));
		$blocktype = filter_slashes($this->input->get('blocktype', TRUE));
		$storegion = intval($this->input->get('storegion', TRUE));
		$stconcept = intval($this->input->get('stconcept', TRUE));
		$stocktype = intval($this->input->get('stocktype', TRUE));
		$stsuggest = intval($this->input->get('stsuggest', TRUE));

		$callback  = $this->input->get('callback', TRUE);

		$blocktypearray = empty($blocktype)?array():array('bankuai'=>$blocktype);
		$storegionarray = empty($storegion)?array():array('diyu' => $storegion);
		$stconceptarray = empty($stconcept)?array():array('gainian like '=>"%$stconcept%");
		$stocktypearray = empty($stocktype)?array():array('stocktype'=>$stocktype);
		$stsuggestarray = empty($stsuggest)?array():array('jianyi'=>$stsuggest);
		//$recentlysarray = array('time'=>$time);

		$wherearray = array_merge($blocktypearray,$storegionarray,$stconceptarray,$stocktypearray,$stsuggestarray/*,$recentlysarray*/);
		$wherearray = empty($wherearray)?array('id >'=>'0'):$wherearray;

		$offset = ($page-1)*$limit;

		//$stockList = $this->qiangu_quotes_manage_mdl->getStockQuotesList($blocktype,$storegion,$stconcept,$stocktype,$stsuggest,$page,$limit );
		
		$stockListceshi = $this->qiangu_quotes_manage_mdl->getStockQuotesListceshi($wherearray,$offset,$limit);
		//var_dump($stockListceshi);exit;
		if(is_empty($stockListceshi))
		{
			$this->data['count'] = $stockListceshi['count'];
			foreach($stockListceshi['list'] as $key => $rs)
			{
				/* 个股代码 */
				$this->data['list'][$key]['stockcode'] = $rs['gegudaima'];
				/* 个股名称 */
				$this->data['list'][$key]['stockname'] = $rs['gegumingcheng'];
				/* 走势点评 */
				$this->data['list'][$key]['stockidea'] = $rs['ggdp'];
				/* 涨跌幅 */
				$this->data['list'][$key]['stockchan'] = formats(floatval($rs['zdf']),2);
				/* 主力成本 */
				$this->data['list'][$key]['stockmain'] = $rs['zlcb'];
				/* 时间日期 */
				$this->data['list'][$key]['stocktime'] = $rs['sjrq'];
			}
			
		}
        returnJson($this->data, $callback);
    }
  
}

/* End of file quotes_manage.php */
/* Location: ./application/controllers/_stock/quotes_manage.php */