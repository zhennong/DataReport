<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/22
 * Time: 17:34
 */

namespace Admin\Controller;


use Common\Tools;

class CompanyController extends AdminController
{
    public function company_index()
    {
    }

    /**
     * 公司产品列表
     */
    public function companyGoodsList()
    {
        // 字段
        $column = [
//            ['select' => 'company.company', 'as' => 'company_name', 'show_name' => 'company_name'],
//            ['select' => 'company.username', 'as' => 'company_username', 'show_name' => '用户名'],
            ['select' => 'goods.company', 'as' => 'company', 'show_name' => '公司'],
            ['select' => 'goods.itemid', 'as' => 'goods_id', 'show_name' => '产品id'],
            ['select' => 'goods.title', 'as' => 'goods_title', 'show_name' => '产品'],
        ];
        foreach ($column as $k => $v) {
            $field[] = "{$v['select']} AS {$v['as']}";
        }
        $field = Tools::arr2str($field);
        $this->assign(['column' => $column]);
        if ($draw = I("get.draw")) {
            // 预定义
            $start = $_GET['start'];
            $limit = $_GET['length'];
            $order = $_GET['order'];
            $search = [" 1 "];

            // 重组条件
            $order = "{$column[$order[0]['column']]['as']} {$order[0]['dir']}";
            foreach ($_GET['columns'] as $k => $v) {
                if ($v['search']['value'] != '') {
                    $search[] = "{$column[$v['data']]['select']} LIKE '%{$v[search][value]}%'";
                }
            }
            $search = Tools::arr2str($search, " AND ");

            // sql主语句
            $_sql = "SELECT {$field} 
                FROM __MALL_sell_5 AS goods
                WHERE {$search}
                ORDER BY {$order}";

            // 查询总数
            $sql = "SELECT COUNT(*) as total FROM ({$_sql}) AS x ";
            $x = $this->MallDb->list_query($sql);
            $total = $x[0]['total'];

            // 查询数据并重组
            $sql = "{$_sql}
                LIMIT {$start}, {$limit}";
            $data = $this->MallDb->list_query($sql);
            $x = [];
            foreach ($data as $k => $v) {
                foreach ($column as $key => $value) {
                    $x[$k][] = $v[$value['as']];
                }
            }

            //获取Datatables发送的参数 必要
            $show = [
                "draw" => $draw,
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $x,
            ];
            $x = json_encode($show);
            echo $x;
            exit();
        } else {
            $this->display();
        }
    }
}