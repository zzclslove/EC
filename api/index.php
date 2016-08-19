<?php
define('IN_ECS', true);

require('./init.php');
require('./lib_main.php');
require_once(ROOT_PATH . 'includes/cls_json.php');

$json = new JSON;
$action = isset($_REQUEST['action'])? $_REQUEST['action']:'';

switch ($action)
{
    case 'get_init_data':
    {
        $results = array('result' => 'false', 'next' => 'false', 'data' => array());
        $initData = array(
            'productImageHeight' => $GLOBALS['_CFG']['image_width'],
            'productImageWidth' => $GLOBALS['_CFG']['image_height'],
            'logined'           => empty($_SESSION['user_id'])?false:true,
            'topicList' => array(),
            'recommendProducts' => array(
                'hotProductList' => array(),
                'bestProductList' => array(),
                'newProductList' => array()
            ),
            'categoryList' => array()
        );

        //获取首页广告
        $sql = "SELECT `topic_id`, `topic_img`, `title` FROM " . $ecs->table('topic') .
            "WHERE  " . gmtime() . " >= start_time and " . gmtime() . "<= end_time";
        $query = $db->query($sql);
        while ($topics = $db->fetch_array($query))
        {
            $initData['topicList'][] = $topics;
        }

        //获取所有类别
        $sql = "SELECT `cat_id`, `cat_name`, `parent_id`, `category_img`, `sort_order` FROM " . $ecs->table('category') . " WHERE `is_show`='1' ORDER BY `sort_order` ASC ";
        $query = $db->query($sql);
        while ($category = $db->fetch_array($query))
        {
            $initData['categoryList'][] = $category;
        }

        //获取推荐产品
        $initData['recommendProducts']['bestProductList'] = get_recommend_goods('best');
        $initData['recommendProducts']['hotProductList'] = get_recommend_goods('hot');
        $initData['recommendProducts']['newProductList'] = get_recommend_goods('new');

        $results['result'] = 'true';
        $results['data'] = $json->encode($initData);
        exit($json->encode($results));
        break;
    }
    case 'get_topic_info':
    {
        $topic_id  = empty($_REQUEST['topic_id']) ? 0 : intval($_REQUEST['topic_id']);
        $sql = "SELECT * FROM " . $ecs->table('topic') . " WHERE topic_id = '$topic_id'";
        $results = array('result' => 'false', 'next' => 'false', 'data' => array());
        $topic = $db->getRow($sql);
        $topic['data'] = addcslashes($topic['data'], "'");
        $tmp = @unserialize($topic["data"]);
        $arr = (array)$tmp;

        $goods_id = array();

        foreach ($arr AS $key=>$value)
        {
            foreach($value AS $k => $val)
            {
                $opt = explode('|', $val);
                $arr[$key][$k] = $opt[1];
                $goods_id[] = $opt[1];
            }
        }
        $sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, " .
            'g.promote_start_date, g.promote_end_date, g.goods_thumb ' .
            'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE " . db_create_in($goods_id, 'g.goods_id');
        $res = $GLOBALS['db']->query($sql);

        $sort_goods_arr = array();
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            if ($row['promote_price'] > 0)
            {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $row['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            }
            else
            {
                $row['promote_price'] = '';
            }

            if ($row['shop_price'] > 0)
            {
                $row['shop_price'] =  price_format($row['shop_price']);
            }
            else
            {
                $row['shop_price'] = '';
            }

            $row['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
            $row['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
            $row['short_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $row['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $row['short_style_name'] = add_style($row['short_name'], $row['goods_name_style']);

            foreach ($arr AS $key => $value)
            {
                foreach ($value AS $val)
                {
                    if ($val == $row['goods_id'])
                    {
                        $key = $key == 'default' ? $_LANG['all_goods'] : $key;
                        $sort_goods_arr[$key][] = $row;
                    }
                }
            }
        }
        $topic['data'] = $sort_goods_arr;
        $results['result'] = 'true';
        $results['data'] = $topic;
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