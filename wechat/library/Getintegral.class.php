<?php
/**
 * 获取用户积分
 */
class Getintegral extends WechatResponse
{
    protected $msg;

    public function __construct($fromUserName, $toUserName, $content)
    {
        parent::__construct($fromUserName, $toUserName);
    }

    public function run()
    {
        global $db_prefix;
        global $db;

        $getUserId = 'select `card` from `'.$db_prefix.'user` where `openId`=\''.$this->toUserName.'\'';
        $card = $db->fetchOne($getUserId);

        if($card)
        {
            include 'Rsa.class.php';
            $rsa = new Rsa();

            $param = array('opera'=>'get_integral', 'account'=>"".$this->fromUserName, 'card'=>$card);
            $param = json_encode($param);
            
            $param = $rsa->private_key_encrypt($param, 'base64', 'api/key/rsa_private_key.pem');
            $response = post('http://www.jkdzsw.com/mobile/api/user.php', array('data'=>$param));

            $response = $rsa->public_key_decrypt($response, 'base64', 'api/key/outer_public_key.pem');

            if($response)
            {
                $response = json_decode($response);

                if($response->msg)
                {
                    $this->msg = $response->msg;
                } else {
                    $this->msg = '没有找到任何信息';
                }
            }
        } else {
            $this->msg = '您尚未绑定亲友卡，请先进行<a href="http://www.jkdzsw.com/mobile/bind.php?o='.$this->toUserName.'">绑定</a>';
        } 
    }


    public function __toString()
    {
        $responseObj = new TextResponse($this->fromUserName, $this->toUserName, $this->msg);

        return $responseObj->__toString();
    }
}
