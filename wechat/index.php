<?php
include('library/init.inc.php');

//接收信息
$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
$data = simplexml_load_string($xml);
$temp = '';
$logs = fopen('logs.txt', 'a');
if(isset($data->ToUserName))
{
    $getPublicAccount = 'select `token` from `'.$db_prefix.'publicAccount` where `account`=\''.$db->escape($data->ToUserName).'\' limit 1';
    $publicAccount = $db->fetchRow($getPublicAccount);

    if(!$publicAccount)
    {
        echo '目标服务号不存在';
        exit;
    }

    $token = $publicAccount['token'];
    if(!checkSignature($_GET['signature'], $_GET['timestamp'], $_GET['nonce'], $token))
    {
        echo '请求服务器错误';
        exit;
    }
}

$addLogs = 'insert into logs values (null, \''.$data->FromUserName.'\',\''.$xml.'\',\''.$data->MsgType.'\', \''.$data->ToUserName.'\', null);';
$db->insert($addLogs);
fwrite($logs, 'receive from server:'."\n".$xml."\n");
$responseId = 0;
$response = '';
//如果当前的用户模式是人工应答模式，直接转发到人工客服上
$checkUserMode = 'select `openId` from `'.$db_prefix.'user` where `mode`=\'artificial\' and `modeExpired`>='.time().' and `openId`=\''.$data->FromUserName.'\' limit 1';
if(strtolower($data->MsgType) == 'text' && $db->fetchOne($checkUserMode))
{
    //如果消息是00，则退出人工应答模式
    if($data->Content == '00')
    {
        fwrite($logs, 'exit artificial mode'."\n");
        $updateUser = 'update `'.$db_prefix.'user` set `mode`=\'\',`modeExpired`=0 where `openId`=\''.$data->FromUserName.'\' limit 1';
        $db->update($updateUser);

        $loader->includeClass('Getexpert');
        $responseObj = new Getexpert($data->ToUserName, $data->FromUserName, $data->Content);

        $responseObj->exitMode();
        $response = $responseObj->__toString();
        $addLogs = 'insert into logs values (null, \''.$data->FromUserName.'\',\''.$response.'\',\'response\', \''.$data->ToUserName.'\', null);';
        $db->insert($addLogs);
        fwrite($logs, 'response:'."\n".$response."\n");
        fclose($logs);
        echo $response;
        exit;
    }

    fwrite($logs, 'enter artificial mode'."\n");
    pushMessage($data->Content, $data->FromUserName);
    echo '';
    exit;
}

switch(strtolower($data->MsgType))
{
//文本消息
case 'text':
    $getRules  = 'select `responseId`,`rule`,`matchMode` from `'.$db_prefix.'rules` where `publicAccount`=\''.$db->escape($data->ToUserName).'\'';
    $getRules .= ' and `enabled`=1 order by `orderView`';

    $rules = $db->fetchAll($getRules);
    foreach($rules as $rule)
    {
        if(1 == $rule['matchMode'])//精确匹配
        {
            if($rule['rule'] == $data->Content)
            {
                $responseId = $rule['responseId'];
                break;
            }
        } else {//正则匹配
            if(preg_match($rule['rule'], $data->Content))
            {
                $responseId = $rule['responseId'];
                break;
            }
        }
    }

    if(0 == $responseId)
    {
        //没有符合的规则，返回默认回复
        $getDefaultRule  = 'select `responseId` from `'.$db_prefix.'rules` where `publicAccount`=\''.$db->escape($data->ToUserName).'\' ';
        $getDefaultRule .= ' and `isDefault`=1';
        $rule = $db->fetchRow($getDefaultRule);
        $responseId = $rule['responseId'];   
    }

    break;
//事件消息
case 'event':
    fwrite($logs, "\nEnter event message handler.".strtolower($data->Event)."\n");
    switch(strtolower($data->Event))
    {
    /*
    <xml>
        <ToUserName><![CDATA[toUser]]></ToUserName>
        <FromUserName><![CDATA[FromUser]]></FromUserName>
        <CreateTime>123456789</CreateTime>
        <MsgType><![CDATA[event]]></MsgType>
        <Event><![CDATA[subscribe]]></Event>
        <EventKey><![CDATA[qrscene_123123]]></EventKey>
        <Ticket><![CDATA[TICKET]]></Ticket>
    </xml>
    */
    case 'subscribe'://关注事件
        if(isset($data->EventKey) && strpos($data->EventKey, 'qrscene_') === 0)
        {
            $temp = str_replace('qrscene_', '', $data->EventKey);
            $loader->includeClass('Scan');
            $responseObj = new Scan($data->ToUserName, $data->FromUserName, $temp);
            $responseObj->run();
        }
        $checkUser = 'select `id` from `'.$db_prefix.'user` where `openId`=\''.$db->escape($data->FromUserName).'\'';
        $user = $db->fetchRow($checkUser);
        if(!$user)
        {
            $addUser = 'insert into `'.$db_prefix.'user` (`id`,`openId`,`addTime`,`unsubscribed`,`leaveTime`,`integral`,`publicAccount`) values (null, \''.$db->escape($data->FromUserName).'\', '.time().', 0, 0, 0,\''.$data->ToUserName.'\')';
            $db->insert($addUser);

            $updateUser = 'update `'.$db_prefix.'user` set `path`=\''.$db->getLastId().'\'';
            $db->update($updateUser);
        } else {
            $updateUser = 'update `'.$db_prefix.'user` set `unsubscribed`=0 where `id`='.$user['id'];
            $db->update($updateUser);
        }

        $getResponse  = 'select `responseId` from `'.$db_prefix.'rules` ';
        $getResponse .= 'where `rule`=\'subscribe\' and `publicAccount`=\''.$db->escape($data->ToUserName).'\'';

        $responseRule = $db->fetchRow($getResponse);

        if($responseRule)
        {
            $responseId = $responseRule['responseId'];
        } else {
             //没有符合的规则，返回默认回复
            $getDefaultRule  = 'select `responseId` from `'.$db_prefix.'rules` where `publicAccount`=\''.$db->escape($data->ToUserName).'\' ';
            $getDefaultRule .= ' and `isDefault`=1';
            $rule = $db->fetchRow($getDefaultRule);
            $responseId = $rule['responseId'];
        }
        break;
    case 'unsubscribe'://取消关注事件
        $updateUserStatus  = 'update `'.$db_prefix.'user` set `unsubscribed`=1, `leaveTime`='.time();
        $updateUserStatus .= ' where `openId`=\''.$db->escape($data->FromUserName).'\' limit 1;';
        $db->update($updateUserStatus);
        break;
    case 'location'://上报地理位置
        /*
        <xml>
            <ToUserName><![CDATA[gh_e415303998c0]]></ToUserName>
            <FromUserName><![CDATA[oY8_kjpNBdSXBSij0C3Po11ZBolA]]></FromUserName>
            <CreateTime>1414133877</CreateTime>
            <MsgType><![CDATA[event]]></MsgType>
            <Event><![CDATA[LOCATION]]></Event>
            <Latitude>23.131355</Latitude>
            <Longitude>113.352715</Longitude>
            <Precision>65.000000</Precision>
        </xml>
        */
        break;
    case 'scan'://已关注用户扫描推广二维码
        /*
        <xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[FromUser]]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType><![CDATA[event]]></MsgType>
            <Event><![CDATA[SCAN]]></Event>
            <EventKey><![CDATA[SCENE_VALUE]]></EventKey>
            <Ticket><![CDATA[TICKET]]></Ticket>
        </xml>
         */
        fwrite($logs, "\nEnter scan event handler.\n".$data->ToUserName.$data->EventKey."\n");
        $getRules  = 'select `responseId` from `'.$db_prefix.'rules` where `publicAccount`=\''.$data->ToUserName.'\'';
        $getRules .= ' and `enabled`=1 and `rule`=\'scan_'.$data->EventKey.'\' limit 1;';

        $temp = $data->EventKey;

        $rules = $db->fetchRow($getRules);

        if($rules)
        {
            $responseId = $rules['responseId'];
        } else {
             //没有符合的规则，返回默认回复
            $getDefaultRule  = 'select `responseId` from `'.$db_prefix.'rules` where `publicAccount`=\''.$db->escape($data->ToUserName).'\' ';
            $getDefaultRule .= ' and `isDefault`=1';
            $rule = $db->fetchRow($getDefaultRule);
            $responseId = $rule['responseId'];
        }
        break;
    case 'click'://点击事件菜单
        /*
        <xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[FromUser]]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType><![CDATA[event]]></MsgType>
            <Event><![CDATA[CLICK]]></Event>
            <EventKey><![CDATA[EVENTKEY]]></EventKey>
        </xml>
         */
        fwrite($logs, "\nEnter click event handler.\n".$data->ToUserName.$data->EventKey."\n");
        $getRules  = 'select `responseId` from `'.$db_prefix.'rules` where `publicAccount`=\''.$data->ToUserName.'\'';
        $getRules .= ' and `enabled`=1 and `rule`=\'click_'.$data->EventKey.'\' limit 1;';

        $rules = $db->fetchRow($getRules);

        if($rules)
        {
            $responseId = $rules['responseId'];
        } else {
             //没有符合的规则，返回默认回复
            $getDefaultRule  = 'select `responseId` from `'.$db_prefix.'rules` where `publicAccount`=\''.$db->escape($data->ToUserName).'\' ';
            $getDefaultRule .= ' and `isDefault`=1';
            $rule = $db->fetchRow($getDefaultRule);
            $responseId = $rule['responseId'];
        }
        break;
    case 'view'://点击链接菜单
        /*
        <xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[FromUser]]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType><![CDATA[event]]></MsgType>
            <Event><![CDATA[VIEW]]></Event>
            <EventKey><![CDATA[www.qq.com]]></EventKey>
        </xml>
         */
        break;
    }
    break;
//图片消息
case 'image':
//语音消息
case 'voice':
//视频消息
case 'video':
//地理位置消息
case 'location':
//连接消息
case 'link':
    break;
default:
    echo $_GET['echostr'];
    exit;
}
if($responseId <= 0)
{
            $getDefaultRule  = 'select `responseId` from `'.$db_prefix.'rules` where `publicAccount`=\''.$db->escape($data->ToUserName).'\' ';
            $getDefaultRule .= ' and `isDefault`=1';
            $rule = $db->fetchRow($getDefaultRule);
            $responseId = $rule['responseId'];
}
if(0 < $responseId)
{
    $getResponse  = 'select `id`,`msgType`,`content`,`title`,`description`,`musicUrl`,`HQMusicUrl`,`url`,`picUrl`,`mediaId`,`thumbMediaId` from ';
    $getResponse .= '`'.$db_prefix.'response` where `id`='.$responseId;

    $responseRule = $db->fetchRow($getResponse);

    switch($responseRule['msgType'])
    {
        case 'text':
            $responseObj = new TextResponse($data->ToUserName, $data->FromUserName, $responseRule['content']);
            $response = $responseObj->__toString();
            break;
        case 'news':
            $items[] = array(
                'title' => $responseRule['title'],
                'description' => $responseRule['description'],
                'picUrl' => $responseRule['picUrl'],
                'url' => $responseRule['url'],
            );
            $getSubNews = 'select r.* from '.$db_prefix.'newsMapping as m';
            $getSubNews .= ' left join '.$db_prefix.'response as r on m.subId = r.id';
            $getSubNews .= ' where r.msgType = \'news\' and m.mainId = '.$responseRule['id'];
            $subNews = $db->fetchAll($getSubNews);
            if( $subNews ) {
                foreach( $subNews as $subNew) {
                    $items[] = array(
                        'title' => $subNew['title'],
                        'description' => $subNew['description'],
                        'picUrl' => img_url_to_wechat($subNew['picUrl']),
                        'url' => $subNew['url'],
                    );
                }
            }

//            $title = unserialize($responseRule['title']);
//            $description = unserialize($responseRule['description']);
//            $picUrl = unserialize($responseRule['picUrl']);
//            $url = unserialize($responseRule['url']);
//
//            $items = array();
//            foreach($title as $key=>$value)
//            {
//                $items[] = array(
//                    'title' => $value,
//                    'description' => $description[$key],
//                    'picUrl' => $picUrl[$key],
//                    'url' => $url[$key]
//                );
//            }

            $responseObj = new NewsResponse($data->ToUserName, $data->FromUserName, $items);
            $response = $responseObj->__toString();
            break;
        case 'themes':
            fwrite($logs, "\nEnter themes.".$responseRule['content']."\n");
            $content = $responseRule['content'];

            $loader->includeClass($content);
            $responseObj = new $content($data->ToUserName, $data->FromUserName, isset($data->Content) ? $data->Content : $temp);

            $responseObj->run();
            $response = $responseObj->__toString();

            fwrite($logs, "End call themes\n");
            break;
        case 'modules':
            $content = $response['content'];
            $loader->includeClass($content);
            $responseObj = new $content($data->ToUserName, $data->FromUserName, $data->Content);

            $responseObj->run();
            $response = $responseObj->__toString();

            break;
	    case 'multiservertransfer':
	        $content = 'MultiServerTransfer';
    	    $loader->includeClass($content);
	        $responseObj = new $content($data->ToUserName, $data->FromUserName, 'winsen@hnspyjc');
	        $response = $responseObj->__toString();

    	    break;
    }
}

$addLogs = 'insert into logs values (null, \''.$data->FromUserName.'\',\''.$response.'\',\'response\', \''.$data->ToUserName.'\', null);';
$db->insert($addLogs);
fwrite($logs, 'response:'."\n".$response."\n");
fclose($logs);
echo $response;
