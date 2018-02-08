<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 公共函数
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/

/**
  * 输出友好的调试信息
  *
  * @param mixed $vars 需要判断的日期
  * @return mixed
  */
function t($vars)
{
	if(is_array($vars))
		exit("<pre><br>" . print_r($vars, TRUE) . "<br></pre>".rand(1000,9999));
	else
		exit($vars);
}

/**
  * 读取远程/本地数据
  *
  * @param string  $url    远程地址
  * @param integer $is_ser 是否进行反序列化
  * @return string
  */
function get_contents($url, $is_ser = 0)
{ 
	return $is_ser ? unserialize(file_get_contents($url)) : file_get_contents($url);
}

/**
  * 处理缓存键名称
  *
  * @return string
  */
function get_keys()
{
    $argList = func_get_args();

	return join('_', $argList);
}

/**
  * 返回json结构,并支持ajax跨域
  *
  * @param array  $data 数组
  * @param string $call 匿名函数
  * @return json
  */
function returnJson($data = array(), $call = 'call')
{ 
	exit(empty($call) ? json_encode($data) : $call.'('.json_encode($data).')');
}

/** 
  * 安全过滤函数1,转义单引号
  * 
  * @param  mixed   $string 字符串/数组 
  * @param  integer $force  强制进行过滤
  * @param  boolean $strip  是否需要去除反转义符号
  * @return mixed 
  */  
function filter_slashes($string, $force = 1, $strip = FALSE)
{
	/* 如果是表单则需要判断MAGIC_QUOTES_GPC状态 */
	if(!MAGIC_QUOTES_GPC || $force)
	{
		if(is_array($string))
		{
			foreach($string as $key => $val)
			{
				$string[$key] = filter_slashes($val, $force, $strip);
			}
		}
		else
		{
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	$string = filter_sql($string);
	$string = filter_str($string);
	$string = filter_html($string);

	return $string;
}

/** 
  * 安全过滤函数2,过滤html、进制代码
  * 
  * @param  mixed $string 需要过滤的数据 
  * @param  mixed $flags  是否使用PHP自带函数
  * @return mixed 
  */  
function filter_html($string, $flags = NULL)
{
	if(is_array($string))
	{
		foreach($string as $key => $val)
			$string[$key] = filter_html($val, $flags);
	}
	else
	{
		if($flags === NULL)
		{
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== FALSE)
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
		}
		else
		{
			if(PHP_VERSION < '5.4.0')
				$string = htmlspecialchars($string, $flags);
			else
				$string = htmlspecialchars($string, $flags, 'UTF-8');
		}
	}
	return $string;
}

/**
  * 安全过滤函数3,数据加下划线防止SQL注入
  *
  * @param  string $value 需要过滤的值
  * @return string
  */
function filter_sql($value)
{
	$sql = array("select", 'insert', "update", "delete", "\'", "\/\*", 
					"\.\.\/", "\.\/", "union", "into", "load_file", "outfile");
	$sql_re = array("","","","","","","","","","","","");

	return str_replace($sql, $sql_re, $value);
}

/**
  * 安全过滤函数4,过滤特殊有危害字符
  * 
  * @param string $value 需要过滤的数据
  * @return string
  */
function filter_str($value)
{
	$value = str_replace(array("\0","%00","\r"), '', $value); 
	$value = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $value);
	$value = str_replace(array("%3C",'<'), '&lt;', $value);
	$value = str_replace(array("%3E",'>'), '&gt;', $value);
	$value = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $value);

	return $value;
}

/**
  * 计算当前到第2天当前23:59的时长,这段时间缓存不更新
  * 
  * @param string $flags 市场标识
  * @return integer
  */
function getOpenTime($expire, $flags = 'A')
{
	$nowTime = date('His');
	$cacheTime = $expire;

	if(!is_open($flags))
	{
		switch($flags)
		{
			case 'A':
				if($nowTime > '150000' && $nowTime < '235959')
					$cacheTime = (strtotime('235959') - time());
				else if($nowTime > '000000' && $nowTime < '093000')
					$cacheTime = (strtotime('093000') - time());
			break;
			case 'H':
				if($nowTime > '162000' && $nowTime < '235959')
					$cacheTime = (strtotime('235959') - time());
				else if($nowTime > '000000' && $nowTime < '095000')
					$cacheTime = (strtotime('095000') - time());
			break;
		}
		logs('flags:'.$flags.'|nowtime:'.$nowTime.'|cacheTime:'.$cacheTime,'cachetime');
	}
	return $cacheTime;
}

