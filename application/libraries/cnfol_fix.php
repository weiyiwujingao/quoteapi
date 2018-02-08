<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 行情StoreGW通讯类,Codeigniter专用 v2.0
 * 使用方法
 * 1.将文件放入application/libraries/
 * 2.程序中载入$this->load->library('cnfol_fix',[配置键名]);
 * 3.程序中调用$this->cnfol_fix->getContext('接口号','参数数组',[获取节点])
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Cnfol_fix
{
	/* socket资源句柄 */
    private $handle = null;

	/* 固定分隔符号标识 */
    private $split  = null;

	/* 静态实例 */
	//private static $instance = null;

	/* 
		默认缓存配置,可自定义在CI配置文件中

		@param server	 服务IP
		@param port		 服务端口
		@param timeout   超时时间(秒)
		@param userid    用户ID
		@param productid 产品ID
		@param security  安全码
		@param source    登录来源
		@param filelog   错误日志
	*/
	private $options = array
	(
		'server'    => 'gw.price.store.cnfol.net',
		'port'      => 443,
		'timeout'   => 10,
		'userid'    => 0,
		'productid' => 10,
		'security'  => 'STOREGW',
		'source'    => 'CNFOL',
		'filelog'   => 'cnfol_fix'
	);

	/* 错误码定义 */
	private $errorflag = array
	(
		'3001' => '作业功能别不存在',
		'3002' => '资料长度不正确',
		'3003' => '资料参数不正确',
		'3004' => '连接数超过设定值',
		'3005' => '数据库连接失败',
		'3006' => '回复资料产生失败',
		'3007' => 'BServer数据异常',
		'3008' => 'QuoteService连接异常',
		'3010' => '用户未登入或客户端IP地址未授权',
		'3011' => '安全码错误',
		'3012' => '用户不具有此功能权限',
		'3099' => '接口其它异常'
	);

	/**
	 * 构造函数
	 *
	 * @param string $keys 参数配置
	 * @return void
	 */
    public function __construct($keys = 'cnfolfix')
	{
		$this->split = chr(1);

		/* 检查CI中的config_item函数是否存在 */
		if(!function_exists('config_item'))
			exit('call to undefined function config_item()');

        $this->setOptions(config_item($keys));
    }

   /**
     * 获得静态实例
	 *
     * @return object
     */
	/*
    public static function getInstance()
	{
        if($this->instance == null)
            $this->instance = new cnfol_fix('cnfolfix');

        return $this->instance;
    }
	*/

   /**
	 * 发送及接收socket
	 *
	 * @param string  $func 接口号
	 * @param array   $body 参数集合
	 * @param string  $keys 默认获取完整数组,反之获取$key = list节点
	 * @return array
	 */
    public function getContext($func, $body = array(), $keys = '')
	{
		if(!$this->handle)
		{
			$this->handle = fsockopen($this->options['server'], $this->options['port'], $errno, $errstr, $this->options['timeout']);

			if(!$this->handle)
			{
				$this->logWrite('fsockopen connect fail:'.$this->options['server'].':'.$this->options['port'].'|error:'.$errstr.',errno:'.$errno);
				return array();
			}
		}
		/* 组装请求头信息 */
        $bodystr  = $this->getBody($body);
        $headstr  = $this->getHead($func,strlen($bodystr));
        $request  = $headstr.$bodystr;
        $request .= '10=' . str_pad($this->getCheckCode($request), 3, '0', STR_PAD_LEFT) . $this->split;

		runTime('begin');
        $result = $this->read($request);
		runTime('end');

		$this->logWrite('runtime:'.runTime('begin','end',4).'s,memory:'.runTime('begin','end','m').'kb,func:'.$func.',param:'.$bodystr);

        return (isset($result[$keys]) && !empty($result[$keys])) ? $result[$keys] : $result;
    }

	/**
	 * 发送/接收socket消息
	 *
	 * @param string $request 请求的head+body数据
	 * @return mixed
	 */
    private function read($request)
	{
		stream_set_timeout($this->handle, $this->options['timeout']);

        if(fwrite($this->handle, $request) === FALSE)
		{
			$this->logWrite('fwrite fail:'.$this->options['server'].':'.$this->options['port'].'|request:'.$request);
			return FALSE;
		}

        $headstr = $bodystr = '';
		$headarr = $rsphead = $rspbody = array();

		$i = $headLength = 0;

        while(TRUE)
        {
            if(($tmp = fread($this->handle, 1)) == $this->split)
                $i++;

            $headstr .= $tmp;

			/* 读到8个特殊分隔符跳出 */
            if($i >= 8) break;

			/* 读取响应头长度异常返回错误(GW服务端不可全信) */
			if($headLength++ > 100)
			{
				$this->logWrite('fread Response head headLength:'.$headLength.',i:'.$i.',request:'.$request);
				return FALSE;
			}
        }

        $headarr = explode($this->split, $headstr);

        foreach($headarr as $rs)
		{
            $keys = explode('=', $rs);

            if(!isset($keys[0]) || !isset($keys[1]))
				continue;

            $rsphead[$keys[0]] = $keys[1];
        }

        $this->options['userid']   = $rsphead[32002];
        $this->options['security'] = $rsphead[32004];

        /* 检查错误码状态是否正常 */
		if($rsphead[32005] > 0)
		{
			$error = isset($this->errorflag[$rsphead[32005]]) ? $this->errorflag[$rsphead[32005]] : $rsphead[32005];
			
			$this->logWrite('32005:'.$error.',request:'.$request);

			return FALSE;
		}

		$length = intval($rsphead[9]);
		
		$count = 0;
		while($length > 0)
		{
            $res = fread($this->handle,$length);
            $length -= strlen($res);
            $bodystr .= $res;

			/* 超出异常读流次数或读取内容为空返回错误(GW服务端不可全信) */
			if($count++ > 100 || empty($res))
			{
				$this->logWrite('fread Response body count:'.$count.',request:'.$request);
				return FALSE;				
			}
		}

        fread($this->handle, 3);

        $checksum = '';

        while(($tmp = fread($this->handle, 1)) != $this->split)
		{
            $checksum .= $tmp;
        }

		$rspbody = $this->strToArray($bodystr);

		unset($head,$headarr);

        return $rspbody;		
    }

	/**
	 * 组装socket head请求头信息
	 *
	 * @param string  $func 接口号
	 * @param integer $len  总长度(字节)
	 * @return string
	 */
    private function getHead($func,$len)
	{
        $head[] = '8=CNFOLSTORE';
        $head[] = '9='.$len;
        $head[] = '35=UCS01';
        $head[] = '32001='.$func;
        $head[] = '32002='.$this->options['userid'];
        $head[] = '32003='.$this->options['productid'];
        $head[] = '32004='.$this->options['security'];

        return implode($this->split,$head) . $this->split;
    }

	/**
	 * 组装socket body部份信息
	 *
	 * @param string $body 内容数据
	 * @return string
	 */
    private function getBody($body)
	{
        $str_body = '';

        foreach($body as $key => $value)
            $str_body .= $key . '=' . $value . $this->split;

        return $str_body;
    }

	/**
	 * 将body字符串数据转为数组
	 *
	 * @param string $bodystr body数据
	 * @return array
	 */
    private function strToArray($bodystr)
	{
        $bodyarr = explode($this->split,$bodystr);

        if(end($bodyarr) == '') array_pop($bodyarr);

		$i = 0;
		$flag = FALSE;
		$body = array();

        foreach($bodyarr as $kv)
		{
            $keys = explode('=', $kv);
			
			if($keys[0] == 32017)
			{
				$flag = TRUE;
				$i = $keys[1]-1;
				$body['list'][$i][$keys[0]] = $keys[1];
			}
			else if($flag)
			{
				$body['list'][$i][$keys[0]] = $keys[1];
			}
			else
			{
				$body[$keys[0]] = $keys[1];
			}
        }
		return $body;
    }

	/**
	 * 计算校验码
	 *
	 * @param integer $code 错误码
	 * @return void
	 */
    private function getCheckCode($text)
	{
        $sum = 0;
        $len = strlen($text);

        for($i = 0; $i < $len; $i++)
		{
            $sum += ord($text[$i]);
        }
        return $sum % 256;
    }

	/**
	 * 根据自定义配置覆盖默认配置
	 *
	 * @param array $options 自定义缓存配置
	 * @return void
	 */
	public function setOptions($options = '')
	{
		$options ? $this->options = array_merge($this->options, $options) : '';
	}

	/**
	 * 日志记录
	 *
	 * @param string $msg 日志内容
	 * @return void
	 */
    private function logWrite($msg)
	{
		!defined('APPPATH') && exit('cnfol_fix:`APPPATH` constants undefined');

		$log = '[' . date('H:i:s') . '][' . $msg . ']' . PHP_EOL;
		$logPath = APPPATH . 'logs/' . $this->options['filelog'] . '_' . date('Ymd') . '.log';

        error_log($log, 3, $logPath);
    }

	/**
	 * 关闭当前socket连接
	 *
	 * @return void
	 */
    private function close()
	{
        $this->handle && fclose($this->handle);
        $this->handle = null;
    }

	/**
	 * 析构函数,在对象被回收时,关闭当前socket连接
	 *
	 * @return void
	 */
    public function __destruct()
	{
        $this->close();
    }
}

/* End of file cnfol_fix.php */
/* Location: ./application/libraries/cnfol_fix.php */