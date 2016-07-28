<?php

/**
 * ECSHOP 获取商品信息
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

require('./init.php');
require('./lib_main.php');

require_once(ROOT_PATH . 'includes/cls_json.php');

$json = new JSON;
$action = isset($_REQUEST['action'])? $_REQUEST['action']:'';

switch ($action)
{
    case 'get_goods_list':
    {
        $page = empty($_REQUEST['page']) ? 1 : intval($_REQUEST['page']);
        $start = ($page - 1) * 10;

        $day = getdate();
        $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        $filter['cat_id']           = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['intro_type']       = empty($_REQUEST['intro_type']) ? '' : trim($_REQUEST['intro_type']);
        $filter['keyword']          = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'goods_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = $filter['cat_id'] > 0 ? " AND " . get_children($filter['cat_id']) : '';

        /* 推荐类型 */
        switch ($filter['intro_type'])
        {
            case 'is_best':
                $where .= " AND is_best=1";
                break;
            case 'is_hot':
                $where .= ' AND is_hot=1';
                break;
            case 'is_new':
                $where .= ' AND is_new=1';
                break;
            case 'is_promote':
                $where .= " AND is_promote = 1 AND promote_price > 0 AND promote_start_date <= '$today' AND promote_end_date >= '$today'";
                break;
            case 'all_type';
                $where .= " AND (is_best=1 OR is_hot=1 OR is_new=1 OR (is_promote = 1 AND promote_price > 0 AND promote_start_date <= '" . $today . "' AND promote_end_date >= '" . $today . "'))";
        }

        /* 关键字 */
        if (!empty($filter['keyword']))
        {
            $where .= " AND (goods_sn LIKE '%" . mysql_like_quote($filter['keyword']) . "%' OR goods_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%')";
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('goods'). " AS g WHERE 1 = 1 $where";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / 10) : 1;

        $sql = "SELECT goods_id as id, goods_name as name, market_price, promote_price, goods_brief as brief, goods_thumb as thumb, shop_price, promote_end_date ".
            " FROM " . $GLOBALS['ecs']->table('goods') . " AS g WHERE 1 = 1 $where" .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT " . $start . ", 10";
        $row = $GLOBALS['db']->getAll($sql);
        foreach($row as $key => $value){
            $row[$key]['promote_end_date'] = local_date('Y-m-d H:i', $value['promote_end_date']);
        }
        $results = array('goods' => $row, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

        exit($json->encode($results));  
        break;
    }
    case 'get_goods_info':
    {
        $goods_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
        $goods = get_goods_info($goods_id);
        if ($goods === false){
            $results = array('result'=>'false', 'data'=>'没有找到产品');
            exit(json_encode($results));
        }else{
            /* 取得评论列表 */
            $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
                " WHERE id_value = '$goods_id' AND comment_type = '0' AND status = 1 AND parent_id = 0");
            $page_count = ($count > 0) ? intval(ceil($count / 5)) : 1;
            $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
                " WHERE id_value = '$goods_id' AND comment_type = '0' AND status = 1 AND parent_id = 0".
                ' ORDER BY comment_id DESC';
            $res = $GLOBALS['db']->selectLimit($sql, 5, 0);
            $arr = array();
            $ids = '';
            while ($row = $GLOBALS['db']->fetchRow($res))
            {
                $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
                $arr[$row['comment_id']]['id']       = $row['comment_id'];
                $arr[$row['comment_id']]['email']    = $row['email'];
                $arr[$row['comment_id']]['username'] = $row['user_name'];
                $arr[$row['comment_id']]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
                $arr[$row['comment_id']]['content']  = nl2br(str_replace('\n', '<br />', $arr[$row['comment_id']]['content']));
                $arr[$row['comment_id']]['rank']     = $row['comment_rank'];
                $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            }
            /* 取得已有回复的评论 */
            if ($ids)
            {
                $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
                    " WHERE parent_id IN( $ids )";
                $res = $GLOBALS['db']->query($sql);
                while ($row = $GLOBALS['db']->fetch_array($res))
                {
                    $arr[$row['parent_id']]['re_content']  = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
                    $arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
                    $arr[$row['parent_id']]['re_email']    = $row['email'];
                    $arr[$row['parent_id']]['re_username'] = $row['user_name'];
                }
            }
            $comments = array();
            foreach($arr as $key => $value){
                array_push($comments, $value);
            }
            $goods['comments'] = $comments;
            $goods['comments_count'] = $count;

            /* 商品购买记录 */
            $sql = 'SELECT u.user_name, og.goods_number, oi.add_time, IF(oi.order_status IN (2, 3, 4), 0, 1) AS order_status ' .
                'FROM ' . $ecs->table('order_info') . ' AS oi LEFT JOIN ' . $ecs->table('users') . ' AS u ON oi.user_id = u.user_id, ' . $ecs->table('order_goods') . ' AS og ' .
                'WHERE oi.order_id = og.order_id AND ' . time() . ' - oi.add_time < 2592000 AND og.goods_id = ' . $goods_id . ' ORDER BY oi.add_time DESC LIMIT ' . (($page > 1) ? ($page-1) : 0) * 5 . ',5';
            $bought_notes = $db->getAll($sql);
            foreach ($bought_notes as $key => $val)
            {
                $bought_notes[$key]['add_time'] = local_date("Y-m-d", $val['add_time']);
            }
            $sql = 'SELECT count(*) ' .
                'FROM ' . $ecs->table('order_info') . ' AS oi LEFT JOIN ' . $ecs->table('users') . ' AS u ON oi.user_id = u.user_id, ' . $ecs->table('order_goods') . ' AS og ' .
                'WHERE oi.order_id = og.order_id AND ' . time() . ' - oi.add_time < 2592000 AND og.goods_id = ' . $goods_id;
            $count = $db->getOne($sql);
            $goods['notes'] = $bought_notes;
            $goods['notes_count'] = $count;

            $goods['goods_img'] = get_goods_gallery($goods_id);

            /* 购买该商品可以得到多少钱的红包 */
            if ($goods['bonus_type_id'] > 0)
            {
                $time = gmtime();
                $sql = "SELECT type_money FROM " . $ecs->table('bonus_type') .
                    " WHERE type_id = '$goods[bonus_type_id]' " .
                    " AND send_type = '" . SEND_BY_GOODS . "' " .
                    " AND send_start_date <= '$time'" .
                    " AND send_end_date >= '$time'";
                $goods['bonus_money'] = floatval($db->getOne($sql));
                if ($goods['bonus_money'] > 0)
                {
                    $goods['bonus_money'] = price_format($goods['bonus_money']);
                }
            }

            $properties = get_goods_properties($goods_id);
            $goods['props'] = $properties['pro'];
            $goods['specs'] = $properties['spe'];

            /* 记录浏览历史 */
            if (!empty($_COOKIE['ECS']['history']))
            {
                $history = explode(',', $_COOKIE['ECS']['history']);

                array_unshift($history, $goods_id);
                $history = array_unique($history);

                while (count($history) > $_CFG['history_number'])
                {
                    array_pop($history);
                }

                setcookie('ECS[history]', implode(',', $history), gmtime() + 3600 * 24 * 30);
            }
            else
            {
                setcookie('ECS[history]', $goods_id, gmtime() + 3600 * 24 * 30);
            }

            /* 更新点击次数 */
            $GLOBALS['db']->query('UPDATE ' . $ecs->table('goods') . " SET click_count = click_count + 1 WHERE goods_id = '$_REQUEST[id]'");

            exit(json_encode($goods));
        }

        break;
    }
    case 'get_shop_info':
    {
        $results = array('result' => 'true', 'data' => array());
        $sql = "SELECT `value` FROM " . $ecs->table('shop_config') . " WHERE code='shop_name'";
        $shop_name = $db->getOne($sql);
        $sql = "SELECT `value` FROM " . $ecs->table('shop_config') . " WHERE code='currency_format'";
        $currency_format = $db->getOne($sql);
        $sql = "SELECT r.region_name, sc.value FROM " . $ecs->table('region') . " AS r INNER JOIN " . $ecs->table('shop_config') . " AS sc ON r.`region_id`=sc.`value` WHERE sc.`code`='shop_country' OR sc.`code`='shop_province' OR sc.`code`='shop_city' ORDER BY sc.`id` ASC";

        $shop_region = $db->getAll($sql);
        $results['data'] = array
        (
            'shop_name' => $shop_name,
            'domain' => 'http://' . $_SERVER['SERVER_NAME'] . '/',
            'shop_region' => $shop_region[0]['region_name'] . ' ' . $shop_region[1]['region_name'] . ' ' . $shop_region[2]['region_name'],
            'currency_format' => $currency_format
        );
        exit($json->encode($results));
        break;
    }
    case 'get_shipping':
    {
        $results = array('result' => 'false', 'data' => array());
        $sql = "SELECT `shipping_id`, `shipping_name`, `insure` FROM " . $ecs->table('shipping');
        $result = $db->getAll($sql);
        if (!empty($result))
        {
            $results['result'] = 'true';
            $results['data'] = $result;
        }
        exit($json->encode($results));
        break;
    }
    case 'get_goods_attribute':
    {
        $results = array('result' => 'false', 'data' => array());
        $goods_id = isset($data['goods_id'])? intval($data['goods_id']):0;
        if (!empty($goods_id))
        {
            $sql = "SELECT t2.attr_name, t1.attr_value FROM " . $ecs->table('goods_attr') . " AS t1 LEFT JOIN " . $ecs->table('attribute') . " AS t2 ON t1.attr_id=t2.attr_id WHERE t1.goods_id='$goods_id'";
            $result = $db->getAll($sql);
            if (!empty($result))
            {
                $results['result'] = 'true';
                $results['data'] = $result;
            }
        }
        else
        {
            $results = array('result'=>'false', 'data'=>'缺少商品ID，无法获取其属性');
        }
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