/**
  * 关联数据排序
  *
  * @param array   $array     排序数组
  * @param string  $keys      排序字段 
  * @param integer $ordertype 排序方式 0:倒序 1:升序
  * @param boolean $flag      是否保留原key
  * @return array
  */
function array_sort($array, $keys = 'DiffPriceRate', $ordertype = 0, $flag = FALSE)
{ 
	$keysvalue = $newvalue = array();

	foreach($array as $key => $rs)
	{
		$keysvalue[$key] = $rs[$keys];
	}

	$ordertype ? asort($keysvalue) : arsort($keysvalue);

	reset($keysvalue);

	foreach($keysvalue as $key => $rs)
	{
		$flag ? $newvalue[] = $array[$key] : $newvalue[$key] = $array[$key];
	}
	return $newvalue; 
}

/**
  * 返回js能识别的变量
  *
  * @param array   $data  数据
  * @param integer $total 数据总数-前台分页之用
  * @param string  $func  JS回调函数
  * @return string
  */
function json_var($data, $total, $func = '')
{
    $data = empty($data) ? array() : $data;
    $total = $total > 0 ? $total : 0;
	if($func != '')
		$var = 'var dataArray='.json_encode($data).';'.$func.".GetData(dataArray,{$total});";
	else
		$var = 'var dataArray='.json_encode($data).';';
    exit($var);
}

/**
  * utf-8字符串截取
  *
  * @param string  $datastr 要截取的字符串
  * @param integer $width   要求长度
  * @param boolean $point   是否添加缩略字符
  * @return string
  */
function utf8_cutstr($datastr, $width = 20, $point = FALSE)
{
    $start    = 0;
    $encoding = 'UTF-8';
	$datastr  = trim($datastr);
    
	$trimmarker = $point ? '...' : '';
    
    if($width == '')
        $width = mb_strwidth($str, $encoding);

    return htmlspecialchars(mb_strimwidth($datastr, $start, $width, $trimmarker, $encoding));
}

/**
  * 追加股票代码后缀
  *
  * @param integer $stockid 股票代码
  * @return string
  */
function append_suf($stockid)
{
	if(strlen($stockid) == 6 && is_numeric($stockid))
	{
		/* 股票代号后缀配置 */
		$sufArr = array('6'=>'K', '9'=>'K', '0'=>'J', '2'=>'J', '3'=>'J');

		/* 获取股票代码第一个数字 */
		$firstCode = substr($stockid, 0, 1);
		$codeArr   = array_keys($sufArr);
		
		if(in_array($firstCode, $codeArr))
			$stockid .= $sufArr[$firstCode];
	}
	return $stockid;
}

/**
  * 判断是否为合法沪深股票代码
  *
  * @param array  $stockids 股票代码
  * @param string $flags    市场标识 A:沪深 H:港股 HE:港股指数
  * @return boolean
  */
function is_code($stockids, $flags = 'A')
{
	$rep = array('A' => '/^\d{6}[J|K]{1}$/', 'H' => '/^(\d{5}[A]{1}|\d{7}[A]{1}|SPHKG|SPHKL)$/');
	
	if(!is_array($stockids)) $stockids = array($stockids);

	foreach($stockids as $stockid)
	{
		if(!preg_match($rep[$flags], $stockid))
			return FALSE;
	}
	return TRUE;
}

/**
  * 判断是否为开盘时间,支持休市
  *
  * @param string $flags 市场标识 A:沪深 H:港股
  * @return boolean
  */
function is_open($flags = 'A')
{
	$nowTime = date('Y-m-d H:i:s');
	$time1 = $time2 = $time3 = $time4 = '';

    switch($flags)
	{
		case 'A':
			$holidays = config_item('holidays');

			$time1 = date('Y-m-d 09:30:00');
			$time2 = date('Y-m-d 11:35:00');
			$time3 = date('Y-m-d 13:00:00');
			$time4 = date('Y-m-d 15:05:00');
		break;
		case 'H':
			$holidays = config_item('hk_holidays');

			$time1 = date('Y-m-d 09:30:00');
			$time2 = date('Y-m-d 12:20:00');
			$time3 = date('Y-m-d 13:00:00');
			$time4 = date('Y-m-d 16:20:00');
		break;
	}
	
	if(!empty($holidays) && in_array(date('Y-m-d'), $holidays))
        return FALSE;
	else if(($nowTime >= $time1 && $nowTime <= $time2))
		return TRUE;
	else if($nowTime >= $time3 && $nowTime <= $time4)
        return TRUE;
    else
		return FALSE;
}

