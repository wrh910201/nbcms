<?php
include 'library/init.inc.php';

back_base_init();

$operation = 'add|edit';
$action = 'add|list|delete|edit|cycle|remove';

$act = checkAction($action, getGET('act'));
$opera = checkAction($operation, getPOST('opera'));

if('' == $act)
{
    $act = 'list';
}

//新增产品
if('add' == $opera)
{
    if(!checkPurview('pur_product_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $price = getPOST('price');
    $marketPrice = getPOST('marketPrice');
    $categoryId = getPOST('categoryId');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $detail = getPOST('detail');
    $alts = getPOST('alt');

    $addTime = time();

    if('' == $name)
    {
        showSystemMessage('产品名称不能为空', array());
        exit;
    } else {
        $title = $db->escape(htmlspecialchars($name));
    }

    if('' == $price)
    {
        $price = 0;
    } else {
        $price = $db->escape(floatval($price));
    }

    if('' == $marketPrice)
    {
        $marketPrice = 0;
    } else {
        $marketPrice = $db->escape(floatval($marketPrice));
    }

    if('' == $keywords)
    {
        showSystemMessage('出于SEO的考虑，请务必填写关键词', array());
        exit;
    } else {
        $keywords = $db->escape(htmlspecialchars($keywords));
    }

    if('' == $description)
    {
        showSystemMessage('出于SEO的考虑，请务必填写资讯简介', array());
        exit;
    } else {
        $description = $db->escape(htmlspecialchars($description));
    }

    if('' == $categoryId || 0 >= $categoryId)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $categoryId = intval($categoryId);
    }

    $detail = $db->escape($detail);

    $addproduct  = 'insert into `'.DB_PREFIX.'product` (`price`,`marketPrice`,`name`,`categoryId`,`keywords`,`description`,';
    $addproduct .= '`detail`,`addTime`) values ('.$price.','.$marketPrice.',';
    $addproduct .= '\''.$name.'\','.$categoryId.',\''.$keywords.'\',\''.$description.'\',\''.$detail.'\',';
    $addproduct .= $addTime.')';

    if($db->insert($addproduct))
    {
    	$id = $db->getLastId();
    	$gallery = $_FILES['gallery'];
    	$successUpload = 0;
    	foreach($gallery['name'] as $key=>$v)
    	{
    		$file = array(
    			'tmp_name' => $gallery['tmp_name'][$key],
    			'name'	   => $gallery['name'][$key],
    			'size'	   => $gallery['size'][$key],
    			'error'	   => $gallery['error'][$key],
    			'type'	   => $gallery['type'][$key]
    		);
    		$imgDest = upload($file);
    		$alt = !empty($alts[$key]) ? $name : $db->escape(htmlspecialchars($alts[$key]));
    		$isDefault = 0;
    		if($key == 0)
    		{
    			$isDefault = 1;
    		}
    		
    		if($imgDest['error'] == 0)
    		{
    			$imgDest = $imgDest['msg'];
    			$successUpload++;
    			$addGallery  = 'insert into `'.DB_PREFIX.'gallery` (`productId`,`original`,`thumb`,`normal`,`alt`,`isDefault`)';
    			$addGallery .= ' values('.$id.',\''.$imgDest.'\',\''.$imgDest.'\',\''.$imgDest.'\',\''.$alt.'\','.$isDefault.')';
    			$db->insert($addGallery);
    		}
    	}
        showSystemMessage('新增产品成功,成功上传图片'.$successUpload.'张', array(), 10);
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array(), 10);
        exit;
    }
}

