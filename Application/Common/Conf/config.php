<?php
return array(
	//'配置项'=>'配置值'
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => 'mysql5', // 服务器地址
	'DB_NAME'   => 'tp32', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => '123456', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => 'tongji_', // 数据库表前缀
	'DB_CHARSET'=> 'utf8', // 字符集
	//'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增

	// 注册类库的自动加载
	'AUTOLOAD_NAMESPACE' => array(
		'nelexa' => APP_PATH . 'Library/Nelexa/', // 根据实际情况配置路径
	),
);