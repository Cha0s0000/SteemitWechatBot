<?php
namespace app\index\controller;

use think\Controller;
use think\Cookie;

class Index extends Controller
{
    public function index()
    {
    	
		return $this->fetch();

    }   

    public function getname()
    {
    	$inputdata = input('post.');
    	$SteemitName = $inputdata['name'];
    	$validCode = random_code();
    	$data = ['steemitname' => $SteemitName, 'validcode' => $validCode];
    	db('steemitaccount')->insert($data);
    	echo "<script language=JavaScript>alert(\"Send your valicCode to WechatBot. \\n  \\nYour valid Code is : {$validCode}\");location.href='javascript:history.go(-1);';</script>";
 
    }

    public function validwechat()
    {
        define("TOKEN","weixin");
        if (isset($_GET['echostr'])) {
            valid();
        }else{
            $postData = $GLOBALS[HTTP_RAW_POST_DATA];
            if(!$postData)
            {
                echo  "error";
                exit();
            }

            $object = simplexml_load_string($postData,"SimpleXMLElement",LIBXML_NOCDATA);
            $MsgType = $object->MsgType;
            switch ($MsgType) { 
            case 'text':
                $content = $object ->Content;
                $openid = $object ->FromUserName;
                if (strstr($content, "valid"))
                {
                    $validcode = substr($content,6);
                    $valid = db('steemitaccount')->where('validcode', $validcode)->find();
                    if(!empty($valid))
                    {
                        $steemitname = $valid['steemitname'];
                        db('steemitname')->where('validcode',$valid)->update(['openid' => $openid]);
                        $reply = "Successfully bind your wechat to steemitname{{$steemitname}}";
                    }
                    else
                    {
                        $reply = "Please input the right valid code.";
                    }
                }
                else
                {
                     $reply = "Still in developing";
                }


                return replyText($obj,$reply);

                break;
            default: 
                break;
            }
        }

    }

}