/**
  * 判断数组是否为空
  *
  * @param array  $value 需要判断的数组
  * @param string $node  判断节点
  * @return boolean
  */
function is_empty($value, $node = 'list')
{
	if(isset($value[$node]) && !empty($value[$node]))
		return TRUE;
	else
		return FALSE;
}

/**
  * 判断日期合法性
  *
  * @param string $date   需要判断的日期
  * @param string $format 格式
  * @return boolean
  */
function is_date($date, $format = 'Y-m-d H:i:s')
{
	$unixTime  = strtotime($date);
	$checkDate = date($format, $unixTime);

	if($checkDate == $date)
	   return TRUE;
	else
	   return FALSE;
}

/**
  * 判断数字的范围合法性
  *
  * @param array   $number 需要判断的数字
  * @param integer $start  起始范围
  * @param integer $end    结束范围
  * @return boolean
  */
function checkRange($number, $start = 1, $end = 150)
{
	if(!is_array($number)) $number = array($number);

	foreach($number as $rs)
	{
		if($rs > $end || $rs < $start)
			return FALSE;
	}
	return TRUE;
}

/**
  * 数据格式化
  *
  * @param mixed   $value 数据
  * @param integer $limit 保留位数
  * @param string  $mark  替代符号,一般用于数据为null或空字符串情况
  * @return mixed
  */
function formats($value, $limit = 2, $mark = '-')
{
	$value = is_numeric($value) ? floatval($value) : $value;
	
	if(empty($value))
		$value = $mark;
	else if(is_numeric($value))
		$value = number_format($value, $limit, '.', '');

	return $value;
}

/**
 * gbk转utf8
 *
 * @param string $content 需要转换的内容
 * @return string
 */
function gbk_to_utf8($content)
{
    return empty($content) ? '' : iconv('gbk', 'utf-8', $content);
}

/**
 * utf8转gbk
 *
 * @param string $content 需要转换的内容
 * @return string
 */
function utf8_to_gbk($content)
{
    return empty($content) ? '' : iconv('utf-8', 'gbk', $content);
}

/**
 * 记录和统计时间(微秒)和内存使用情况
 * 使用方法:
 * <code>
 * 记录开始标记位 runTime('begin');
 * ... 区间运行代码
 * 记录结束标签位runTime('end');
 * 统计区间运行时间 精确到小数后6位 echo runTime('begin','end',6);
 * 统计区间内存使用情况echo runTime('begin','end','m');
 * 如果end标记位没有定义,则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 *
 */
function runTime($start, $end = '', $dec = 4)
{
    static $_mem  = array();
    static $_info = array();

    if(is_float($end))
	{ 
		/* 记录时间 */
        $_info[$start] = $end;
    }
	else if(!empty($end))
	{ 
		/* 统计时间和内存使用 */
        if(!isset($_info[$end]))
			$_info[$end] = microtime(TRUE);

        if(MEMORY_LIMIT_ON && $dec=='m')
		{
            if(!isset($_mem[$end])) $_mem[$end] = memory_get_usage();
				
            return number_format(($_mem[$end] - $_mem[$start])/1024);
        }
		else
		{
            return number_format(($_info[$end] - $_info[$start]),$dec);
        }
    }
	else
	{	/* 记录时间和内存使用 */
        $_info[$start] = microtime(TRUE);

        if(MEMORY_LIMIT_ON) $_mem[$start] = memory_get_usage();
    }
    return NULL;
}

/**
 * 日志记录
 *
 * @param string $msg  股票代码
 * @param string $file 日志文件名
 * @return void
 *
 */
function logs($msg, $file = 'system')
{
	$log = '['.date('H:i:s').']['.$msg.']'.PHP_EOL;

	$filePath = APPPATH . 'logs/' . $file . '_' . date('Ymd').'.log';

	error_log($log, 3, $filePath);
}

/* End of file func_helper.php */
/* Location: ./application/helper/func_helper.php */