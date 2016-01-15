<?php
namespace Common\Controller;

use Think\Controller;

/**
 * Description of CommonController
 *
 * @author wodrow
 */
abstract class CommonController extends Controller{
    public function _empty(){
        $this->display('Public:Error');
    }
}
