<?php
return array(
    /* 数据库设置 */
    'DB_TYPE'           =>  'mysql',     	// 数据库类型
    'DB_HOST'           =>  '192.168.0.15', 	// 服务器地址
    'DB_NAME'           =>  'data_report',      // 数据库名
    'DB_USER'           =>  'root',     	// 用户名
    'DB_PWD'            =>  'root',     	// 密码
    'DB_PORT'           =>  '3306',     	// 端口
    'DB_PREFIX'         =>  'dr_',      	// 数据库表前缀
    'DB_DEBUG'          =>  true, 		// 数据库调试模式 开启后可以记录SQL日志

    /* 主题设置 */
    'DEFAULT_THEME' => 'default',

    /* 业务网站数据库 */
    'BUSINESS_DB' => 'mysql://root:root@192.168.0.15:3306/nongyao001_com',
    'BUSINESS_DB_TABLE_PREFIX' => 'destoon_',
    
     /*验证码设置*/
    'VERIFY' => array(
	'fontSize'    =>  60,    // 验证码字体大小
	'length'      =>  4,     // 验证码位数
	//'useNoise'    =>    false, // 关闭验证码杂点
    )
);