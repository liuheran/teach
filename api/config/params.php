<?php
return [
    'enableTrack' =>true,
	'imageUrl' => '',
    'adminEmail' => 'admin@example.com',
    'errorCode'  => [
        //用户登录
        '100001' => '手机号码错误',
        '100002' => '密码错误',
    	'100003' => '用户已存在',
    	'100004' => '表单验证失败',
    	'100005' => '注册失败',
    	'100006' => '验证失败',
    	'100203' => '登录失败',    		
    	'100204' => '获取Token失败',
    	'100320' => '参数有误',
    	'100321' => '修改失败',
        
        '100501' => 'ID错误',
        
    	//获取用户信息通过手机号
    	'100401' => '获取用户ID错误',
    	'100402' => '获取用户信息错误',
        //喜欢
        '301001' => '参数错误',
        '301002' => '添加失败',
        '302001' => '参数错误',
        '302002' => '删除失败',
        '303001' => '参数错误',
        '303002' => '密码错误',
        '303003' => '查询错误',
        //喜欢
        '401001' => '参数错误',
        '401002' => '添加失败',
        '402001' => '参数错误',
        '402002' => '删除失败',
        '403001' => '参数错误',
        '403002' => '密码错误',
        '403003' => '查询错误',
        //用户评论
        '501001' => '参数错误',
        '501002' => '插入数据库错误',
        '502001' => '参数错误',
        '502002' => '删除数据库错误',
        '503001' => '参数错误',
        '503002' => '修改数据库错误',
        //我保护的人，保护我的人
        '601001' => '参数错误',
        '601002' => '添加失败',
        '602001' => '参数错误',
        '602002' => '删除失败',
        '603001' => '参数错误',
        '603002' => '密码错误',
        '603003' => '查询错误',
        //紧急联系人
        '701001' => '参数错误',
        '701002' => '添加失败',
        '702001' => '参数错误',
        '702002' => '删除失败',
        '703001' => '参数错误',
        '703002' => '查询失败',
        '703003' => '查询错误',
        //用户通知
        '801001' => '参数错误',
        '801002' => '添加失败',
        '802001' => '参数错误',
        '802002' => '删除失败',
        '803001' => '参数错误',
        '803002' => '删除失败',
        '804001' => '参数错误',
        '804002' => '查询失败',
        '804003' => '查询错误',
        //版本升级通知
        '151000' => '无需升级',
        '151001' => '参数缺失',
        '151002' => '参数错误',
        //用户搜索
        '160001' => '用户ID参数错误',
        '160002' => '用户距离参数错误',
    ],
];
