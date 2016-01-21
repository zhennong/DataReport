<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 1/21/16
 * Time: 2:07 PM
 */

namespace Common\Common;

class Tools {
    /**
     * 系统加密方法
     * @param string $data 要加密的字符串
     * @param string $key  加密密钥
     * @param int $expire  过期时间 单位 秒
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function think_encrypt($data, $key = '', $expire = 0) {
        $key  = md5(empty($key) ? 'DATA_AUTH_KEY' : $key);
        $data = base64_encode($data);
        $x    = 0;
        $len  = strlen($data);
        $l    = strlen($key);
        $char = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        $str = sprintf('%010d', $expire ? $expire + time():0);

        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
        }
        return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
    }

    /**
     * 系统解密方法
     * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
     * @param  string $key  加密密钥
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function think_decrypt($data, $key = ''){
        $key    = md5(empty($key) ? 'DATA_AUTH_KEY' : $key);
        $data   = str_replace(array('-','_'),array('+','/'),$data);
        $mod4   = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data   = base64_decode($data);
        $expire = substr($data,0,10);
        $data   = substr($data,10);

        if($expire > 0 && $expire < time()) {
            return '';
        }
        $x      = 0;
        $len    = strlen($data);
        $l      = strlen($key);
        $char   = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }else{
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }

    /**
     * 数据签名认证
     * @param  array  $data 被认证的数据
     * @return string       签名
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function data_auth_sign($data) {
        //数据类型检测
        if(!is_array($data)){
            $data = (array)$data;
        }
        ksort($data); //排序
        $code = http_build_query($data); //url编码并生成query字符串
        $sign = sha1($code); //生成签名
        return $sign;
    }

    /**
     * 格式化字节大小
     * @param  number $size      字节数
     * @param  string $delimiter 数字和单位分隔符
     * @return string            格式化后的带单位的大小
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function format_bytes($size, $delimiter = '') {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function list2tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
        // 创建Tree
        $tree = [];
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * 将list2tree的树还原成列表
     * @param  array $tree  原来的树
     * @param  string $child 孩子节点的键
     * @param  string $order 排序显示的键，一般是主键 升序排列
     * @param  array  $list  过渡用的中间数组，
     * @return array        返回排过序的列表数组
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public static function tree2list($tree, $child = '_child', $order='id', &$list = []){
        if(is_array($tree)) {
            $refer = [];
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if(isset($reffer[$child])){
                    unset($reffer[$child]);
                    tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = list_sort_by($list, $order, $sortby='asc');
        }
        return $list;
    }

    /**
     * 获取节点所有父级元素
     * @param array $list 数据
     * @param int $node_id 节点id
     * @param int $pk 主键
     * @param int $pid 外键
     * @param int $root 根节点
     * @return array 父节点
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function get_list_parents($list,$node_id,$pk='id',$pid='pid',$root=0)
    {
        $i = $root;
        while($node_id!=$root){
            foreach ($list as $k => $v){
                if ($v[$pk]==$node_id){
                    $node_to_root[$i++] = $k;
                    $node_id = $v[$pid];
                }
            }
        }
        $i = $i-1;
        for($i;$i>=$root;$i--){
            $root_to_node[] = $node_to_root[$i];
            $parent_list[] = $list[$node_to_root[$i]];
        }
        return $parent_list;
    }

    /**
     * 获取节点排序
     * @param array $tree 数据
     * @param int $node_id 节点id
     * @param int $start 起始值
     * @param string $sort_name 排序字段下标
     * @param array $p
     * @return array 带节点排序的数据
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function get_tree_node_sort($tree,$start=0,$sort_name='_node_sort',&$p=[]){
        $start++;
        foreach ($tree as $k => $v) {
            $p[$k] = $v;
            $p[$k][$sort_name] = $start-1;
            if ($v['_child']) {
                get_tree_node_sort($v['_child'],$start,$sort_name,$p[$k]['_child']);
            }
        }
        return $p;
    }

    /**
     * 获取list数组单个字段值
     * @param array $list 数组
     * @param int $key 索引键
     * @param int $search 索引值
     * @param int $field 查询键
     * @return int|string 查询值
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function get_list_field($list,$key,$search,$field){
        foreach ($list as $k => $v){
            if($v[$key]==$search){
                return $v[$field];
            }
        }
    }

    /**
     * 字符串转换为数组，主要用于把分隔符调整到第二个参数
     * @param  string $str  要分割的字符串
     * @param  string $glue 分割符
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function str2arr($str, $glue = ','){
        return explode($glue, $str);
    }

    /**
     * 数组转换为字符串，主要用于把分隔符调整到第二个参数
     * @param  array  $arr  要连接的数组
     * @param  string $glue 分割符
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function arr2str($arr, $glue = ','){
        return implode($glue, $arr);
    }

    public static function array_group1($arr,$by)
    {
        $x = [];
        foreach ($arr as $k => $v) {
            if (!in_array($v[$by], $x)) {
                $x[] = $v[$by];
            }
        }
        foreach ($arr as $k => $v) {
            foreach ($x as $k1 => $v1) {
                if ($v[$by]==$v1) {
                    $y[$v1][]=$v;
                }
            }
        }
        return $y;
    }
    public static function array_group2($arr,$by)
    {
        $x = [];
        $arr = self::array_group1($arr, $by);
        foreach($arr as $k => $v){
            $x[] = $v;
        }
        return $x;
    }
    public static function array_group3($arr,$by)
    {
        $x = [];
        $keys = array_keys($arr[0]);
        $arr = self::array_group2($arr, $by);
        foreach ($arr as $k => $v){
            $x[] = self::array_group4($v,$keys,$by);
        }
        return $x;
    }
    public static function array_group4($arr,$keys,$by) {
        $x = [];
        foreach ($arr as $k => $v){
            foreach ($keys as $key => $value) {
                if ($value == $by) {
                    $x[$by] = $v[$value];
                }else{
                    $x[$value][] = $v[$value];
                }
            }
        }
        return $x;
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    public static function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }

    /**
     * 生成目录
     * @access public
     * @param string $path 目录位置与名称
     * @return void
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function createDir($path)
    {
        return is_dir($path) or (createDir(dirname($path)) and mkdir($path, 0777));
    }

    /**
     * 图片裁剪处理函数
     * @param  string $img_url  图片位置 must
     * @param  string $save_path 处理完毕的保存路径 must
     * @param  string $save_name 处理完毕的保存名称 must
     * @param  array $crop 裁剪参数
     * @param  array $thumb 缩略参数
     * @return array
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    /*function cutImage($img_url,$save_path,$save_name,$crop=['x1'=>0,'y1'=>0,'w'=>200,'h'=>200],$thumb=['w'=>200,'h'=>200])
    {
        $image = new \Think\Image(\Think\Image::IMAGE_GD,$img_url); // GD库
        createDir($save_path);
        $save_url = $save_path.$save_name;
        $image->crop($crop['w'],$crop['h'],$crop['x1'],$crop['y1'])->thumb($thumb['w'],$thumb['h'])->save($save_url);
        return $save_url;
    }*/

    /**
     * 获取当前页面完整URL地址
     */
    public static function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    /**
     * 根据时间戳返回星期几
     * @param string $time 时间戳
     * @return 星期几
     */
    public static function weekday($time)
    {
        if(is_numeric($time))
        {
            $weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
            return $weekday[date('w', $time)];
        }
        return false;
    }

    /**
     * 浏览器友好的变量输出
     * @param mixed $var 变量
     * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
     * @param string $label 标签 默认为空
     * @param boolean $strict 是否严谨 默认为true
     * @return void|string
     */
    static function dump($var, $echo=true, $label=null, $strict=true) {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }

    /**
     * 生成随机二维数组
     * @param $l 长度
     * @return array
     */
    static function get_rand_arr($l){
        $x = [];
        for($i=0;$i<$l;$i++){
            $x[$i]['rand'] = rand(1000,9999);
        }
        return $x;
    }
}
