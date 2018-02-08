<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * Filecache类,Codeigniter专用 v2.0
 * 使用方法
 * 1.将文件放入application/libraries/
 * 2.程序中载入$this->load->library('cnfol_file',配置参数));
 * 3.写缓存$this->cnfol_file->set(键值/文件名称,缓存数据,模块名/目录名,过期时间(秒),是否开启互斥锁);
 * 4.读缓存$this->cnfol_file->get(键值/文件名称,模块名/目录名);
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Cnfol_file
{
	/* 静态实例 */
	//private static $instance = null;

	/*
		默认缓存配置,可自定义在CI配置文件中

		@param prefix   缓存文件名前缀
		@param type     缓存存储类型 serialize:序列化保存 array:PHP数组方式保存
		@param expire   缓存文件过期时间,0:永不过期
		@param compress 是否需要将内容做zip压缩 0:否 1:是
		@param savepath	缓存路径,默认为CI框架application/cache/
		@param filelog  缓存日志文件名
	*/
	private $options = array
	(
		'prefix'   => '',
		'type'     => 'serialize',
		'expire'   => 10,
		'compress' => 0,
		'savepath' => '',
		'filelog'  => 'cnfol_file'
	);

   /**
	 * 构造函数
	 *
	 * @param array	$options 缓存配置键名
	 * @return void
	 */
	public function __construct($keys = 'dbcache')
	{
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
            $this->instance = new cnfol_file('dbcache');

        return $this->instance;
    }
	*/
	
   /**
	 * 写入缓存
	 *
	 * @param string  $key	  缓存名称
	 * @param mixed	  $data	  缓存数据
	 * @param string  $module 模块名称
	 * @param integer $expire 过期时间
	 * @param boolean $locked 是否开启互斥锁
	 * @return mixed
	 */
	public function set($key, $data, $module = 'system', $expire = 10, $locked = FALSE)
	{	
		$filename = $this->filename($key,$module);

		$contents = array('time' => time(), 'expire' => $expire, 'data'	 => $data);

	    if($this->options['type'] == 'array')
		{
	    	$data = "<?php\nreturn ".var_export($contents, TRUE).";\n?>";
	    }
		elseif($this->options['type'] == 'serialize')
		{
			$data = serialize($contents);

			/* 是否开启字符串压缩 */
			if($this->options['compress'] && function_exists('gzcompress'))
	    		$data = gzcompress($data,3);
	    }

		if($locked)
		{
			$file_size = file_put_contents($filename, $data, LOCK_EX);
		}
		else
		{
			$file_size = file_put_contents($filename, $data);
		}
	    
	    return ($file_size > 0) ? TRUE : FALSE;
	}
	
   /**
	 * 获取缓存
	 *
	 * @param string $keys   缓存名称
	 * @param string $module 模块名称
	 * @return mixed
	 */
	public function get($keys, $module = 'system')
	{
		$filename = $this->filename($keys, $module);
		
		/* 文件不存在 */
		if(!is_file($filename))
			return FALSE;

		if($this->options['type'] == 'array')
		{
			$data = @require($filename);
		}
		elseif($this->options['type'] == 'serialize')
		{
			$content = file_get_contents($filename);

			/* 是否开启字符串压缩 */
			if($this->options['compress'] && function_exists('gzcompress'))
	    		$content = gzuncompress($content);

			$data = unserialize($content);
		}

		/* 已过期删除之,$data['expire'] = 0 不过期 */
		if(($data['expire'] > 0) && (time() >  $data['time'] + $data['expire']))
		{
			unlink($filename);
			return FALSE;
		}		
		return $data['data'];
	}
	
   /**
	 * 删除缓存
	 *
	 * @param string $keys	 缓存名称
	 * @param string $module 模块名称(可以路径+文件形式)
	 * @return boolean
	 */
	public function delete($keys, $module = 'system')
	{
		$filename = $this->filename($keys, $module);

		if(file_exists($filename))
		{
			return unlink($filename) ? TRUE : FALSE;
		}
		else
		{
			$this->logWrite('mkdir:delete file fail');
			return FALSE;
		}
	}

   /**
     * 获得变量的存储文件名
     *
     * @param string $keys   缓存名称
     * @param string $module 模块名称
     * @return string
     */
    private function filename($keys, $module='')
	{
		$dir = '';
		$name = md5($keys);

		for($i = 0; $i < 2; $i++)
		{
			$dir .=	substr($name, $i, 2) . DIRECTORY_SEPARATOR;
		}

		$dir = $this->options['savepath'] . $module . DIRECTORY_SEPARATOR . $dir;

		if(!is_dir($dir))
		{
			if(!mkdir($dir, 0775, TRUE))
			{
				$this->logWrite('mkdir:create dir fail');
				return FALSE;
			}
		}
		$filename = $dir . $this->options['prefix'] . $keys . '_cache';

        return $filename;
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
		!defined('APPPATH') && exit('cnfol_file:`APPPATH` constants undefined');

		$log = '[' . date('H:i:s') . '][' . $msg . ']' . PHP_EOL;
		$logPath = APPPATH . 'logs/' . $this->options['filelog'] . '_' . date('Ymd') . '.log';

        error_log($log, 3, $logPath);
    }
}

/* End of file cnfol_file.php */
/* Location: ./application/libraries/cnfol_file.php */