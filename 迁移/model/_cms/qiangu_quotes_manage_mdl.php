<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深即时行情模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Qiangu_quotes_manage_mdl extends CI_Model 
{
	/*数据库表*/
	private $_tables = array('ihf_qgqp'=>'ihf_qgqp');

    public function __construct()
	{
        parent::__construct();

		$this->load->library(array('cnfol_mem'));
    }

   /**
     * 千股千评行情列表数据
	 *
     * @param integer $page		    页码
     * @param integer $limit        获取条数
	 * @param string  $blocktype    行业
	 * @param string  $storegion    地域
	 * @param string  $stconcept    概念
	 * @param string  $stocktype    股票类型
	 * @param string  $stsuggest    增减持建议
     * @return array
     */
    public function getStockQuotesList($blocktype,$storegion,$stconcept,$stocktype,$stsuggest,$page=1,$limit=20) 
    {	
		//echo $stockid;
		$keys = get_keys(date('Ymd'),$blocktype,$storegion,$stconcept,$stocktype,$stsuggest,$page,$limit);
		$data = $this->cnfol_mem->get($keys);
		//$data = '';
		if(empty($data))
		{
			$this->load->database('cms');

			$sql = 'SELECT max(time) FROM ihf_qgqp';
			$query = $this->db->query($sql);
			$time = $query->result_array();
			$time = $time['0']["max(time)"];

			$blocktypearray = empty($blocktype)?array():array('bankuai'=>$blocktype);
			$storegionarray = empty($storegion)?array():array('diyu' => $storegion);
			$stconceptarray = empty($stconcept)?array():array('gainian like '=>"%$stconcept%");
			$stocktypearray = empty($stocktype)?array():array('stocktype'=>$stocktype);
			$stsuggestarray = empty($stsuggest)?array():array('jianyi'=>$stsuggest);
			$recentlysarray = array('time'=>$time);

			$wherearray = array_merge($blocktypearray,$storegionarray,$stconceptarray,$stocktypearray,$stsuggestarray,$recentlysarray);
			$wherearray = empty($wherearray)?array('id >'=>'0'):$wherearray;

			$page = ($page-1)*$limit;

			$this->db->select("sjrq,gegudaima,gegumingcheng,ggdp,zdf,zlcb")
					 ->from($this->_tables['ihf_qgqp'])
					 ->where($wherearray)
					 ->order_by('id','desc')
					 ->limit($limit,$page);

			$query = $this->db->get();
			if($query->num_rows() > 0)
			{
				$data['list'] = $query->result_array();
				$data['count']= count($data['list']);
				$this->cnfol_mem->set($keys, $data, ONE_DAYS);
			}
		}
		return $data;
	

    }
	/**
     * 千股千评个股详情数据
	 *
     * @param integer $page		    页码
     * @param integer $limit        获取条数
     * @param string  $stockid      个股代码
     * @return array
     */
    public function getStockQuotes($stockid,$limit,$page) 
    {	
		$keys = get_keys(date('Ymd'),MD5($stockid),$page,$limit);
		$data = $this->cnfol_mem->get($keys);
		//$data = '';
		if(empty($data))
		{
			$page = ($page-1)*$limit;
			$this->load->database('cms');
			$this->db->select("ggdp,jgcyd,zjlx,zlcb,zdf,cjl,sjrq")
					 ->from($this->_tables['ihf_qgqp'])
					 ->where('gegudaima',$stockid)
					 ->order_by('id','desc')
					 ->limit($limit,$page);
			$query = $this->db->get();
			if($query->num_rows() > 0)
			{
				$data['list'] = $query->result_array();
				$data['count']= count($data['list']);
				$this->cnfol_mem->set($keys, $data, ONE_DAYS);
			}
		}
		return $data;		
    }
	   /**
     * 千股千评行情列表数据
	 *
     * @param integer $page		    页码
     * @param integer $limit        获取条数
	 * @param string  $blocktype    行业
	 * @param string  $storegion    地域
	 * @param string  $stconcept    概念
	 * @param string  $stocktype    股票类型
	 * @param string  $stsuggest    增减持建议
     * @return array
     */
    public function getStockQuotesListceshi($wherearray,$offset=0,$limit=20) 
    {	
	    $keys = date('Ymd').'-'.join('-',$wherearray).'-'.$offset.'-'.$limit;
		$data = $this->cnfol_mem->get($keys);
		//$data = '';
		if(empty($data))
		{
			$this->load->database('cms');

			$this->db->select("sjrq,gegudaima,gegumingcheng,ggdp,zdf,zlcb")
					 ->from($this->_tables['ihf_qgqp'])
					 ->where($wherearray)
					 ->where('`time` = (SELECT time FROM ihf_qgqp ORDER BY id DESC LIMIT 1)')
					 ->order_by('id','desc');
			
			$db = clone($this->db);

			$data['count']= $this->db->count_all_results();

			$this->db = $db;
			$this->db->limit($limit,$offset);

			$query = $this->db->get();
			if($query->num_rows() > 0)
			{
				$data['list'] = $query->result_array();
				$this->cnfol_mem->set($keys, $data, ONE_DAYS);
			}
		}
		return $data;

    }

}

/* End of file quotes_manage_mdl.php */
/* Location: ./application/models/_stock/quotes_manage_mdl.php */