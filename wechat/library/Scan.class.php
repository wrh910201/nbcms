<?php
/**
 *
 */
class Scan extends WechatResponse
{
    protected $url;
    protected $event_key;

    public function __construct($fromUserName, $toUserName, $event_key)
    {
        parent::__construct($fromUserName, $toUserName);
        $this->event_key = $event_key;
    }

    public function run()
    {
        global $db_prefix;
        global $db;

        $publicAccount = $this->fromUserName;
        $openId = $this->toUserName;

        $checkParent = 'select `id`,`openId`,`path` from `'.$db_prefix.'user` where `id`='.$this->event_key.' limit 1';
        $parent = $db->fetchRow($checkParent);

        $parentId = '';
        $path = '';

        if($parent)
        {
            $parentId = $parent['openId'];
            $path = $parent['path'].',';
        }

        //如果是已关注的用户不作任何修改
        $checkUser = 'select `id` from `'.$db_prefix.'user` where `openId`=\''.$openId.'\'';
        $user = $db->fetchRow($checkUser);

        if(!$user)
        {
            $addUser = 'insert into `'.$db_prefix.'user` (`id`,`openId`,`addTime`,`unsubscribed`,`leaveTime`,`integral`,`publicAccount`,`parentId`,`path`) values (null, \''.$this->toUserName.'\', '.time().', 1, 0, 0,\''.$this->fromUserName.'\',\''.$parentId.'\',\''.$path.'\')';
            if($db->insert($addUser))
            {
                $id = $db->getLastId();

                $updatePath = 'update `'.$db_prefix.'user` set `path`=\''.$path.$id.'\' where `id`='.$id;
                $db->update($updatePath);
            }
        }
    }


    public function __toString()
    {
        return '';
    }
}
