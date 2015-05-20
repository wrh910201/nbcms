<?php
error_reporting(E_ALL^E_NOTICE);
class ApipostAction{
 
    private $_appkeys = 'e58df07b39875e3b20a018dc';
    private $_masterSecret = '0a8a4fad86203ba9b1a258a6';
 
    function request_post($url="",$param="",$header="") {
        if (empty($url) || empty($param)) {
        return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        // 增加 HTTP Header（头）里的字段 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);//运行curl
     
        curl_close($ch);
        return $data;
    }
 
    function send($audience,$notification,$message) 
    {
        $url = 'https://api.jpush.cn/v3/push';
        $base64=base64_encode("$this->_appkeys:$this->_masterSecret");
        $header=array("Authorization:Basic $base64","Content-Type:application/json");
        // print_r($header);
        //$param='{"platform":"all","audience":{"tag":["delivery"]},"notification" : {"alert" : "Hi,JPush!"},"message":{"msg_content":"'.$message.'","title":"'.$title.'"}}';
        $param = array(
        	'platform' => "all",
        	'audience' => $audience,
        //	'message'  => $message,
        	'notification' => $notification
        );
        $res = $this->request_post($url,json_encode($param),$header);
        $res_arr = json_decode($res, true);
    }
}
/*
$jpush=new ApipostAction();
$audience = array(
	"tag" => array("admin", "service")
//	"alias" => array()
);
$notification = array(
	"android" => array(
			"alert" => "Hi, JPush!",
			"extras" => array("target" => "OrderDetail", "order_id" => 2)
		)
);
$message = array(
	"msg_content" => "Hello, you cannot see me on the time.",
	"extras" => array("host" => "http://www.jkdzsw.com")
);
$jpush->send($audience, $notification, $message);
*/
