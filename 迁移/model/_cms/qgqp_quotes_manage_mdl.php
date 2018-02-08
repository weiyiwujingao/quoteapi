<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深即时行情模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Qgqp_quotes_manage_mdl extends CI_Model 
{
	/*数据库表*/
	private $_tables = array('ihf_qgqp3'=>'ihf_qgqp3');

    public function __construct()
	{
        parent::__construct();

		$this->load->library(array('cnfol_mem'));
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
		$data = '';
		if(empty($data))
		{
			$page = ($page-1)*$limit;

			$this->load->database('cms');
			$this->db->select("ggdp,jgcyd,zjlx,zlcb,zdf,cjl,sjrq")
					 ->from($this->_tables['ihf_qgqp3'])
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
    public function getStockQuotesList($wherearray,$offset=0,$limit=2800) 
    {	
	    $keys = date('Ymd').'-'.join('-',$wherearray).'-'.$offset.'-'.$limit;
		$data = $this->cnfol_mem->get($keys);
		$data = '';
		if(empty($data))
		{
			$this->load->database('cms');

			$this->db->select("sjrq,gegudaima,gegumingcheng,ggdp,zdf,zlcb")
					 ->from($this->_tables['ihf_qgqp3'])
					 ->where($wherearray)
					 ->where('`time` = (SELECT time FROM ihf_qgqp3 ORDER BY id DESC LIMIT 1)')
					 ->order_by('id','desc');
			
			$db = clone($this->db);

			$data['count']= $this->db->count_all_results();//var_dump($data);

			$this->db = $db;
			$this->db->limit($limit,$offset);

			$query = $this->db->get();//var_dump($query->result_array());exit;

			if($query->num_rows() > 0)
			{
				$data['list'] = $query->result_array();			
			}
			$this->cnfol_mem->set($keys, $data, ONE_DAYS);
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
    public function getSingleStockQuotes($stockid) 
    {	
		$keys = get_keys(date('Ymd'),MD5('single'.$stockid));
		$data = $this->cnfol_mem->get($keys);
		$data = '';
		if(empty($data))
		{
			$this->load->database('cms');
			$this->db->select("sjrq,gegudaima,gegumingcheng,ggdp,zdf,zlcb")
					 ->from($this->_tables['ihf_qgqp3'])
					 ->where('gegudaima',$stockid)
					 ->or_where('gegumingcheng',$stockid)
					 ->order_by('id','desc')
					 ->limit(1);
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