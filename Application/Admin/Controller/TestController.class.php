<?php
/**
 * Created by PhpStorm.
 * User: iredbaby
 * Date: 16-3-5
 * Time: 下午5:40
 */

namespace Admin\Controller;


use Common\Controller\CommonController;

class TestController extends CommonController
{
    public function test1(){
//        echo C('DB_TYPE');
    }

    public function test2(){
        echo C('DB_TYPE');
        exit;
    }
}