<?php
/*
 *  日志记录
 *  @author winsen
 *  @version 1.0.0
 */
class Logs extends MySQL
{
    public function cleanUp()
    {
        
    }

    public function recordArticleLogs($userId, $opera, $articleId)
    {
        global $db_prefix;

        $sql = 'insert into `'.$db_prefix.'articleLogs` (`userId`,`opera`,`addTime`,`articleId`,`referer`,`agent`) '.
               'values(%d, \'%s\', %d, %d, \'%s\',\'%s\');';
        $sql = sprintf($sql, $userId, $opera, time(), $articleId, $_SERVER['HTTP_REFERER'], $_SERVER['USER_AGENT']);
        return $this->insert($sql);
    }

    public function recordLogs($userId, $url)
    {
        global $db_prefix;

        $sql = 'insert into `'.$db_prefix.'logs` (`userId`,`session`,`url`,`enterTime`,`referer`,`agent`) values ('.
               '%d,\'%s\',\'%s\',%d,\'%s\',\'%s\');';
        $sql = sprintf($sql, $userId, session_id(), $url, time(), $_SERVER['HTTP_REFERER'], $_SERVER['USER_AGENT']);

        return $this->insert($sql);
    }

    public function completeLogs($userId, $url)
    {
        global $db_prefix;

        $sql = 'select `id` from `'.$db_prefix.'logs` where `userId`=%d and `url`=\'%s\' and `session`=\'%s\' and `leaveTime`=0';

        $id = $this->fetchOne($sql);

        if($id)
        {
            $sql = 'update `'.$db_prefix.'logs` set `leaveTime`='.time().' where `id`='.$id;

            return $this->update($sql);
        } else {
            return false;
        }
    }
}
