<?php

function random_code($length = 8,$chars = null)
    {
	 	if(empty($chars))
	 	{
	 		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
	    $count = strlen($chars) - 1;
	  	$code = '';
	    while( strlen($code) < $length)
    	{
    		$code .= substr($chars,rand(0,$count),1);
	  	}
	  	return $code;
	}


function valid(){
		if(checkSignature())
		{
			$echostr = $_GET["echostr"];
             header('content-type:text');
			echo $echostr;
			exit;
		}
		else
		{
			echo "error";
			exit;
		}
	}

	//检查签名
function checkSignature()
	{
		//获取微信服务器GET请求的4个参数
		$signature = $_GET['signature'];
		$timestamp = $_GET['timestamp'];
		$nonce = $_GET['nonce'];

		//定义一个数组，存储其中3个参数，分别是timestamp，nonce和token
		$tempArr = array($nonce,$timestamp,TOKEN);

		//进行排序
		sort($tempArr,SORT_STRING);

		//将数组转换成字符串

		$tmpStr = implode($tempArr);

		//进行sha1加密算法
		$tmpStr = sha1($tmpStr);

		//判断请求是否来自微信服务器，对比$tmpStr和$signature
		if($tmpStr == $signature)
		{
			return true;
		}
		else
		{
			return false;
		}
	}	

function replyText($obj,$content){
		$replyXml = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
	        //返回一个进行xml数据包

		$resultStr = sprintf($replyXml,$obj->FromUserName,$obj->ToUserName,time(),$content);
	        return $resultStr;		
	}