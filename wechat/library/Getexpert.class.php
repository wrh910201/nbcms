<?php
/**
 * 建立人工应答模式
 */
class Getexpert extends WechatResponse
{
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
        //检查如果不是亲友不能使用该功能
        $checkCard = 'select `card` from `'.$db_prefix.'user` where `openId`=\''.$this->toUserName.'\'';
        if($db->fetchOne($checkCard))
        {
            $updateUser = 'update `'.$db_prefix.'user` set `mode`=\'artificial\',`modeExpired`='.(time()+21600).' where `openId`=\''.$this->toUserName.'\'';
            $db->update($updateUser);
            $this->msg = '专家连线成功，您已进入“专家咨询”对话模式。接下来将由“龙日荣教授团队”与您直接与您对话。目前本模式的只支持文本信息的发送，请见谅。结束对话后请输入“00”退出专家咨询模式';
        } else {
            $this->msg = '您尚未绑定亲友卡，请先进行<a href="http://www.jkdzsw.com/mobile/bind.php?o='.$this->toUserName.'">绑定</a>';
        }
    }

    public function exitMode()
    {
        $this->msg = '您已退出专家咨询模式，点左下角小图标切换到菜单页面';
    }


    public function __toString()
    {
        $responseObj = null;
        $responseObj = new TextResponse($this->fromUserName, $this->toUserName, $this->msg);

        return $responseObj->__toString();
    }
}
