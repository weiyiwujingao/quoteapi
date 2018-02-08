<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$route['default_controller'] = '_stock/quotes_manage/index';
$route['404_override'] = '';

/* A股路由 */
$route['^f10\.html$']     = '_stock/f10_manage/index';
$route['^stock\.html$']   = '_stock/quotes_manage/index';
$route['^mystock\.html$'] = '_stock/mystock_manage/index';
$route['^chart\.html$']   = '_stock/chart_manage/index';

/* 港股路由 */
$route['^hkchart\.html$']   = '_hkstock/hkchart_manage/index';
$route['^hkstock\.html$']   = '_hkstock/hkquotes_manage/index';
$route['^hkmystock\.html$'] = '_hkstock/hkmystock_manage/index';

/* 财经头条-第三方使用的接口 */
$route['^thirdparty\/stock\.html$'] = '_thirdparty/quotes_manage/index';
$route['^thirdparty\/mystock\.html$'] = '_thirdparty/mystock_manage/index';
$route['^thirdparty\/hkmystock\.html$'] = '_thirdparty/hkmystock_manage/index';

/* End of file routes.php */
/* Location: ./application/config/routes.php */