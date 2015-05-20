<?php
/**
 *
 */
class Qrcode extends WechatResponse
{
    protected $imgUrl;
    protected $url;
    protected $msg;

    public function __construct($fromUserName, $toUserName, $content)
    {
        parent::__construct($fromUserName, $toUserName);
    }

    public function run()
    {
        global $db_prefix;
        global $db;

        $checkCard = 'select `card` from `'.$db_prefix.'user` where `openId`=\''.$this->toUserName.'\'';
        if(!$db->fetchOne($checkCard))
        {
            $this->msg = '您尚未绑定亲友卡，请先进行<a href="http://www.jkdzsw.com/mobile/bind.php?o='.$this->toUserName.'">绑定</a>';
            return;
        }

        $getInfo = 'select `appID`,`appsecret`,`expireTime`,`accessToken` from `'.$db_prefix.'publicAccount` where `account`=\''.$this->fromUserName.'\'';
        $info = $db->fetchRow($getInfo);

        $accessToken = '';
        if($info['expireTime'] >= time())
        {
            $accessToken = $info['accessToken'];
        } else {
            $accessToken = getAccessToken($info['appID'], $info['appsecret']);
        }

        $getUserId = 'select `id`,`sceneId` from `'.$db_prefix.'user` where `openId`=\''.$this->toUserName.'\'';
        $user = $db->fetchRow($getUserId);

        $scene_id = 0;
        if($user['id'] < 100000 && $user['id'] > 0)
        {
            $scene_id = $user['id'];
        } else {
            //短时间内超过100000个估计概率不大，等再优化策略
            //回收超时的scene_id
            $getExpiredRule = 'select ｀id` from `'.$db_prefix.'rules` where `expired`>0 and `expired`<'.time().' and `rule` like \'scan_%\'';
            $ruleId = $db->getOne($getExpiredRule);
        }
        //临时二维码申请
        $data = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        $response = rawPost('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$accessToken, $data);

        $response = json_decode($response);

        if(isset($response->errcode))
        {
            $this->imgUrl = '';
            $this->msg = '获取二维码失败';
        } else {
            $ticket = $response->ticket;
            $this->url = $response->url;

            $this->imgUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        }

        /*
        if($this->imgUrl)
        {
            //检查是否存在响应事件
            $checkRule = 'select `id` from `'.$db_prefix.'rules` where `rule`=\'scan_'.$scene_id.'\' limit 1';
            $ruleId = $db->fetchOne($checkRule);

            if($ruleId)
            {
                $updateExpired = 'update `'.$db_prefix.'rules` set `expired`='.(time()+1800).' where `id`='.$ruleId;
                $db->update($updateExpired);
            } else {
                $addRule = 'insert into `'.$db_prefix.'rules` (`publicAccount`,`rule`,`responseId`,`name`,`expired`) values (\'%s\','.
                           '\'%s\',%d,\'%s\',%d)';
                $addRule = sprintf($addRule, $this->fromUserName, 'scan_'.$scene_id, 2, '扫码关注事件', (time()+1800));
                $db->insert($addRule);
            }
        }
         */
    }


    public function __toString()
    {
        if($this->imgUrl)
        {
            $item = array(array('title'=>'扫描关注"碱康e家"', 'description'=>'请在半小时内让您的朋友扫描关注"碱康e家"微信号，超时需要重新获取推广二维码，需要获取永久推广二维码，请与客服联系。', 'picUrl'=>$this->imgUrl, 'url'=>$this->imgUrl));

            $responseObj = new NewsResponse($this->fromUserName, $this->toUserName, $item, false);
        } else {
            $responseObj = new TextResponse($this->fromUserName, $this->toUserName, $this->msg);
        }

        return $responseObj->__toString();
    }
}
