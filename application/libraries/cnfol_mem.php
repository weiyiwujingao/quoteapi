<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * Memcache类,Codeigniter专用 v2.0
 * 使用方法
 * 1.将文件放入application/libraries/
 * 2.程序中载入$this->load->library('cnfol_mem',[配置键名]);
 * 3.程序中调用$this->cnfol_mem->set('缓存键名','缓存内容','过期时间',[是否压缩]);
 * 4.程序中调用$this->cnfol_mem->get('缓存键名');
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Cnfol_mem
{
	/* memcache资源句柄 */
	private $handle = null;

	/* 静态实例 */
	//private static $instance = null;

	/* 
		默认缓存配置,可自定义在CI配置文件中
		
		@param server  服务IP,请使用数组配置
		@param expire  缓存过期时间 0:永不过期,单位秒
		@param prefix  缓存keys前缀
		@param filelog 日志文件名称
	*/
	private $options = array
	(
		'server' => array(
			array('host'=>'memcache4.cache.cnfol.com','port'=>11211),
			array('host'=>'memcache9.cache.cnfol.com','port'=>11211)
		),
		'prefix'   => '',
		'expire'   => 10,
		'filelog'  => 'cnfol_mem'
	);

	public function __construct($keys = 'memcache')
	{
		if(!extension_loaded('memcache'))
			exit('unable to load the requested base class Memcache');
        
		/* 检查CI中的config_item函数是否存在 */
		if(!function_exists('config_item'))
			exit('call to undefined function config_item()');

        $this->setOptions(config_item($keys));

		$this->connect();
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
	 * 连接服务,支持memcache集群
	 * 
	 * @return void
	 */
	private function connect()
	{
		$this->handle = new Memcache;

		foreach($this->options['server'] as $rs)
		{
			$flag = $this->handle->addServer($rs['host'], $rs['port']);

			if(FALSE === $flag)
				$this->logWrite('cnfol_mem connect fail|host:'.$rs['host']);
		}
	}

	/**
	 * 获取缓存
	 *
	 * @param string $keys 缓存键值
	 * @return mixed
	 */
	public function get($keys)
	{
		$rs = $this->handle->get($this->options['prefix'].$keys);

		return $rs;
	}

	/**
	 * 添加缓存
	 *
	 * @param string  $keys     缓存keys
	 * @param mixed   $value    缓存数据
	 * @param integer $expire   缓存过期时间 0:永不过期
	 * @param integer $compress 数据是否压缩存储 0:否 1:是
	 * @return boolean
	 */
	public function set($keys, $value, $expire = 10, $compress = 0)
	{
		$flag = $this->handle->set($this->options['prefix'] . $keys, $value, $compress, $expire);

		if(FALSE === $flag)
			$this->logWrite('cnfol_mem set fail|keys:'.$keys);
		
		return $flag;
	}

	/**
	 * 删除缓存
	 *
	 * @param string $keys 缓存keys
	 * @return boolean
	 */
	public function delete($keys)
	{
		$flag = $this->handle->delete($this->options['prefix'] . $keys);

		if(FALSE === $flag)
			$this->logWrite('cnfol_mem delete fail|keys:'.$keys);

		return $flag;
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
		!defined('APPPATH') && exit('cnfol_mem:`APPPATH` constants undefined');

		$log = '[' . date('H:i:s') . '][' . $msg . ']' . PHP_EOL;
		$logPath = APPPATH . 'logs/' . $this->options['filelog'] . '_' . date('Ymd') . '.log';

        error_log($log, 3, $logPath);
    }
}

/* End of file cnfol_mem.php */
/* Location: ./application/libraries/cnfol_mem.php */