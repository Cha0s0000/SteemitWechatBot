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

}