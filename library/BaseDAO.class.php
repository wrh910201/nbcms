<?php
/**
 * 数据库表操作封装的父类
 * @author winsen
 * @version 1.0.0
 */
abstract class BaseDAO
{
    protected $_db;//resource MySQL的数据库链接
    protected $_name;//string 当前操作对应的表名

    /**
     * 构造函数
     * @param resource $db MySQL的数据库链接
     * @param string $name 数据库的表名
     * @return void
     * @author winsen
     */
    public function __construct($db, $name)
    {
        $this->_db = $db;
        $this->_name = $name;
    }
}