//编辑资讯
if('edit' == $opera)
{
    if(!checkPurview('pur_product_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $name = getPOST('name');
    $price = getPOST('price');
    $marketPrice = getPOST('marketPrice');
    $categoryId = getPOST('categoryId');
    $keywords = getPOST('keywords');
    $description = getPOST('description');
    $detail = getPOST('detail');

    $addTime = time();

    if('' == $name)
    {
        showSystemMessage('产品名称不能为空', array());
        exit;
    } else {
        $title = $db->escape(htmlspecialchars($title));
    }

    if('' == $price)
    {
        $price = 0;
    } else {
        $price = $db->escape($price);
    }

    if('' == $marketPrice)
    {
        $marketPrice = 0;
    } else {
        $marketPrice = $db->escape($marketPrice);
    }

    if('' == $keywords)
    {
        showSystemMessage('出于SEO的考虑，请务必填写关键词', array());
        exit;
    } else {
        $keywords = $db->escape(htmlspecialchars($keywords));
    }

    if('' == $description)
    {
        showSystemMessage('出于SEO的考虑，请务必填写资讯简介', array());
        exit;
    } else {
        $description = $db->escape(htmlspecialchars($description));
    }

    if('' == $categoryId || 0 >= $categoryId)
    {
        showSystemMessage('参数错误', array());
        exit;
    } else {
        $categoryId = intval($categoryId);
    }

    $detail = $db->escape($detail);

    $updateproduct  = 'update `'.DB_PREFIX.'product` set `name`=\''.$name.'\', `price`=\''.$price.'\',';
    $updateproduct .= '`keywords`=\''.$keywords.'\', `description`=\''.$description.'\', `marketPrice`='.$marketPrice;
    $updateproduct .= '`categoryId`='.$categoryId.', `detail`=\''.$detail.'\'';
    $updateproduct .= ' where `id`='.$id.' limit 1';

    if($db->update($updateproduct))
    {
        $links = array(
            array('alt'=>'查看产品列表', 'link'=>'product.php?act=list')
        );
        showSystemMessage('更新产品成功', $links);
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

if('list' == $act)
{
    if(!checkPurview('pur_product_list', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getproducts  = 'select a.`id`,a.`name`,ac.`name` as category from ';
    $getproducts .= '`'.DB_PREFIX.'product` as a left join ';
    $getproducts .= '`'.DB_PREFIX.'category` as ac on a.`categoryId`=ac.`id`';

    assign('products', $db->fetchAll($getproducts));
}

if('add' == $act)
{
    if(!checkPurview('pur_product_add', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $getcategory = 'select `name`,`id`,`path` from `'.DB_PREFIX.'category` order by `path`';
    $category = $db->fetchAll($getcategory);

    foreach($category as $key=>$cat)
    {
        $count = count(explode(',', $cat['path']));

        if($count > 1)
        {
            $temp = '|--';
            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $cat['name'] = $temp.$cat['name'];
        }

        $category[$key] = $cat;
    }

    assign('category', $category);
}

if('edit' == $act)
{
    if(!checkPurview('pur_product_edit', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');
    if('' == $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $id = intval($id);

    $getproduct = 'select `id`,`price`,`marketPrice`,`name`,`categoryId`,`keywords`,`description`,`detail` from `'.DB_PREFIX.'product` where `id`='.$id.' limit 1';
    $product = $db->fetchRow($getproduct);

    if($product)
    {
        assign('product', $product);
    } else {
        showSystemMessage('参数错误', array());
        exit;
    }
    $getcategory = 'select `name`,`id`,`path` from `'.DB_PREFIX.'category` order by `path`';
    $category = $db->fetchAll($getcategory);

    foreach($category as $key=>$cat)
    {
        $count = count(explode(',', $cat['path']));

        if($count > 1)
        {
            $temp = '|--';
            while($count--)
            {
                $temp = '&nbsp;&nbsp;'.$temp;
            }

            $cat['name'] = $temp.$cat['name'];
        }

        $category[$key] = $cat;
    }

    assign('category', $category);
}

if('delete' == $act)
{
    if(!checkPurview('pur_product_delete', $_SESSION['purview']))
    {
        showSystemMessage('权限不足', array());
        exit;
    }

    $id = getGET('id');

    if('' == $id)
    {
        showSystemMessage('参数错误', array());
        exit;
    }

    $id = intval($id);
    $deleteproduct = 'delete from `'.DB_PREFIX.'product` where `id`='.$id.' limit 1';

    if($db->update($deleteproduct))
    {
        showSystemMessage('删除产品成功', array(array('alt'=>'查看产品列表', 'link'=>'product.php')));
        exit;
    } else {
        showSystemMessage('系统繁忙，请稍后再试', array());
        exit;
    }
}

assign('subTitle', '产品管理');
assign('act', $act);
$smarty->display('product.phtml');
