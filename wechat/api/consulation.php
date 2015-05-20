<?php
/**
 * 支持各个分离系统的会员信息对接
 * @author winsen
 * @date 2014-11-20
 */
include '../library/init.inc.php';

//接收数据统一使用json格式的RSA私钥加密的密文的base64编码字符串
//返回也是相同的格式
$openId = !empty($_POST['openId']) ? addslashes($_POST['openId']) : '';
$content = !empty($_POST['content']) ? addslashes($_POST['content']) : '';
$opera = !empty($_POST['operation']) ? addslashes($_POST['operation']) : '';
$name = !empty($_POST['name']) ? addslashes($_POST['name']) : '';
$time = !empty($_POST['time']) ? addslashes($_POST['time']) : '';
$server = !empty($_POST['server']) ? addslashes($_POST['server']) : '';
$response = array('result'=>false, 'content'=>'', 'history'=>null, 'data'=>null);
$log = fopen('api.txt', 'a');

if('pull' == $opera && '' != $openId)
{
    $getHistory = 'select `username`,`type`,`content`,`addTime` from `'.$db_prefix.'consulation` where `openId`=\''.$openId.'\' order by `addTime` ASC';

    $history = $db->fetchAll($getHistory);
    if($history)
    {
        $response['result'] = true;
        $response['history'] = $history;
    }
}

if('list' == $opera)
{
    $getList = 'select `from`,a.`name` as username, b.`openId`,b.`content` as msg, b.`id` as msgId, \'consulation\' as target from `'.
                $db_prefix.'user` as a join `'.$db_prefix.'consulation` as b using(`openId`) where b.`addTime`<'.(time()+168800);
    if($server != '')
    {
        $getList .= ' and `from`=\''.$server.'\' ';
    }
    $getList .= ' group by b.`openId` ';
    if($time != '')
    {
        $getList .= ' order by b.`add_time` '.$time;
    } else {
        $getList .= ' order by b.`add_time` desc';
    }
    $list = $db->fetchAll($getList);

    if($list)
    {
        foreach($list as $k=>$v)
        {
            $list[$k]['extras'] = json_encode($v);
        }
        $response['data'] = $list;
        $response['result'] = true;
    } else {
        $response['result'] = false;
    }
}

if('send' == $opera && '' != $openId && '' != $content)
{
    //向微信服务器发送信息
    $getInfo = 'select `appID`,`appsecret`,`expireTime`,`accessToken` from `'.$db_prefix.'publicAccount` where `account`=\'gh_e415303998c0\'';
    $info = $db->fetchRow($getInfo);

    $accessToken = '';
    if($info['expireTime'] >= time())
    {
        $accessToken = $info['accessToken'];
    } else {
        $accessToken = getAccessToken($info['appID'], $info['appsecret']);
    }

    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s';
    $url = sprintf($url, $accessToken);

    $data = '{"touser":"%s","msgtype":"text","text":{"content":"%s"}}';
    $data = sprintf($data, $openId, $content);

    fwrite($log, $url."\n");
    fwrite($log, $data."\n");

    $result = rawPost($url, $data);
    fwrite($log, $result);
    
    $response['result'] = true;
    $response['content'] = $content;
    $response['from'] = 'server';
    $addHistory = 'insert into `'.$db_prefix.'consulation` (`openId`,`content`,`type`,`addTime`) values (\'%s\',\'%s\',1,%d)';
    $addHistory = sprintf($addHistory, $openId, $content, time());
    $db->insert($addHistory);

    //推发送推送信息
    include '../library/JPush.class.php';
    $jpush = new ApipostAction();
    $audience = array(
		    'tag' => array('admin')
    );

    $message = array(
		'android' => array(
				'title' => '回复消息成功',
				'extras' => array('username' => $name, 'msg' => "".$content, 'openId'=>$openId, 'target'=>'consulation', 'msgId'=> $msgId,'from'=>'server')
			)
	);
	$notification = $message;
	$message['android']['msg_content'] = $message['android']['title'];
	$message = $message['android'];
	$jpush->send($audience, null, $message);
}
fclose($log);
$response = json_encode($response);
echo $response;
