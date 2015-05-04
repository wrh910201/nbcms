<?php
/**
 * 公共函数库
 * @author winsen
 * @version 1.0.0
 */

/**
 * 权限检查函数
 * @param int $sys_purview 系统定义的权限
 * @param int $user_purview 用户的权限
 * @return bool 拥有该权限时返回true,否则返回false
 * @author winsen
 */
function checkPurview($sys_purview, $user_purview)
{
    return $sys_purview & $user_purview;
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
 * 获取REQUEST的参数封装
 * @param string $var 参数名
 * @return mixed 返回对应的参数,如果参数不存在,则返回null
 * @author winsen
 */
function getREQUEST($var)
{
    if(isset($_REQUEST[$var]))
    {
        return $_REQUEST[$var];
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
 * 数据过滤函数
 * @param mixed $var 需要过滤的变量
 * @param string $type 过滤的数据类型
 * @return mixed 过滤后的数据
 * @author winsen
 */
function filterVar($var, $type)
{
    switch($type)
    {
    //字符串,过滤空串
    case 'string':
        if(!empty($var) && is_string($var))
        {
            return $var;
        } else {
            return null;
        }
        break;
    //整型数,过滤非整型数
    case 'integer':
        if(isset($var) && is_numeric($var))
        {
            return intval($var);
        } else {
            return null;
        }
        break;
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
function showSystemMessage($msg, $links, $time = 3)
{
    global $smarty;
    assign('message', nl2br(htmlspecialchars($msg)));
    if(count($links) > 0)
    {
        assign('links', $links);
        assign('link', $links[0]['link']);
    } else {
        $pre = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        assign('links', array(array('alt'=>'返回上一页', 'link'=> $pre)));
        assign('link', $pre);
    }
    assign('time', $time);
    assign('pageTitle', '系统信息');
    $smarty->display('message.phtml');
    exit;
}


/**
 * 文件上传
 */
function upload($file, $type = '')
{
    $ext_arr = array(
    	'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
	    'flash' => array('swf', 'flv'),
    	'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
	    'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
    );

    $php_path = dirname(__FILE__) . '/';
    $php_url = dirname($_SERVER['PHP_SELF']) . '/';
    //文件保存目录路径
    $save_path = '../upload/';
    //文件保存目录URL
    $save_url = '../upload/';


    //PHP上传失败
    if (!empty($file['error'])) 
    {
        switch($file['error'])
        {
		case '1':
			$error = '超过php.ini允许的大小。';
			break;
		case '2':
			$error = '超过表单允许的大小。';
			break;
		case '3':
			$error = '图片只有部分被上传。';
			break;
		case '4':
			$error = '请选择图片。';
			break;
		case '6':
			$error = '找不到临时目录。';
			break;
		case '7':
			$error = '写文件到硬盘出错。';
			break;
		case '8':
			$error = 'File upload stopped by extension。';
			break;
		case '999':
		default:
			$error = '未知错误。';
    	}

        return array('error'=>1, 'msg'=>$error);
    }

    $save_path = realpath($save_path).'/';

    //有上传文件时
    if (empty($file) === false) 
    {
	    //原文件名
    	$file_name = $file['name'];
	    //服务器上临时文件名
    	$tmp_name = $file['tmp_name'];
	    //文件大小
    	$file_size = $file['size'];
	    //检查文件名
    	if (!$file_name) {
	    	return array('error'=>1,'msg'=>"请选择文件。");
    	}
	    //检查目录
    	if (@is_dir($save_path) === false) {
	    	return array('error'=>1,'msg'=>"上传目录不存在。");
    	}
	    //检查目录写权限
    	if (@is_writable($save_path) === false) {
	    	return array('error'=>1,'msg'=>"上传目录没有写权限。");
    	}
	    //检查是否已上传
    	if (@is_uploaded_file($tmp_name) === false) {
	    	return array('error'=>1,'msg'=>"上传失败。");
    	}
	    //检查文件大小
        if ($file_size > 100000000)
        {
		    return array('error'=>1,'msg'=>"上传文件大小超过限制。");
    	}
	    //检查目录名
    	$dir_name = empty($type) ? 'image' : trim($type);
        if (empty($ext_arr[$dir_name])) 
        {
		    return array('error'=>1,'msg'=>"目录名不正确。");
    	}
	    //获得文件扩展名
    	$temp_arr = explode(".", $file_name);
	    $file_ext = array_pop($temp_arr);
    	$file_ext = trim($file_ext);
	    $file_ext = strtolower($file_ext);
    	//检查扩展名
        if (in_array($file_ext, $ext_arr[$dir_name]) === false)
        {
		    return array('error'=>1,'msg'=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
    	}
	    //创建文件夹
        if ($dir_name !== '') 
        {
		    $save_path .= $dir_name . "/";
    		$save_url .= $dir_name . "/";
	    	if (!file_exists($save_path)) 
            {
			    mkdir($save_path);
    		}
	    }
    	$ymd = date("Ymd");
	    $save_path .= $ymd . "/";
    	$save_url .= $ymd . "/";
	    if (!file_exists($save_path)) 
        {
		    mkdir($save_path);
    	}
	    //新文件名
    	$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
	    //移动文件
    	$file_path = $save_path . $new_file_name;
        if (move_uploaded_file($tmp_name, $file_path) === false) 
        {
		    return array('error'=>1, 'msg'=>"上传文件失败。");
    	}
	    @chmod($file_path, 0644);
    	$file_url = $save_url . $new_file_name;
        
        return array('error' => 0, 'msg' => str_replace('..', '', $file_url));
    }

    return array('error'=>1, 'msg'=>'没有文件上传');
}

function checkAdminLogin()
{
    if(isset($_SESSION['purview']) && isset($_SESSION['account']))
    {
        return true;
    } else {
        showSystemMessage('您尚未登录', array(array('alt'=>'管理后台登录', 'link'=>'index.php')));
        exit;
    }
}

function createMenus()
{
    global $menus;
    $temp = 0xf;
    $index = 0;
    $menu = array();

    while($temp <= $_SESSION['purview'])
    {
        if(checkPurview($temp, $_SESSION['purview']))
        {
            $menu[] = $menus[$index];
        }
        $index++;
        $temp = $temp<<4;
    }

    assign('menus', $menu);
}

/**
 * 生成伪静态化的URL或者原始URL
 */
function buildUrl($script, $params)
{
	global $sysconf;

	switch($script)
	{
		case 'article':
			if($sysconf['isStatic'])
			{
				return 'article-'.intval($params['id']).'.html';
			} else {
				return 'article.php?id='.intval($params['id']);
			}
			break;
		case 'product':
			if($sysconf['isStatic'])
			{
				return 'product-'.intval($params['id']).'.html';
			} else {
				return 'product.php?id='.intval($params['id']);
			}
			break;
		case 'articleCat':
			if($sysconf['isStatic'])
			{
				return 'articleCat-'.intval($params['id']).'.html';
			} else {
				return 'articleCat.php?id='.intval($params['id']);
			}
			break;
	}
}

function smarty_modifier_build_url($script, $params)
{
    global $conf;
    switch($script)
    {
    case 'article':
        if($conf['isStatic'])
        {
            return 'article-'.$params.'.html';
        } else {
            return 'article.php?id='.$params;
        }
        break;
    case 'ads':
        if($conf['isStatic'])
        {
            return 'ads-'.$params.'.html';
        } else {
            return 'ads.php?id='.$params;
        }
        break;
	case 'product':
		if($conf['isStatic'])
		{
			return 'product-'.$params.'.html';
		} else {
			return 'product.php?id='.$params;
		}
		break;
	case 'articleCat':
		if($conf['isStatic'])
		{
			return 'articleCat-'.intval($params['id']).'.html';
		} else {
			return 'articleCat.php?id='.intval($params['id']);
		}
		break;
    }
}

function buildUrHere($script, $params)
{
	global $db;
	$urHere = array(array('link'=>'/','title'=>'首页'));
	switch($script)
	{
		case 'article':
			$getArticle = 'select `title`,`articleCatId` from `'.DB_PREFIX.'article` where `id`='.$params['id'];
			$article = $db->fetchRow($getArticle);
			$getArticleCat = 'select `name`,`id` from `'.DB_PREFIX.'articleCat` where `id`='.$article['articleCatId'];
			$articleCat = $db->fetchRow($getArticleCat);
			$urHere[] = array('link'=>buildUrl('articleCat', array('id'=>$articleCat['id'])), 'title'=>$articleCat['name']);
			$urHere[] = array('link'=>'', 'title'=>$article['title']);
			break;
		case 'product':
			break;
		case 'category':
			break;
		case 'articleCat':
			$getArticleCat = 'select `path` from `'.DB_PREFIX.'articleCat` where `id`='.$params['id'];
			$path = $db->fetchRow($getArticleCat);
			$path = $path['path'];
			$path = explode(',', $path);
			foreach($path as $cid)
			{
				$getArticleCat = 'select `id`,`name` from `'.DB_PREFIX.'articleCat` where `id`='.$cid;
				$cat = $db->fetchRow($getArticleCat);
				if($params['id'] != $cid)
				{
					$urHere[] = array('link'=>buildUrl('articleCat', $cat), 'title'=>$cat['name']);
				} else {
					$urHere[] = array('link'=>'', 'title'=>$cat['name']);
				}
			}
			break;
	}
	return $urHere;
}