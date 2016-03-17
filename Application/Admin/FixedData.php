<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/9
 * Time: 17:58
 */

namespace Admin;


trait FixedData
{
    // 价格区间
    public $price_range = [
        ['range_name' => '少于100元', 'start_price' => 1, 'end_price' => 100],
        ['range_name' => '101-200元', 'start_price' => 101, 'end_price' => 200],
        ['range_name' => '201-300元', 'start_price' => 201, 'end_price' => 300],
        ['range_name' => '301-400元', 'start_price' => 301, 'end_price' => 400],
        ['range_name' => '401-500元', 'start_price' => 401, 'end_price' => 500],
        ['range_name' => '501-600元', 'start_price' => 501, 'end_price' => 600],
        ['range_name' => '601-700元', 'start_price' => 601, 'end_price' => 700],
        ['range_name' => '701-800元', 'start_price' => 701, 'end_price' => 800],
        ['range_name' => '801-900元', 'start_price' => 801, 'end_price' => 900],
        ['range_name' => '901-1000元', 'start_price' => 901, 'end_price' => 1000],
        ['range_name' => '1001-2000元', 'start_price' => 1001, 'end_price' => 2000],
        ['range_name' => '2001-3000元', 'start_price' => 2001, 'end_price' => 3000],
        ['range_name' => '3001-5000元', 'start_price' => 3001, 'end_price' => 5000],
        ['range_name' => '5001-10000元', 'start_price' => 5001, 'end_price' => 10000],
        ['range_name' => '多于10001元', 'start_price' => 10001],
    ];

    // 部门数据
    public $depart_data = [
        '1' => ['depart_id' => 1, 'depart_name' => '交易部'],
        '2' => ['depart_id' => 1, 'depart_name' => '售后部'],
        '3' => ['depart_id' => 1, 'depart_name' => '产品部'],
        '4' => ['depart_id' => 1, 'depart_name' => '管理员'],
        '5' => ['depart_id' => 1, 'depart_name' => '财务部'],
        '6' => ['depart_id' => 1, 'depart_name' => '货运部'],
    ];
}