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
| 自定义配置
|--------------------------------------------------------------------------
|
| 沪深、港股常量配置
|
*/

define('HK_STOCK_EXT', 'A');

defined('MEMORY_LIMIT_ON') or define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
defined('MAGIC_QUOTES_GPC') or define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

/* 缓存时间 */
define('TEN_SECONDS', 10);
define('THREE_HOURS', 3600 * 3);
define('HALF_DAYS', 3600 * 12);
define('ONE_DAYS', 86400);
define('SEVEN_DAYS', 86400 * 7);
define('ONE_MONTHS', 86400 * 30);

/* End of file constants.php */
/* Location: ./application/config/constants.php */