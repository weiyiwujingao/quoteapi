<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| global constant
|--------------------------------------------------------------------------
*/

/* 超时时间 */
defined('TIME_OUT') or define('TIME_OUT',10);
defined('MEMORY_LIMIT_ON') or define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
defined('MAGIC_QUOTES_GPC_ON') or define('MAGIC_QUOTES_GPC_ON', get_magic_quotes_gpc());

define('HK','-');
define('HK_EXT','.html');
define('HKSTOCK_EXT','A');
define('LOGIN_LIMIT_KEY','49b7c8876d8cb85b');

/* A股行情 */
define('STOCK_URL','http://quotes.3g.cnfol.com/');

/* 沪深行情API */
define('STOCK_API','http://quotes2.api.3g.cnfol.com/stock.html');
/* 沪深分时API */
define('CHART_API','http://quotes2.api.3g.cnfol.com/chart.html');
define('CHART_API2','http://quotes2.api.3g.cnfol.com/');
/* 港股分时API */
define('HKCHART_API','http://quotes2.api.3g.cnfol.com/hkchart.html');
/* 沪深分时图表API */
define('CHART_IMAGE_API','http://chart2.api.3g.cnfol.com');
/* 沪深自选股API */
define('MYSTOCK_API','http://quotes2.api.3g.cnfol.com/mystock.html');
/* 港股行情API */
define('HK_STOCK_API','http://quotes2.api.3g.cnfol.com/hkstock.html');

/* 个股新闻数据接口 */
define('API_STOCK_NEWS','http://shell.cnfol.com/article/hqarticle.php?classid=%s&title=%s&record=%s');

/* 缓存过期时间 */
define('TEN_SECONDS', 10);
define('ONE_MINUTES',30);
define('ONE_DAYS',24*3600);
define('SEVEN_DAYS',7*24*3600);
define('TEN_DAYS',10*24*3600);
define('FIFTEEN_DAYS',15*24*3600);

/* 缓存key测试及正式开关,正式环境只需将"test_"设置为空就好了 */
define('IS_ONLINE','');

/* 个股新闻缓存KEY */
define('STOCK_NEWS_LIST','hkstock_news_list_'.IS_ONLINE);

/* 自选股 */
define('MYSTOCK_LIST','mystock__list__'.IS_ONLINE);

/* 个股详情API */
//define('STOCK_DETAIL_API','http://quotes2.test.api.3g.cnfol.com/cms.html');

define('STOCK_DETAIL_API','http://quotes.api.cnfol.com/cms.html');

/* End of file constants.php */
/* Location: ./application/config/constants.php */