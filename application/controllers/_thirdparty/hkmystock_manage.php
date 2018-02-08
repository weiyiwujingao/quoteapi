<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 港股行情自选股 v2.0 
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class HKMystock_manage extends MY_Controller
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
			$this->load->model('_thirdparty/hkmystock_manage_mdl');
            $this->$func();
		}
		else
		{
			exit;
		}
    }

   /**
     * 自选股操作
	 *
     * @param integer $sid  股票ID
     * @param integer $uid  用户ID
	 * @param integer $type 操作类型 1:添加 2:删除 3:修改
	 * @return json
     */
    function operMyStock()
    {
		$uid  = filter_slashes($this->input->get('uid', TRUE));
		$sid  = filter_slashes($this->input->get('sid', TRUE));
		$type = intval($this->input->get('type', TRUE));
		$callback = $this->input->get('callback',TRUE);
		$stockid  = $sid . HK_STOCK_EXT;

		if(!in_array($type, array(1,2,3)) || !is_code($stockid, 'H') || strlen($uid) != 32)
			exit;
		
		$errno = '00';

		/* 操作频率判断 */
		if($this->hkmystock_manage_mdl->limit_time($uid))
		{
			$errno = '01';

			$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));

			returnJson($this->data, $callback);
		}
		else
		{
			$data['Type']   = 1;
			$data['UserID'] = $uid;

			switch($type)
			{
				case '1':
					$data['AvgBidPrice'] = 0;
					$data['GroupID']	 = 1;
					$data['ClassID']	 = 1;
					$data['MaxCnts']     = 100;
					$data['StockCodes']  = $stockid;
					$data['CostPrice']   = 0;
					$data['HoldCnts']    = 0;
					$data['Commission']  = 0;
					$data['Handling']	 = 0;

					$errno = $this->hkmystock_manage_mdl->insert_stock($data) ? '00' : '02';

					$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));
				break;
				case '2':
					$data['StockCode'] = $stockid;

					$errno = $this->hkmystock_manage_mdl->delete_stock($data) ? '00' : '04';

					$this->data = array('flag' => $errno, 'info' => $this->getErrorMsg($errno));
				break;
			}
		}
		returnJson($this->data, $callback);
    }

   /**
     * 提示信息
	 *
     * @param string $errno 错误码
	 * @return array
     */
	private function getErrorMsg($errno='00')
	{
		$lang = array
		(
			'00' => '操作成功',
			'01' => '您操作过于频繁,请稍后再试',
			'02' => '您已添加过该股票',
			'03' => '您已添加过该股票',
			'04' => '该股票不存在',
			'90' => '您没有权限访问'
		);
		return $lang[$errno];
	}
}

/* End of file hkmystock_manage.php */
/* Location: ./application/controllers/_hkstock/hkmystock_manage.php */