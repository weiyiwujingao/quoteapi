<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 行情API-全局主配置
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/

/*
	dbcache 文件缓存

	@param prefix   缓存文件名前缀
	@param type     缓存存储类型 serialize:序列化保存 array:PHP数组方式保存
	@param expire   缓存文件过期时间,0:永不过期
	@param savepath	缓存路径,默认为CI框架application/cache/
	@param filelog  缓存日志文件名
*/
$config['dbcache'] = array
(
	'prefix'   => 'quotes_api_',
	'type'     => 'serialize',
	'expire'   => '10',
	'savepath' => APPPATH . 'cache' . DIRECTORY_SEPARATOR,
	'filelog'  => 'cnfol_file'
);

/* 
	默认缓存配置,可自定义在CI配置文件中
	
	@param server  服务IP,请使用数组配置
	@param expire  缓存过期时间 0:永不过期,单位秒
	@param prefix  缓存keys前缀
	@param filelog 日志文件名称
*/
$config['memcache'] = array
(
	'server' => array(
		array('host'=>'memcache4.cache.cnfol.com','port'=>11211),
		array('host'=>'memcache9.cache.cnfol.com','port'=>11211)
	),
	'prefix'   => '',
	'expire'   => 10,
	'filelog'  => 'cnfol_mem'
);

/* 
	沪深行情

	@param server	 服务IP
	@param port		 服务端口
	@param timeout   超时时间(秒)
	@param userid    用户ID
	@param productid 产品ID
	@param security  安全码
	@param source    登录来源
	@param filelog   错误日志
*/
$config['cnfolfix'] = array
(
	'server'    => ' ',
	'port'      => 443,
	'timeout'   => 10,
	'userid'    => 0,
	'productid' => 10,
	'security'  => 'STOREGW',
	'source'    => 'CNFOL',
	'filelog'   => 'cnfol_fix'
);

/* 
	沪深行情F10

	@param server	 服务IP
	@param port		 服务端口
	@param timeout   超时时间(秒)
	@param userid    用户ID
	@param productid 产品ID
	@param security  安全码
	@param source    登录来源
	@param filelog   错误日志
*/
$config['cnfolf10'] = array
(
	'server'    => ' ',
	'port'      => 5555,
	'timeout'   => 10,
	'userid'    => 0,
	'productid' => 10,
	'security'  => 'STOREGW',
	'source'    => 'CNFOL',
	'filelog'   => 'cnfol_f10'
);

/* 
	港股行情

	@param server	 服务IP
	@param port		 服务端口
	@param timeout   超时时间(秒)
	@param userid    用户ID
	@param productid 产品ID
	@param security  安全码
	@param source    登录来源
	@param filelog   错误日志
*/
$config['cnfolhkfix'] = array
(
	'server'    => ' ',
	'port'      => 888,
	'timeout'   => 10,
	'userid'    => 0,
	'productid' => 10,
	'security'  => 'STOREGW',
	'source'    => 'CNFOL',
	'filelog'   => 'cnfol_hkfix'
);

/* 
	港股行情F10

	@param server	 服务IP
	@param port		 服务端口
	@param timeout   超时时间(秒)
	@param userid    用户ID
	@param productid 产品ID
	@param security  安全码
	@param source    登录来源
	@param filelog   错误日志
*/
$config['cnfolhkf10'] = array
(
	'server'    => ' ',
	'port'      => 8888,
	'timeout'   => 10,
	'userid'    => 0,
	'productid' => 10,
	'security'  => 'STOREGW',
	'source'    => 'CNFOL',
	'filelog'   => 'cnfol_hkf10'
);

/**
 * 用户中心
 *
 * @param key     服务器验证KEY,暂时无用
 * @param server  服务器地址
 * @param port    服务器端口
 * @param filelog 日志保存路径
 */
$config['cnfolsocket'] = array
(
    'server'  => ' ',
    'port'    => '443',
    'key'     => '                                ',
    'filelog' => 'cnfolsocket'
);

/* End of file main_setting.php */
/* Location: ./application/config/main_setting.php */
