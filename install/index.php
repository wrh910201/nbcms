<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <title>CMS安装程序</title>
</head>
<body>
<?php
if(file_exists('../data/config.lock'))
{
    echo 'To be reinstalled, plz delete the file "data/config.lock" first!';
    exit;
}
?>
<form method="POST" name="install">
数据库前缀:<input type="text" name="db_prefix" value="cms_"/><br/>
数据库服务器:<input type="text" name="db_host" value="localhost"/><br/>
数据库端口:<input type="text" name="db_port" value="3306"/><br/>
数据库用户名:<input type="text" name="db_username" value="root"/><br/>
数据库密码:<input type="password" name="db_password"/><br/>
数据库名:<input type="text" name="db_name" value="nbcms"/><br/>
<hr/>
管理员账号:<input type="text" name="account" value="admin"/><br/>
登录密码:<input type="password" name="password"/><br/>
确认密码:<input type="password" name="confirmPass"/><br/>
E-mail:<input type="text" name="email" value="admin@localhost"/><br/>
手机号码:<input type="text" name="mobile"/><br/>
<input type="submit" name="submit" value="开始安装"/>
</form>
</body>
</html>
