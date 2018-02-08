<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 用户模型
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $dtime:2015-09-03
 ****************************************************************/
class User_manage_mdl extends CI_Model
{
    private $userId     = '';
    private $userName   = '';
    private $nickName   = '';
    private $userMoney  = '';
    private $logTime    = '';
    private $loginKey   = '';

    public function __construct()
	{
        parent::__construct();

        $userInfo = $this->getPCookie();

        if(isset($userInfo) && !empty($userInfo))
        {
            $this->userId   = $userInfo['userId'];
            $this->userName = $userInfo['username'];
            $this->nickName = $userInfo['nickname'];
            $this->userMoney= $userInfo['money'];
            $this->logTime  = $userInfo['logtime'];
            $this->loginKey = $userInfo['keys'];
        }
    }

    public function getUserID()
    {
        return $this->userId;
    }

    public function getUserName()
    {
        return $this->userName;
    }

    public function getNickname()
    {
        return $this->nickName;
    }

    public function getUserMoney()
    {
        return $this->userMoney;
    }

    public function getLogTime()
    {
        return $this->logTime;
    }

    public function getLoginKey()
    {
        return $this->loginKey;
    }

    /**
     * 取得cookie中存储的用户信息
     *
     * @return 成功：cookie信息 失败：false
     */
    private function getPCookie()
    {
        $userCookie = isset($_COOKIE['cookie']['passport']['userId']) ? $_COOKIE['cookie']['passport'] : '';

		$userCookie['userId']  = isset($userCookie['userId']) ? $userCookie['userId'] : '';
		$userCookie['logtime'] = isset($userCookie['logtime']) ? $userCookie['logtime'] : '';
		$userCookie['keys'] = isset($userCookie['keys']) ? $userCookie['keys'] : '';

        if(!empty($userCookie) && $this->checkLoginKey($userCookie['userId'], $userCookie['logtime'], $userCookie['keys']))
        {
            return $userCookie;
        }
        else
        {
            return false;
        }
    }

    /**
     * 检查LoginKey
     *
     * @param unknown_type $userid
     * @param unknown_type $logtime
     * @return boolean
     */
    private function checkLoginKey($userId, $logtime, $keys)
    {
        $loginKey = strtoupper(md5(md5($userId).md5($logtime).md5('49b7c8876d8cb85b')));
        if($loginKey == $keys)
        {
            return true;
        }
        return false;
    }
}

/* End of file user_manage_mdl.php */
/* Location: ./application/models/_user/user_manage_mdl.php */