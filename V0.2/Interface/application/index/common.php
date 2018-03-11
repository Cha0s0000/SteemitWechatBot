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

	//check signature
function checkSignature()
	{
		//GET the four parameters from WeChat server
		$signature = $_GET['signature'];
		$timestamp = $_GET['timestamp'];
		$nonce = $_GET['nonce'];

		//create a array to save the parameters
		$tempArr = array($nonce,$timestamp,TOKEN);

		//sorting
		sort($tempArr,SORT_STRING);

		//change into string

		$tmpStr = implode($tempArr);

		//encryption with sha1
		$tmpStr = sha1($tmpStr);

		//Determine whether the request comes from the WeChat server, versus $tmpStr and $signature.
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
	        //return a xml format data

		$resultStr = sprintf($replyXml,$obj->FromUserName,$obj->ToUserName,time(),$content);
	        return $resultStr;		
	}
