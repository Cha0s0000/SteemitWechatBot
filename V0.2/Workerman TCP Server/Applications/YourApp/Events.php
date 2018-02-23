<?php

use \GatewayWorker\Lib\Gateway;

class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        // 向当前client_id发送数据 
        // Gateway::sendToClient($client_id, "Hello $client_id\r\n");
        // 向所有人发送
        Gateway::sendToAll("$client_id login\r\n");
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
        $message_type = substr($message,0,2);
        $data = json_decode($message,true);
        $link = $data['parent_permlink'];
        $parent_author = $data['parent_author'];
        $author = $data['author'];
        $body = $data['body'];
        $link = 'https://steemit.com/@'.$parent_author.'/'.$link;

        $appid = 'wx63d349787679880a';
        $appsec = '197d9698db455bc1e71dbe8793b15ea0';  
        $token_url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsec;
        $json_token = http_request($token_url);
        $tokendata=json_decode($json_token,true);
        $access_token = $tokendata['access_token'];
        $template=array(          
          'touser'=>"o6zvTwIbhFW7PIPEhvSJZKKq_CGg",
          'template_id'=>"iwfI1ywCN6CCk5XiU5otISVZrADu9wRKoOyjEElj0PE",
          'url'=>$link,
          'topcolor'=>"#7B68EE",
          'data'=>array(
                  'first'=>array('value'=>urlencode("New comment"),'color'=>"#FF0000"),
                  'author'=>array('value'=>urlencode($author),'color'=>'#173177'),
                  'link'=>array('value'=>urlencode($link),'color'=>'#173177'),
                  'remark'=>array('value'=>urlencode($body),'color'=>'#173177'), )
          );
        $json_template=json_encode($template);
        //echo $json_template;
        //echo $this->access_token;
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
        $res=http_request($url,urldecode($json_template));
        GateWay::sendToAll($res);
        
       
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 
       $close_sql = "UPDATE device set online_or_not='0' where client_id = '$client_id'";
       $result_close = mysql_query($close_sql);
       // GateWay::sendToAll("$client_id logout\r\n");
   }

    
}
function http_request($url,$data=array()){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}