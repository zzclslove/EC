DROP TABLE IF EXISTS `ecs_verify_code`;

CREATE TABLE `ecs_verify_code` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`mobile` char(12) NOT NULL,
`getip` char(15) NOT NULL,
`verifycode` char(6) NOT NULL,
`dateline` int(10) unsigned NOT NULL default '0',
`reguid` mediumint(8) unsigned default '0',
`regdateline` int(10) unsigned default '0',
`status` tinyint(1) NOT NULL default '1',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

ALTER TABLE  `ecs_shop_config` CHANGE  `code`  `code` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';

INSERT INTO `ecs_shop_config` VALUES ('37', '0', 'ihuyi_sms_', 'group', '', '', '', '1');

INSERT INTO `ecs_shop_config` VALUES ('8888', '37', 'ihuyi_sms_debug', 'select', '1,0', '', '0', '1');

INSERT INTO `ecs_shop_config` VALUES ('8900', '37', 'ihuyi_sms_mobile_num', 'text', '', '', '10', '1');

INSERT INTO `ecs_shop_config` VALUES ('8910', '37', 'ihuyi_sms_ip_num', 'text', '', '', '10', '1');

INSERT INTO `ecs_shop_config` VALUES ('8920', '37', 'ihuyi_sms_user_name', 'text', '', '', '', '1');
INSERT INTO `ecs_shop_config` VALUES ('8930', '37', 'ihuyi_sms_pass_word', 'password', '', '', '', '1');
INSERT INTO `ecs_shop_config` VALUES ('8935', '37', 'ihuyi_yy_pass_word', 'password', '', '', '', '1');
INSERT INTO `ecs_shop_config` VALUES ('8940', '37', 'ihuyi_sms_shop_mobile', 'text', '', '', '', '1');
INSERT INTO `ecs_shop_config` VALUES ('9004', '37', 'ihuyi_sms_smsgap', 'text', '', '', '120', '1');
INSERT INTO `ecs_shop_config` VALUES ('9005', '37', 'ihuyi_sms_smsyy', 'text', '', '', '3', '1');

INSERT INTO `ecs_shop_config` VALUES ('9006', '37', 'ihuyi_sms_mobile_reg', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9007', '37', 'ihuyi_sms_mobile_reg_value', 'textarea', '', '', '您的验证码是：{$verify_code}。请不要把验证码泄露给其他人。', '1');

INSERT INTO `ecs_shop_config` VALUES ('9008', '37', 'ihuyi_sms_mobile_log', 'select', '1,0', '', '0', '1');

INSERT INTO `ecs_shop_config` VALUES ('9009', '37', 'ihuyi_sms_mobile_pwd', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9010', '37', 'ihuyi_sms_mobile_pwd_value', 'textarea', '', '', '您的用户名：{$user_name}，新密码：{$new_password}。请及时登陆修改密码！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9011', '37', 'ihuyi_sms_mobile_changepwd', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9012', '37', 'ihuyi_sms_mobile_changepwd_value', 'textarea', '', '', '您的用户名：{$user_name}，密码已修改，新密码：{$new_password}。请牢记新密码！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9013', '37', 'ihuyi_sms_mobile_bind', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9014', '37', 'ihuyi_sms_mobile_bind_value', 'textarea', '', '', '您的手机号：{$user_mobile}，绑定验证码：{$verify_code}。一天内提交有效！', '1');
INSERT INTO `ecs_shop_config` VALUES ('9015', '37', 'ihuyi_sms_mobile_cons', 'select', '1,0', '', '0', '1');

INSERT INTO `ecs_shop_config` VALUES ('9016', '37', 'ihuyi_sms_customer_registed', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9017', '37', 'ihuyi_sms_customer_registed_value', 'textarea', '', '', '您注册的用户名：{$user_name}，密码：{$user_pwd}。感谢您的注册！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9018', '37', 'ihuyi_sms_order_placed', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9019', '37', 'ihuyi_sms_order_placed_value', 'textarea', '', '', '您有新的订单：{$order_sn}，收货人：{$consignee}，电话：{$tel}，请及时确认订单！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9020', '37', 'ihuyi_sms_order_canceled', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9021', '37', 'ihuyi_sms_order_canceled_value', 'textarea', '', '', '订单号 ：{$order_sn} 买家已取消订单！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9022', '37', 'ihuyi_sms_order_payed', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9023', '37', 'ihuyi_sms_order_payed_value', 'textarea', '', '', '订单号 ：{$order_sn} 买家付款了。收货人：{$consignee}，电话：{$tel}。请及时安排发货！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9024', '37', 'ihuyi_sms_order_confirm', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9025', '37', 'ihuyi_sms_order_confirm_value', 'textarea', '', '', '订单号 ：{$order_sn} 买家已确认收货！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9026', '37', 'ihuyi_sms_customer_placed', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9027', '37', 'ihuyi_sms_customer_placed_value', 'textarea', '', '', '您的订单：{$order_sn}，收货人：{$consignee} 电话：{$tel}，已经成功提交。感谢您的购买！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9028', '37', 'ihuyi_sms_customer_canceled', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9029', '37', 'ihuyi_sms_customer_canceled_value', 'textarea', '', '', '您的订单：{$order_sn}，已取消！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9030', '37', 'ihuyi_sms_customer_payed', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9031', '37', 'ihuyi_sms_customer_payed_value', 'textarea', '', '', '您的订单：{$order_sn}，已于{$time}付款成功。感谢您的购买！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9032', '37', 'ihuyi_sms_customer_confirm', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9033', '37', 'ihuyi_sms_customer_confirm_value', 'textarea', '', '', '您的订单：{$order_sn}，确认收货成功。感谢您购买与支持，欢迎您下次光临！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9034', '37', 'ihuyi_sms_order_picking', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9035', '37', 'ihuyi_sms_order_picking_value', 'textarea', '', '', '订单号：{$order_sn} 已于{$time}配货。如有问题请及时联系！', '1');

INSERT INTO `ecs_shop_config` VALUES ('9036', '37', 'ihuyi_sms_order_shipped', 'select', '1,0', '', '0', '1');
INSERT INTO `ecs_shop_config` VALUES ('9037', '37', 'ihuyi_sms_order_shipped_value', 'textarea', '', '', '订单号：{$order_sn} 已于{$time}发货，如有问题请及时联系！', '1');