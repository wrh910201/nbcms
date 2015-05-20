<?php
/**
 * 用户日常操作封装
 * @author winsen
 * @version 1.0.0
 */
class User
{
    private $db;
    private $db_prefix;
    private $db_name = 'adminUser';

    /**
     * 构造函数
     * @param MySQL $db 数据库对象
     * @param string $db_prefix 数据表前缀
     * @return void
     * @author winsen
     */
    public function __construct($db, $db_prefix='wx_')
    {
        $this->db = $db;
        $this->db_prefix = $db_prefix;
    }

    /**
     * 判断用户账号是否存在
     * @param string $account 待检查的用户账号
     * @return bool 不存在返回false,否者返回true
     * @author winsen
     */
    public function checkAccountExists($account)
    {
        $account = $this->db->escape($account);
        $checkAccount = 'select `id` from `'.$this->db_prefix.'adminUser` where `account`=\''.$account.'\' and `enabled`=1';
        $record = $this->db->fetchRow($checkAccount);

        if(null != $record)
        {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断用户密码是否正确
     * @param string $password 用户输入的密码
     * @param string $account 用户账号
     * @return bool 正确返回true,否则返回false
     * @author winsen
     */
    public function checkPassword($password, $account)
    {
        $account = $this->db->escape($account);
        $password = $this->db->escape($password);
        $checkPassword = 'select `password` from `'.$this->db_prefix.'adminUser` where `account`=\''.$account.'\' and `enabled`=1';
        $checkPassword .= ' limit 1';
        $record = $this->db->fetchRow($checkPassword);
        if(null != $record)
        {
            if($record['password'] == $password)
            {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 用户登录SESSION设置
     * @param string $account 用户账号
     * @return bool 成功返回true,否则返回false
     * @author winsen
     */
    public function setLogin($account)
    {
        $account = $this->db->escape($account);
        $getUserInfo = 'select `name`,`phone`,`email`,`purview`,`sex` from `'.$this->db_prefix.'adminUser`';
        $getUserInfo .= ' where `account`=\''.$account.'\' and `enabled`=1';
        $record = $this->db->fetchRow($getUserInfo);
        if(null != $record)
        {
            $_SESSION['nbweixin']['name'] = $record['name'];
            $_SESSION['nbweixin']['phone'] = $record['phone'];
            $_SESSION['nbweixin']['purview'] = $record['purview'];
            $_SESSION['nbweixin']['sex'] = $record['sex'];

            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据账号名获取用户信息
     * @param string $account 用户账号
     * @return mixed 用户存在时返回对应的记录,否则返回null
     * @author winsen
     */
    public function getUserInfoByAccount($account)
    {
        $account = $this->db->escape($account);
        $getUserInfo = 'select `name`,`phone`,`purview`,`sex`,`email`,`enabled` from `'.$this->db_prefix.'adminUser`';
        $getUserInfo .= ' where `account`=\''.$account.'\' limit 1';
        $userInfo = $this->db->fetchRow($getUserInfo);
        
        return $userInfo;
    }
}
