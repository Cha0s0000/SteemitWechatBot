<?php
include_once("conn.php"); 
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
   // Type of the block data:
   //  1. vote   
   //  2. comment  
   //  3. transfer    
   //  4. custom_json   follow 
   //  5. claim_reward_balance  
   //  6. comment_options
   //  7.delegate_vesting_shares   
   //  8.account_create_with_delegation

   public static function onMessage($client_id, $message)
   {
        $data = json_decode($message,true);
        $data_type = $data[0];
        // GateWay::sendToAll($message);
        if($data_type == 'comment')
        {
          $parent_author = $data[1]['parent_author'];
          $check = check_user($parent_author);
          if($check != 'none')
          {
            $link = $data[1]['parent_permlink'];          
            $author = $data[1]['author'];
            $body = $data[1]['body'];
            $link = 'https://steemit.com/@'.$parent_author.'/'.$link;
            $user = $check;      
            GateWay::sendToAll($message);
            $res = comment_mention($user,$link,$author,$body); 

            $res_decode = json_decode($res);
            if($res_decode['errcode'] != 0)
            {
              $content = "You have a new comment.\n\nauthor:{$author}\n\nbody:{$body}\n\nlink:{$link}";
              $res_service = service_mention_text($user,$content);
            }       
          }
                    
        }
        else if($data_type == 'vote')
        {
          $author = $data[1]['author'];
          $check = check_user($author);
          if($check != 'none')
          {
            $link = $data[1]['permlink'];          
            $voter = $data[1]['voter'];
            $weight = $data[1]['weight'];
            $link = 'https://steemit.com/@'.$author.'/'.$link;
            $user = $check;  
            GateWay::sendToAll($message);
            $res = vote_mention($user,$link,$voter,$author,$weight);

            if($res_decode['errcode'] != 0)
            {
              $content = "You have a new vote.\n\nvoter:{$voter}\n\nweight:{$weight}\n\nlink:{$link}";
              $res_service = service_mention_text($user,$content);
            }  
          }
        }

        else if($data_type == 'comment_options')
        {
          $author = $data[1]['author'];
          $check = check_user($author);
          if($check != 'none')
          {
            $link = $data[1]['permlink'];          
            $max_accepted_payout = $data[1]['max_accepted_payout'];
            $allow_votes = $data[1]['allow_votes'];
            $link = 'https://steemit.com/@'.$author.'/'.$link;
            $user = $check;  
            GateWay::sendToAll($message); 
            $res = post_mention($user,$link,$max_accepted_payout,$author,$allow_votes);
            if($res_decode['errcode'] != 0)
            {
              $content = "You have a new post.\n\nmax_accepted_payout:{$max_accepted_payout}\n\nallow_votes:{$allow_votes}\n\nlink:{$link}";
              $res_service = service_mention_text($user,$content);
            }  
          }
        }

        else if($data_type == 'delegate_vesting_shares')
        {
          $delegator = $data[1]['delegator'];
          $check = check_user($delegator);
          if($check != 'none')
          {
            $delegatee = $data[1]['delegatee'];          
            $vesting_shares = $data[1]['vesting_shares'];
            $link = 'https://steemit.com';
            $user = $check;  
            
            GateWay::sendToAll($message); 
            $res = delegator_mention($user,$link,$delegator,$delegatee,$vesting_shares);

            if($res_decode['errcode'] != 0)
            {
              $content = "You have a new delegate.\n\ndelegatee:{$delegatee}\n\nvesting_shares:{$vesting_shares}\n\nlink:{$link}";
              $res_service = service_mention_text($user,$content);
            }  
          }
        }


         else if($data_type == 'custom_json')
        {
          $custom_json = $data[1]['json'];
          $custom_json_decode = json_decode($custom_json,true);
          $follower = $custom_json_decode[1]['follower'];
          $following = $custom_json_decode[1]['following'];
          $check = check_user($follower); 
          if($check != 'none')
          {        
            $link = 'https://steemit.com';
            $user = $check;
            
            GateWay::sendToAll($message); 
            $res = follow_mention($user,$link,$follower,$following);
            if($res_decode['errcode'] != 0)
            {
              $content = "You have a new follow.\n\nfollower:{$follower}\n\nfollowing:{$following}\n\nlink:{$link}";
              $res_service = service_mention_text($user,$content);
            }  
          }
          
        }

         else if($data_type == 'transfer')
        {
          $from = $data[1]['from'];
          $check = check_user($from);
          if($check != 'none')
          { 
            $to = $data[1]['to'];          
            $amount = $data[1]['amount'];
            $memo = $data[1]['memo'];
            $link = 'https://steemit.com';
            $user = $check;
    
            GateWay::sendToAll($message); 
            $res = transfer_mention($user,$link,$from,$to,$amount,$memo);
            if($res_decode['errcode'] != 0)
            {
              $content = "You have a new transfer.\n\nto:{$to}\n\namount:{$amount}\n\nmemo:{$memo}\n\nlink:{$link}";
              $res_service = service_mention_text($user,$content);
            }  
          }
        }

       
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

function check_user($name){
    $sql_query = mysql_query("SELECT openid from steemitaccount where steemitname = {$name} limit 1");
    if(mysql_num_rows($sql_query))
    {
      $check = mysql_fetch_array($sql_query);
      $openid = $check['openid'];
      return $openid;     
    }
    else
      return "none";
}

function get_access_token(){
    $appid = 'wx63d349787679880a';
    $appsec = '197d9698db455bc1e71dbe8793b15ea0';  
    $token_url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsec;
    $json_token = http_request($token_url);
    $tokendata=json_decode($json_token,true);
    $access_token = $tokendata['access_token'];
    return $access_token;
}

function comment_mention($user,$link,$author,$body){
    $access_token = get_access_token();
    $template=array(          
    'touser'=>"$user",
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
    return $res;
}

function vote_mention($user,$link,$voter,$author,$weight){
    $access_token = get_access_token();
    $template=array(          
    'touser'=>"$user",
    'template_id'=>"KRlNgLHFzJitrPhJ7BYPBUQCsooWZwDmKrWn_xpg8tU",
    'url'=>$link,
    'topcolor'=>"#7B68EE",
    'data'=>array(
            'first'=>array('value'=>urlencode("New vote"),'color'=>"#FF0000"),
            'voter'=>array('value'=>urlencode($voter),'color'=>'#173177'),
            'author'=>array('value'=>urlencode($author),'color'=>'#173177'),
            'link'=>array('value'=>urlencode($link),'color'=>'#173177'),
            'weight'=>array('value'=>urlencode($weight),'color'=>'#173177'),)
    );
    $json_template=json_encode($template);
    //echo $json_template;
    //echo $this->access_token;
    $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
    $res=http_request($url,urldecode($json_template));
    return $res;
}

function post_mention($user,$link,$max_accepted_payout,$author,$allow_votes){
    $access_token = get_access_token();
    $template=array(          
    'touser'=>"$user",
    'template_id'=>"is3aKbsT63FOqgiJarVB_gWg4fKN_qP3nkQgxVuAE2Q",
    'url'=>$link,
    'topcolor'=>"#7B68EE",
    'data'=>array(
            'first'=>array('value'=>urlencode("New post"),'color'=>"#FF0000"),
            'author'=>array('value'=>urlencode($author),'color'=>'#173177'),            
            'link'=>array('value'=>urlencode($link),'color'=>'#173177'),
            'max_accepted_payout'=>array('value'=>urlencode($max_accepted_payout),'color'=>'#173177'),
            'allow_votes'=>array('value'=>urlencode($allow_votes),'color'=>'#173177'),)
    );
    $json_template=json_encode($template);
    //echo $json_template;
    //echo $this->access_token;
    $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
    $res=http_request($url,urldecode($json_template));
    return $res;
}

function  delegator_mention($user,$link,$delegator,$delegatee,$vesting_shares){
    $access_token = get_access_token();
    $template=array(          
    'touser'=>"$user",
    'template_id'=>"qu9BndgHE3UCTDL6l1C9HozaIZY4SlBDBfQy7jtOAd0",
    'url'=>$link,
    'topcolor'=>"#7B68EE",
    'data'=>array(
            'first'=>array('value'=>urlencode("New delegate"),'color'=>"#FF0000"),
            'delegator'=>array('value'=>urlencode($delegator),'color'=>'#173177'),            
            'delegatee'=>array('value'=>urlencode($delegatee),'color'=>'#173177'),
            'vesting_shares'=>array('value'=>urlencode($vesting_shares),'color'=>'#173177'),)
    );
    $json_template=json_encode($template);
    //echo $json_template;
    //echo $this->access_token;
    $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
    $res=http_request($url,urldecode($json_template));
    return $res;
}

function  follow_mention($user,$link,$follower,$following){
    $access_token = get_access_token();
    $template=array(          
    'touser'=>"$user",
    'template_id'=>"WZFizAnSmwiGxa5F0Wnpa8ZbyelZupYnwc0wgZ22Ris",
    'url'=>$link,
    'topcolor'=>"#7B68EE",
    'data'=>array(
            'first'=>array('value'=>urlencode("New follow"),'color'=>"#FF0000"),
            'follower'=>array('value'=>urlencode($follower),'color'=>'#173177'),            
            'following'=>array('value'=>urlencode($following),'color'=>'#173177'),)
    );
    $json_template=json_encode($template);
    //echo $json_template;
    //echo $this->access_token;
    $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
    $res=http_request($url,urldecode($json_template));
    return $res;
}

function transfer_mention($user,$link,$from,$to,$amount,$memo){
    $access_token = get_access_token();
    $template=array(          
    'touser'=>"$user",
    'template_id'=>"mjtWel5U_FGet3d3eh31Ri1S1YT2sYtNsaR57-I3qfI",
    'url'=>$link,
    'topcolor'=>"#7B68EE",
    'data'=>array(
            'first'=>array('value'=>urlencode("New transfer"),'color'=>"#FF0000"),
            'from'=>array('value'=>urlencode($from),'color'=>'#173177'),            
            'to'=>array('value'=>urlencode($to),'color'=>'#173177'),
            'amount'=>array('value'=>urlencode($amount),'color'=>'#173177'),
            'memo'=>array('value'=>urlencode($memo),'color'=>'#173177'),)
    );
    $json_template=json_encode($template);
    //echo $json_template;
    //echo $this->access_token;
    $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
    $res=http_request($url,urldecode($json_template));
    return $res;
}

function service_mention_text($user,$content){
    $access_token = get_access_token();
    $data = '{
          "touser":"'.$user.'",
          "msgtype":"text",
          "text":
          {
               "content":"'.$content.'"
          }
      }';
     $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
     $res=http_request($url,$data);
     return $res;

}
function service_mention_news($user,$title,$description,$picurl){
    $access_token = get_access_token();
    $data = '{
          "touser":"'.$user.'",
          "msgtype":"news",
          "news":
          {
               "articles":[
                    "title":"'.$title.'",
                    "description":"'.$description.'",
                    "url":"'.$url.'",
                    "picurl":"'.$picurl.'"
                  ]
          }
      }';
     $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
     $res=http_request($url,$data);
     return $res;

}