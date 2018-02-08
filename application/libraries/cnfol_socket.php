<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 用户中心通讯类,Codeigniter专用 v2.0
 * 使用方法
 * 1.将文件放入application/libraries/
 * 2.程序中载入$this->load->library('cnfol_socket',配置键名));
 * 3.发送请求$this->cnfol_socket->senddata();
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/

class Cnfol_socket
{
	/* socket 资源句柄 */
    private $handle = null;

	/* 静态实例 */
	//private static $instance = null;

	/* 
		默认缓存配置,可自定义在CI配置文件中

		@param server		 服务IP
		@param port		 服务端口
		@param key		 未知作用,暂时没什么用处
		@param timeout    超时时间(秒)
		@param productid 产品ID
		@param security  安全码
		@param source    登录来源
		@param filelog   错误日志
	*/
	private $options = array
	(
		'server'    => 'gw.passport.cnfol.net',
		'port'      => 443,
		'timeout'   => 10,
		'key'       => '                                ',
		'filelog'   => 'cnfol_socket'
	);

   /**
	 * 构造函数
	 *
	 * @param array	$options 缓存配置键名
	 * @return void
	 */
    public function __construct($keys = 'cnfolsocket')
	{
		/* 检查CI中的config_item函数是否存在 */
		if(!function_exists('config_item'))
			exit('call to undefined function config_item()');

        $this->setOptions(config_item($keys));
    }

   /**
	 * 发送及接收socket
	 *
	 * @param string $func 接口服务号
	 * @param array  $body 请求参数列表
	 * @return void
	 */
    public function senddata($func, $body)
	{
		if(!$this->handle)
		{
			$this->handle = fsockopen($this->options['server'], $this->options['port'], $errno, $errstr, $this->options['timeout']);

			if(!$this->handle)
			{
				$this->logWrite('fsockopen connect fail:'.$this->options['server'].':'.$this->options['port'].'|error:'.$errstr.',errno:'.$errno);

				return FALSE;
			}
		}

		$request = $this->array_to_str($func, $body);

		if(fwrite($this->handle, $request) === FALSE)
		{
			$this->logWrite('fwrite fail:'.$this->options['server'].':'.$this->options['port'].'|request:'.$request);

			return FALSE;
		}

		/* 获取12位头信息 */
		$headstr = fread($this->handle, 12);

		if($headstr === FALSE)
		{
			$this->logWrite('fread Response head fail|request:'.$request);

			return FALSE;
		}
		else
		{
			/* 截取后面8位的数值 */
			$strLen = substr($headstr, '4');
			/* 截取左边多余的0 */
			$strLen = ltrim($strLen, '0');
		}

		$response = '';

		while(1 < $strLen)
		{
			$content   = fread($this->handle, $strLen);
			$response .= $content;
			$len       = strlen($content);
			$strLen   -= $len;
		}

		$rs = $this->parse($response);

		$data = $rs['Status'];
	
		if(isset($rs['Records']['Record']) && is_array($rs['Records']['Record']))
		{
			if(!isset($rs['Records']['Record'][0]))
			{
				$tmp['Records']['Record'][0] = $rs['Records']['Record'];
				$rs['Record'] = $tmp['Records'];
			}
			else
			{
				$rs['Record'] = $rs['Records'];
			}
			$data = array_merge($rs['Record'], $data);
		}
        return $data;
     }

   /**
	 * 数组转字符串
	 *
	 * @param string $func 接口服务号
	 * @param array  $body 请求参数列表
	 * @return string
	 */
    private function array_to_str($func, $body)
	{
        $string  = '';
        $strData = '<CNFOLGW><Parameters>';

        if(!empty($body))
		{
            foreach($body as $key => $rs)
            {
                $string .= '<' . $key . '>' . $rs . '</' . $key . '>';
            }
            unset($body);
        }
        $strData .= $string . '</Parameters></CNFOLGW>';
		/* 计算长度并左边补零 */
        $strLen   = str_pad(strlen($strData), 8, '0', STR_PAD_LEFT);
        $strData  = $func . $this->options['key'] . $strLen . $strData;

        return $strData;
    }

   /**
	 * 对象转数组
	 *
	 * @param object $object 需要转换的对象
	 * @return void
	 */
	private function object_to_array(&$object) 
	{
		if(!empty($object))
		{
			$object = (array)$object;
			foreach ($object as $key => $value)
			{
				if(is_object($value) || is_array($value))
				{   
					$this->object_to_array($value);
					$object[$key] = $value;
				} 
			}
		}
		else
		{
			$object = '';
		}
	}

   /**
	 * 装载xml解析器并解析
	 *
	 * @param string $string 需要解析的字符串
	 * @return void
	 */
	private function parse($string = '')
	{
		$rs = '';

		if(!empty($string))
		{
			$rs = simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);
				
			$this->object_to_array($rs);
		}
		return $rs;
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
		!defined('APPPATH') && exit('cnfol_socket:`APPPATH` constants undefined');

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

/* End of file cnfol_socket.php */
/* Location: ./application/libraries/cnfol_socket.php */