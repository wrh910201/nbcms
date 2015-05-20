<?php
/**
 * 支持各个分离系统的会员信息对接
 * @author winsen
 * @date 2014-11-20
 */
include '../library/init.inc.php';
$loader->includeClass('Rsa');

//接收数据统一使用json格式的RSA私钥加密的密文的base64编码字符串
//返回也是相同的格式
$data = isset($_POST['data']) ? $_POST['data'] : '';
$data = str_replace(' ', '+', $data);
$response = array();
$rsa = new Rsa();
if($data)
{
    $data = $rsa->public_key_decrypt($data, 'base64', 'key/outer_public_key.pem');
    
    if($data)
    {
        $data = json_decode($data);
    }

    if($data->opera == 'get')
    {
        $code = $data->code;//OAuth用于获取accessToken的code
        $publicAccount = $db->escape($data->account);//需要对接程序提供公众号信息

        $getInfo = 'select `appID`,`appsecret` from `'.$db_prefix.'publicAccount` where `account`=\''.$publicAccount.'\'';
        $info = $db->fetchRow($getInfo);
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
        $url = sprintf($url, $info['appID'], $info['appsecret'], $code);

        $wechatInfo = get($url);
        $wechatInfo = json_decode($wechatInfo);

        if(isset($wechatInfo->errcode))
        {
            $response = $errors[$wechatInfo->errcode];
        } else {
            $openId = $db->escape($wechatInfo->openid);

            $getUserInfo = 'select `card` from `'.$db_prefix.'user` where `openId`=\''.$openId.'\'';
            $response['card'] = $db->fetchOne($getUserInfo);
            $response['openId'] = $wechatInfo->openid;
        }
    }

    if($data->opera == 'get_detail')
    {
        $code = $data->code;//OAuth用于获取accessToken的code
        $publicAccount = $db->escape($data->account);//需要对接程序提供公众号信息

        $getInfo = 'select `appID`,`appsecret` from `'.$db_prefix.'publicAccount` where `account`=\''.$publicAccount.'\'';
        $info = $db->fetchRow($getInfo);
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
        $url = sprintf($url, $info['appID'], $info['appsecret'], $code);

        $wechatInfo = get($url);
        $wechatInfo = json_decode($wechatInfo);

        if(isset($wechatInfo->errcode))
        {
            $response = $errors[$wechatInfo->errcode];
        } else {
            $openId = $db->escape($wechatInfo->openid);

            $getUserInfo = 'select `card` from `'.$db_prefix.'user` where `openId`=\''.$openId.'\'';
            $response['card'] = $db->fetchOne($getUserInfo);
            $response['openId'] = $wechatInfo->openid;

            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN';
            $url = sprintf($url, $wechatInfo->access_token, $wechatInfo->openid);
            $user_info = get($url);
            $user_info = json_decode($user_info);
            if(isset($user_info->errcode))
            {
                $response = $errors[$user_info->errcode];
            } else {
                $response['photo'] = $user_info->headimgurl;
            }
        }
    }

    if($data->opera == 'update')
    {
        $card = $db->escape($data->card);
        $name = $db->escape($data->name);
        $openId = $db->escape($data->openId);
        $mobile = $db->escape($data->mobile);
        $password = $db->escape($data->password);

        $checkOpenId = 'select `openId` from `'.$db_prefix.'user` where `openId`=\''.$openId.'\'';
        $_openId = $db->fetchOne($checkOpenId);

        if(!$_openId)
        {
            $addUser = 'insert into `'.$db_prefix.'user` (`openId`,`name`,`mobile`,`password`,`card`) values (\'%s\',\'%s\',\'%s\',\'%s\',\'%s\')';
            $addUser = sprintf($addUser, $openId, $name, $mobile, $password, $card);

            if($db->insert($addUser))
            {
                $response['success'] = true;
            } else {
                $response['success'] = false;
            }
        } else {
            $updateUser = 'update `'.$db_prefix.'user` set `name`=\'%s\',`card`=\'%s\',`mobile`=\'%s\',`password`=\'%s\' where `openId`=\'%s\' limit 1';
            $updateUser = sprintf($updateUser, $name, $card, $mobile, $password, $openId);

            if($db->update($updateUser))
            {
                $response['success'] = true;
            } else {
                $response['success'] = false;
            }
        }
    }

    if($data->opera == 'unbind')
    {
        $card = $db->escape($data->card);
        $openId = $db->escape($data->openId);

        $checkUser = 'select `openId` from `'.$db_prefix.'user` where `openId`=\''.$openId.'\' and `card`=\''.$card.'\'';
        $openId = $db->fetchOne($checkUser);

        if($openId)
        {
            $unbind = 'update `'.$db_prefix.'user` set `card`=\'\' where `openId`=\''.$openId.'\'';
            if($db->update($unbind))
            {
                $response['success'] = true;
            } else {
                $response['success'] = false;
            }
        }
    }

    if($data->opera == 'get_parent')
    {
        $openId = $db->escape($data->openId);

        $getParent = 'select `parentId` from `'.$db_prefix.'user` where `openId`=\''.$openId.'\'';
        $parentId = $db->fetchOne($getParent);

        if($parentId)
        {
            $getCard = 'select `card` from `'.$db_prefix.'user` where `openId`=\''.$parentId.'\'';
            $response['card'] = $db->fetchOne($getCard);
        } else {
            $response['card'] = '';
        }
    }

} else {
    $response['card'] = '';
    $response['error'] = 'no data';
}
$response = json_encode($response);
$response = $rsa->private_key_encrypt($response, 'base64', 'key/rsa_private_key.pem');
echo $response;
