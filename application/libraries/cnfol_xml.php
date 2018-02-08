<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * xml解析类,Codeigniter专用 v2.0
 * 使用方法
 * 1.将文件放入application/libraries/
 * 2.程序中载入$this->load->library('cnfol_xml','解析数据'));
 * 3.程序中调用$this->cnfol_xml->xml_to_array('需要解析的数据');
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2015-09-03
 ****************************************************************/
class Cnfol_xml
{
	/* xml 资源句柄 */
	private $handle = null;

   /**
	 * 构造函数
	 *
	 * @param string $string 需要解析的字符串
	 * @return void
	 */
	function __construct($string = '')
	{
		if (!empty($string)) 
		{
			$this->xml = new SimpleXMLElement($string);
		}
	}

   /**
	 * 装载xml解析器并解析
	 *
	 * @param string $string 需要解析的字符串
	 * @return mixed
	 */
	function loadStr($string = '')
	{
		if(empty($string)) return FALSE;
			
		$rs = simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);

		if($rs) 
		{
			$this->object_to_array($rs);

			return $rs;
		}
		else
		{
			return FALSE;
		}
	}

   /**
	 * 对象转数组
	 *
	 * @param object $object 需要转换的对象
	 * @return void
	 */
	function object_to_array(&$object) 
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
}

/* End of file cnfol_xml.php */
/* Location: ./application/libraries/cnfol_xml.php */