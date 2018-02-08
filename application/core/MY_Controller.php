<?php
/****************************************************************
 * 扩展控制器基类,所有控制器必须继承它
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class MY_Controller extends CI_Controller
{
   /**
	 * 构造函数
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();
    }
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */