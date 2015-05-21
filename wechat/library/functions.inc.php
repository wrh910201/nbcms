<?php
/**
 * 公共函数库
 * @author winsen
 * @version 1.0.0
 * @date 2014-10-24
 */

/**
 * 权限检查函数
 * @param int $sys_purview 系统定义的权限
 * @param int $user_purview 用户的权限
 * @return bool 拥有该权限时返回true,否则返回false
 * @author winsen
 */
//function checkPurview($sys_purview, $user_purview)
//{
//    return $sys_purview & $user_purview;
//}

function checkPurview($sys_purview, $user_purview) {
    $user_purview = json_decode($user_purview);
    $has_power = false;
    foreach( $user_purview as $key => $value ) {
        if( in_array($sys_purview, $value) ) {
            $has_power = true;
            break;
        }
    }
    return $has_power;
}

/**
 * 权限合并
 * @param int $user_purview 用户的权限
 * @param mixed $purviewList 需要合并的权限列表
 * @return int 返回合并后的权限
 * @author winsen
 */
function combilePurview($user_purview, $purviewList)
{
    $temp_purview = $user_purview;
    if(is_array($purviewList))
    {
        foreach($purviewList as $purview)
        {
            $temp_purview |= $purview;
        }
    }

    if(is_int($purviewList))
    {
        $temp_purview |= $purviewList;
    }

    return $temp_purview;
}

/**
 * smarty assign函数
 * @param string $var 参数名
 * @param mixed $value 参数值
 * @return void
 * @author winsen
 */
function assign($var, $value)
{
    global $smarty;
    $smarty->assign($var, $value);
}

/**
 * 获取GET的参数封装
 * @param string $var 参数名
 * @return mixed 返回对应的参数,如果参数不存在,则返回null
 * @author winsen
 */
function getGET($var)
{
    if(isset($_GET[$var]))
    {
        return $_GET[$var];
    } else {
        return null;
    }
}

/**
 * 获取POST的参数封装
 * @param string $var 参数名
 * @return mixed 返回对应的参数,如果参数不存在,则返回null
 * @author winsen
 */
function getPOST($var)
{
    if(isset($_POST[$var]))
    {
        return $_POST[$var];
    } else {
        return null;
    }
}

/**
 * 验证页面的act或opera值的合法性
 * @param string $needle 合法操作字符串,多个操作用|分隔开
 * @param string $search 待验证的操作
 * @param string $default 若为非法操作,则采用默认值替换
 * @author winsen
 */
function checkAction($needle, $search, $default = '')
{
    if(!$needle || false === strpos($needle, $search))
    {
        return $default;
    } else {
        return $search;
    }
}

/**
 * 显示系统信息
 * @param string $msg 系统提示的文本信息
 * @param mixed $links 自动跳转以及其他链接
 * @param int $time 自动跳转计时
 * @return void
 * @author winsen
 */
function showSystemMessage($msg, $links = array(), $time = 10)
{
    global $smarty;
    assign('message', htmlspecialchars($msg));
    if(count($links) > 0)
    {
    	assign('link', $links[0]['link']);
        assign('links', $links);
    } else {
    	assign('link', $_SERVER['HTTP_REFERER']);
        assign('links', array(array('alt'=>'返回上一页', 'link'=> $_SERVER['HTTP_REFERER'])));
    }
    assign('time', $time);
    assign('page_title', '系统信息');
    $smarty->display('message.phtml');
    exit;
}

/**
 * 获取access_token
 * @param string $appId 公众号appid
 * @param string $secretKey 公众号密钥appsecret
 * @return string 成功时返回获取的access_token,失败时返回false
 * @author winsen
 * @date 2014-10-24
 */
function getAccessToken($appId, $secretKey)
{
    global $errors;
    $url_getAccessToken = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
    $url = sprintf($url_getAccessToken, $appId, $secretKey);

    $data = get($url, null);
    $response = json_decode($data);

    if(!empty($response->errcode))
    {
        echo $response->errmsg.':'.$errors[$response->errcode];
        return false;
    } else {
        return $response->access_token;
    }
}

/**
 * 微信接入开发者模式验证URL以及接收用户信息时使用
 * @param string $signature 微信加密签名
 * @param string $timestamp 时间戳
 * @param string $nonce 随机数
 * @param string $token 公众号设置的Token
 * @return bool
 * @author winsen
 * @date 2014-10-24
 */
function checkSignature($signature, $timestamp, $nonce, $token)
{
	$token = $token;
	$tmpArr = array($token, $timestamp, $nonce);
	sort($tmpArr, SORT_STRING);
	$tmpStr = implode($tmpArr);
	$tmpStr = sha1($tmpStr);
	
    if( $tmpStr == $signature )
    {
		return true;
	} else {
		return false;
	}
}

/**
 * CURL的GET方法
 * @param string $url 目标链接
 * @param string $params 附带的参数，格式为 param1=value1&param2=value2
 * @return string 通过GET方法获取到的网页数据
 * @author winsen
 * @date 2014-10-24
 */
function get($url, $params = '')
{
    $curl = curl_init();
    if($params != '')
    {
        $url .= '?'.$params;
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

/**
 * CURL的POST方法
 * @param string $url 目标链接
 * @param string $params 附带的参数，格式为 param1=value1&param2=value2 或数组 array(param1=>value1,param2=>value2)
 * @param bool $encode 若参数为数组，则使用true（默认）
 * @return string 通过POST方法获取到的网页数据
 * @author winsen
 * @date 2014-10-24
 */
function post($url, $params = array(), $encode = true)
{
    $postParams = '';
    if($encode)
    {
        foreach($params as $key=>$value)
        {
            if('' != $postParams)
            {
                $postParams .= '&';
            }
            $postParams .= $key.'='.urlencode($value);
        }
    } else {
        $postParams = $params;
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postParams);
    curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

/**
 * CURL的POST方法 用于发送不带参数的数据
 * @param string $url 目标链接
 * @param string $data 附带的参数
 * @return string 通过POST方法获取到的网页数据
 * @author winsen
 * @date 2014-11-20
 */
function rawPost($url, $data)
{
    $curl = curl_init();
    $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this_header);
    curl_setopt($curl, CURLOPT_URL, $url);
//  curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//  curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

/**
 * 判断数据源是否是微信
 * @return bool
 * @author winsen
 * @date 2014-10-24
 */
function is_weixin()
{ 
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) 
    {
			return true;
	}	
	return false;
}

/**
 * 判断用户是否已登陆
 * @return bool
 * @author winsen
 * @date 2014-11-4
 */
function checkLogin()
{
    if(isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0)
    {
        return true;
    } else {
        return false;
    }
}

/**
 * 将信息发送到APP
 * @param string $content 用户的信息
 * @param string $openId 用户openId
 * @return void
 * @author winsen
 * @date 2014-12-24
 */
function pushMessage($content, $openId)
{
    global $db;
    global $db_prefix;
    $openId = addslashes($openId);

    if(!file_exists($openId.'-log'))
    {
        $temp = fopen($openId.'-log', 'x');
        fclose($temp);
    }
    $getName = 'select `name` from `'.$db_prefix.'user` where `openId`=\''.$openId.'\' limit 1';
    $name = $db->fetchOne($getName);

    $addConsulation = 'insert into `'.$db_prefix.'consulation` (`openId`,`content`,`type`,`addTime`,`username`) values (\'%s\',\'%s\',\'%s\',%d,\'%s\')';
    $addConsulation = sprintf($addConsulation, $openId, $content, 0, time(), $name);
    $db->insert($addConsulation);
    $msgId = $db->getLastId();
    //记录到文本日记中
    $log = fopen($openId.'-log', 'a');
    $logContent = '%s:%s:%s';
    $logContent = sprintf($logContent, $openId, date('Y-m-d H:i:s'), $content);
    $logContent = base64_encode($logContent).'|';
    fwrite($log, $logContent);
    fclose($log);
        
    //推发送推送信息
    include 'JPush.class.php';
    $jpush = new ApipostAction();
    $audience = array(
		    'tag' => array('admin')
    );

	$message = array(
		'android' => array(
				'title' => '收到来自亲友'.$name.'的咨询',
				'extras' => array('username' => $name, 'msg' => "".$content, 'openId'=>$openId, 'target'=>'consulation', 'msgId'=> $msgId,'from'=>'client')
			)
	);
	$notification = $message;
	$message['android']['msg_content'] = $message['android']['title'];
	$message = $message['android'];
	$jpush->send($audience, null, $message);
}

function checkAdminLogin()
{
    if(isset($_SESSION['purview']) && isset($_SESSION['account']))
    {
        return true;
    } else {
        showSystemMessage('您尚未登录', array(array('alt'=>'管理后台登录', 'link'=>'../../manager/index.php')));
        exit;
    }
}

function wechat_back_base_init() {
    checkAdminLogin();
}

/**
 * 绑定服务号时同步已有用户分组
 */
function sync_user_group() {
    global $db, $lang, $errors;
    $getInfo = 'select `appID`,`appsecret`,`expireTime`,`accessToken` from `'.$db_prefix.'publicAccount` where `account`=\''.$_SESSION['public_account'].'\'';
    $info = $db->fetchRow($getInfo);

    if($info)
    {
        $accessToken = '';
        if($info['expireTime'] >= time())
        {
            $accessToken = $info['accessToken'];
        } else {
            $accessToken = getAccessToken($info['appID'], $info['appsecret']);
        }

        if($accessToken)
        {
            $updateAccessToken = 'update `'.$db_prefix.'publicAccount` set `accessToken`=\''.$accessToken.'\',`expireTime`='.(time()+7200).' where `account`=\''.$_SESSION['public_account'].'\'';
            $db->update($updateAccessToken);
            //2.发送分组列表请求
            $url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$accessToken;
            $curl = curl_init();
            $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this_header);
            curl_setopt($curl, CURLOPT_URL, $url);
//                curl_setopt($curl, CURLOPT_HEADER, 0);
//            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($data));
            if( defined('CURL_SSLVERSION_TLSv1') ) {
                curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            $data = curl_exec($curl);
            curl_close($curl);

            $data = json_decode($data);
            //3.判断状态码
            if( empty($data->errcode) )
            {
                $groups = array();
                foreach( $data->groups as $group ) {
                    $temp = array();
                    $temp['wechatId'] = $group->id;
                    $temp['name'] = $group->name;
                    $temp['count'] = $group->count;
                    $exists = 'select id from '.$db_prefix.'group where wechatId=\''.$group->id.'\' limit 1';
                    $temp_id = $db->fetchOne($exists);
                    if( $temp_id ) {
                        $update = 'update '.$db_prefix.'group set name = \''.$group->name.'\', count = '.$group->count.' where wechatId=\''.$group->id.'\' limit 1;';
                        $db->update($update);
                    } else {
                        $insert = 'insert into '.$db_prefix.'group (id, wechatId, name, count, addTime, publicAccount) values ';
                        $insert .= ' (null, \''.$group->id.'\', \''.$group->name.'\', \''.$group->count.'\', '.time().', \''.$_SESSION['public_account'].'\');';
                        $db->insert($insert);
                    }
                    $groups[] = $temp;
                }
                assign('groups', $groups);
            } else {
                $response['msg'] = $errors[$data->errcode];
            }
        } else {
            $response['msg'] = $lang['warning']['get_access_token_fail'];
        }
    } else {
        $response['msg'] = $lang['warning']['param_error'];
    }
    !empty($response['msg']) ? showSystemMessage($response['msg']) : true;
}