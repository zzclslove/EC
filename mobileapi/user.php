<?php
/**
 * ECSHOP 用户相关信息
 */
define('IN_ECS', true);

require('./includes/init.php');
require_once(ROOT_PATH . 'includes/cls_json.php');
require(ROOT_PATH . 'includes/lib_sms.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/sms.php');

$json = new JSON;
$action = isset($_REQUEST['action'])? $_REQUEST['action']:'';
$mobile = isset($_REQUEST['mobile'])? $_REQUEST['mobile']:'';
$verifycode = isset($_REQUEST['phonecode'])? $_REQUEST['phonecode']:'';

$result = array('error' => 0, 'message' => '');

switch ($action)
{
    case 'send_phone_code':
    {
        /* 是否开启手机短信验证注册 */
        if($_CFG['ihuyi_sms_mobile_reg'] == '0') {
            $result['error'] = 1;
            $result['message'] = $_LANG['ihuyi_sms_mobile_reg_closed'];
            exit($json->encode($result));
        }

        /* 提交的手机号是否正确 */
        if (!ismobile($mobile))
        {
            $result['error'] = 2;
            $result['message'] = $_LANG['invalid_mobile_phone'];
            exit($json->encode($result));
        }

        //手机号码限制
        $count = $db->getOne("SELECT COUNT(id) FROM " . $ecs->table('verify_code') ." WHERE mobile='".$mobile."' and dateline>".strtotime(date('Y-m-d 0:0:0'))." and dateline<".strtotime(date('Y-m-d 23:59:59')));
        if ($count >= $_CFG['ihuyi_sms_mobile_num'])
        {
            $result['error'] = 6;
            $result['message']= "该手机号每日可发送短信超出限制";
            exit($json->encode($result));
        }

        /* 提交的手机号是否已经注册帐号 */
        $sql = "SELECT COUNT(user_id) FROM " . $ecs->table('users') ." WHERE mobile_phone = '$mobile'";

        if ($db->getOne($sql) > 0)
        {
            $result['error'] = 3;
            $result['message'] = $_LANG['mobile_phone_registered'];
            exit($json->encode($result));
        }

        /* 获取验证码请求是否获取过 */
        $sql = "SELECT COUNT(id) FROM " . $ecs->table('verify_code') ." WHERE status=1 AND getip='" . real_ip() . "' AND dateline>'" . gmtime() ."'-".$_CFG['ihuyi_sms_smsgap'];

        if ($db->getOne($sql) > 0)
        {
            $result['error'] = 4;
            $result['message'] = sprintf($_LANG['get_verifycode_excessived'], $_CFG['ihuyi_sms_smsgap']);
            exit($json->encode($result));
        }

        $verifycode = getverifycode();

        $smarty->assign('shop_name',	$_CFG['shop_name']);
        $smarty->assign('user_mobile',	$mobile);
        $smarty->assign('verify_code',  $verifycode);

        $content = $smarty->fetch('str:' . $_CFG['ihuyi_sms_mobile_reg_value']);

        /* 发送注册手机短信验证 */
        $ret = sendsms($mobile, $content);

        if($ret === true)
        {
            //插入获取验证码数据记录
            $sql = "INSERT INTO " . $ecs->table('verify_code') . "(mobile, getip, verifycode, dateline) VALUES ('" . $mobile . "', '" . real_ip() . "', '$verifycode', '" . gmtime() ."')";
            $db->query($sql);

            $result['error'] = 0;
            $result['message'] = $_LANG['send_mobile_verifycode_successed'];
            exit($json->encode($result));
        }
        else
        {
            $result['error'] = 5;
            $result['message'] = $_LANG['send_mobile_verifycode_failured'] . $ret;
            exit($json->encode($result));
        }
        break;
    }
    case "phone_code_check":
    {
        if ($_CFG['shop_reg_closed']){
            $result['error'] = 1;
            $result['message'] = "注册已关闭";
            exit($json->encode($result));
        }else{
            if ($_CFG['ihuyi_sms_mobile_reg'] == '1'){
                /* 提交的手机号是否正确 */
                if(!ismobile($mobile)) {
                    $result['error'] = 2;
                    $result['message'] = "手机号码错误";
                    exit($json->encode($result));
                }

                /* 提交的验证码不能为空 */
                if(empty($verifycode)) {
                    $result['error'] = 3;
                    $result['message'] = "验证码不能为空";
                    exit($json->encode($result));
                }

                /* 提交的手机号是否已经注册帐号 */
                $sql = "SELECT COUNT(user_id) FROM " . $ecs->table('users') . " WHERE mobile_phone = '$mobile'";
                if ($db->getOne($sql) > 0)
                {
                    $result['error'] = 4;
                    $result['message'] = "该手机已被注册";
                    exit($json->encode($result));
                }

                /* 验证手机号验证码和IP */
                $sql = "SELECT COUNT(id) FROM " . $ecs->table('verify_code') ." WHERE mobile='$mobile' AND verifycode='$verifycode' AND getip='" . real_ip() . "' AND status=1 AND dateline>'" . gmtime() ."'-86400";//验证码一天内有效
                if ($db->getOne($sql) == 0)
                {
                    $result['error'] = 5;
                    $result['message'] = "验证码错误";
                    exit($json->encode($result));
                }else{
                    $result['message'] = "验证通过";
                    exit($json->encode($result));
                }
            }
        }
        break;
    }
    default:
    {
        $results = array('result'=>'false', 'data'=>'缺少动作');
        exit($json->encode($results));
        break;
    }
}

?>