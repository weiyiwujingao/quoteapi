<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0" name="viewport">
<meta name="format-detection" content="telephone=no">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<title>手机分时行情图</title>
</head>
<body>
<canvas id="Chart" width="640" height="400" style="width:320px;height:200px;"><!-- style="width:296px;height:200px;"-->
</canvas>
<div id="ds" style="font: normal Arial, Helvetica, sans-serif;">
</div>
<script type="text/javascript" src="http://hs.3g.cnfol.com/f=ue/Js/Cms/JqPack.js,ui/Js/Chart/MovbilShare.js"></script>
<!--<script type="text/javascript" src="MovbilShare.js"></script>-->
<script>
	var chart = {
		CavId:"Chart",
		UpColor:"#ff0000",
		DownColor:"#06c871",
		LasColor:"#373737",
		PricLineColor:"#39a6fe",
		AvrLinColor:"#efc660",
		HorLineColor:"#c4e5ff",
		TimeColor:"#979797",
		VolBoxColor:"#bfbfbf",
		VolUpCor:"#ff2e2e",
		VolDownCor:"#3fb13e",	
		CavW:640,//同canvas宽高
		CavH:400,
		TopSpace:25,
		LeftSpace:90,
		PrcAreW:470,/*建议242的整数倍*/
		PrcAreH:265,
		TimLnH:38,
		VolAreH:70,
		VolAreW:470,
		Data:{
			high:0,
			clow:99999999999,
			last:0,
			volMax:0,
			timShr:[]//[[价格，均价，成交量,成交量颜色标志],[]……]
		}
	}

	var quotes_api = 'http://quotes2.api.3g.cnfol.com/';

	$.ajax({
		url: quotes_api + 'chart.html',
		cache: false,
		type: 'get',
		dataType: 'jsonp',
		jsonp: 'callback',
		data: {
			/* 固定服务号 */
			action: 'getrline',
			/* 操作类型 */
			stockid: "<?=empty($_GET['stockid']) ? '000001J' : $_GET['stockid']?>",
			/* 用户ID */
			days: 1,
			/* 股票代码 */
			lasttime: 1428371700
		},
		success: function(result)
		{
			if(result.list.length>0){
				chart.Data.last = result.stock;
				var list = result.list;
				for(var i = 1;i<list.length;i++){
					var prc = parseFloat(list[i][1]),
						avprc = parseFloat(list[i][2]),
						vol = parseFloat(list[i][4]),
						corflg = list[i][6]=='1'?1:0;
					chart.Data.high = chart.Data.high>prc?chart.Data.high:prc;
					chart.Data.low = chart.Data.low<prc?chart.Data.low:prc;
					chart.Data.volMax = chart.Data.volMax>vol?chart.Data.volMax:vol;
					chart.Data.timShr.push([prc,avprc,vol,corflg])
				}
				DrwChart(chart);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			/* 请求异常根据自己需要处理 */
		}
	});
</script>
<!--cnzz-->
<script type="text/javascript">
var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1253241311'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "w.cnzz.com/q_stat.php%3Fid%3D1253241311' type='text/javascript'%3E%3C/script%3E"));
</script>
<!--baidu-->
<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F37569f5d0e71676f40b86f173b2e00fa' type='text/javascript'%3E%3C/script%3E"));
</script>
</body>
</html>