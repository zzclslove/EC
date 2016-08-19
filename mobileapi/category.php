<?php

/**
 * ECSHOP 获取商品分类信息
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: goods.php 17217 2011-01-19 06:29:08Z liubo $
 */


define('IN_ECS', true);

require('./includes/init.php');
require_once(ROOT_PATH . 'includes/cls_json.php');

$json = new JSON;
$action = isset($_REQUEST['action'])? $_REQUEST['action']:'';

switch ($action)
{
    case 'get_all_category':
    {
        $sql = "SELECT `cat_id`, `cat_name`, `parent_id`, `category_img`, `sort_order` FROM " . $ecs->table('category') . " WHERE `is_show`='1' ORDER BY `sort_order` ASC ";
        $results = array('result' => 'false', 'next' => 'false', 'data' => '');
        $query = $db->query($sql);
        $category_list = array();
        while ($category = $db->fetch_array($query))
        {
            $category['category_img'] = sanitize_url($_SERVER['SERVER_NAME'].'/'.$category['category_img']);
            $category_list[] = $category;
        }

        $results['result'] = 'true';
        $results['data'] = $json->encode($category_list);
        exit($json->encode($results));
        break;
    }
    default:
    {
        $results = array('result'=>'false', 'data'=>'缺少动作');
        exit(json_encode($results));
        break;
    }
}
?>