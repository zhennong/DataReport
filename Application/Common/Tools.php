<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/6/16
 * Time: 10:11 AM
 */

namespace Common;


class Tools
{
    /**
     * 系统加密方法
     * @param string $data 要加密的字符串
     * @param string $key  加密密钥
     * @param int $expire  过期时间 单位 秒
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function think_encrypt($data, $key = '', $expire = 0) {
        $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
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
        $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
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
     * 设置跳转页面URL
     * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function set_redirect_url($url){
        cookie('redirect_url', $url);
    }
    /**
     * 获取跳转页面URL
     * @return string 跳转页URL
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public static function get_redirect_url(){
        $url = cookie('redirect_url');
        return empty($url) ? __APP__ : $url;
    }
    /**
     * 对查询结果集进行排序
     * @access public
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sortby 排序类型
     * asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    public static function list_sort_by($list,$field, $sortby='asc') {
        if(is_array($list)){
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ( $refer as $key=> $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
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
     * 将list_to_tree的树还原成列表
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
                    Tools::tree2list($value[$child], $child, $order, $list);
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
     * 获取有子元素的列
     */
    public static function get_p_list($tree,$child = '_child',$order='id',&$list=[]){
        foreach($tree as $k => $v){
            if($v[$child]){
                unset($v[$child]);
                $list[] = $v;
                self::get_p_list($tree[$k][$child],$child,$order,$list);
            }
        }
        return $list;
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
                self::get_tree_node_sort($v['_child'],$start,$sort_name,$p[$k]['_child']);
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
     * @author wodrow <451611cv@gmail.com | 1173957281@qq.com>
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
     * @author wodrow <451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function cutImage($img_url,$save_path,$save_name,$crop=['x1'=>0,'y1'=>0,'w'=>200,'h'=>200],$thumb=['w'=>200,'h'=>200])
    {
        $image = new \Think\Image(\Think\Image::IMAGE_GD,$img_url); // GD库
        createDir($save_path);
        $save_url = $save_path.$save_name;
        $image->crop($crop['w'],$crop['h'],$crop['x1'],$crop['y1'])->thumb($thumb['w'],$thumb['h'])->save($save_url);
        return $save_url;
    }
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
     * 调试输出|调试模式下
     * @param  mixed $test 调试变量
     * @param  int $style 模式
     * @param  int $stop 是否停止
     * @return void       浏览器输出
     * @author wodrow <wodrow451611cv@gmail.com | 1173957281@qq.com>
     */
    public static function _vp($test,$stop=0,$style=0)
    {
        switch ($style) {
            case 0:
                echo "<pre>";
                var_dump($test);
                echo "</pre>";
                break;
            case 1:
                echo "<pre>";
                var_dump($test);
                echo "<hr/>";
                for($i=0;$i<100;$i++){
                    echo $i."<hr/>";
                }
                echo "</pre>";
                break;
            case 2:
                $x = "##".date('Y-m-d H:i:s',time())."\r\r```\r".var_export($test, true)."\r```\r";
                file_put_contents(C("DOCUMENT_ROOT").RUNTIME_PATH.'/VPFILE.md',$x);
                break;
            case 3:
                $x = "##".date('Y-m-d H:i:s',time())."\r\r```\r".var_export($test, true)."\r```\r";
                file_put_contents(C("DOCUMENT_ROOT").RUNTIME_PATH.'/VPFILE.md',$x,FILE_APPEND);
                break;
        }
        if ($stop!=0) {
            exit("<hr/>");
        }
    }

    /**
     * 修改权限
     * @param $path
     * @param $filemode
     * @return bool
     */
    public static function chmodr($path, $filemode) {
        if (!is_dir($path))
            return chmod($path, $filemode);
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if($file != '.' && $file != '..') {
                $fullpath = $path.'/'.$file;
                if(is_link($fullpath))
                    return FALSE;
                elseif(!is_dir($fullpath) && !chmod($fullpath, $filemode))
                    return FALSE;
                elseif(!self::chmodr($fullpath, $filemode))
                    return FALSE;
            }
        }
        closedir($dh);
        if(chmod($path, $filemode))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 从数组中删除空白的元素（包括只有空白字符的元素）
     *
     * 用法：
     * @code php
     * $arr = array('', 'test', '   ');
     * ArrayHelper::removeEmpty($arr);
     *
     * dump($arr);
     *   // 输出结果中将只有 'test'
     * @endcode
     *
     * @param array $arr 要处理的数组
     * @param boolean $trim 是否对数组元素调用 trim 函数
     */
    public static function removeEmpty(& $arr, $trim = TRUE)
    {
        foreach ($arr as $key => $value)
        {
            if (is_array($value))
            {
                self::removeEmpty($arr[$key]);
            }
            else
            {
                $value = trim($value);
                if ($value == '')
                {
                    unset($arr[$key]);
                }
                elseif ($trim)
                {
                    $arr[$key] = $value;
                }
            }
        }
    }

    /**
     * 从一个二维数组中返回指定键的所有值
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $values = ArrayHelper::getCols($rows, 'value');
     *
     * dump($values);
     *   // 输出结果为
     *   // array(
     *   //   '1-1',
     *   //   '2-1',
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $col 要查询的键
     *
     * @return array 包含指定键所有值的数组
     */
    public static function getCols($arr, $col ,$add_quotation_marks = false)
    {
        $ret = array();
        foreach ($arr as $row)
        {
            if (isset($row[$col])) {
                if($add_quotation_marks&&is_string($row[$col])){
                    $ret[] = "'" . $row[$col] . "'";
                }else{
                    $ret[] = $row[$col];
                }
            }
        }
        return $ret;
    }

    /**
     * 将一个二维数组转换为 HashMap，并返回结果
     *
     * 用法1：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = ArrayHelper::toHashmap($rows, 'id', 'value');
     *
     * dump($hashmap);
     *   // 输出结果为
     *   // array(
     *   //   1 => '1-1',
     *   //   2 => '2-1',
     *   // )
     * @endcode
     *
     * 如果省略 $valueField 参数，则转换结果每一项为包含该项所有数据的数组。
     *
     * 用法2：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = ArrayHelper::toHashmap($rows, 'id');
     *
     * dump($hashmap);
     *   // 输出结果为
     *   // array(
     *   //   1 => array('id' => 1, 'value' => '1-1'),
     *   //   2 => array('id' => 2, 'value' => '2-1'),
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $keyField 按照什么键的值进行转换
     * @param string $valueField 对应的键值
     *
     * @return array 转换后的 HashMap 样式数组
     */
    public static function toHashmap($arr, $keyField, $valueField = NULL)
    {
        $ret = array();
        if ($valueField)
        {
            foreach ($arr as $row)
            {
                $ret[$row[$keyField]] = $row[$valueField];
            }
        }
        else
        {
            foreach ($arr as $row)
            {
                $ret[$row[$keyField]] = $row;
            }
        }
        return $ret;
    }

    /**
     * 将一个二维数组按照指定字段的值分组
     *
     * 用法：
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $keyField 作为分组依据的键名
     *
     * @return array 分组后的结果
     */
    public static function groupBy($arr, $keyField)
    {
        $ret = array();
        foreach ($arr as $row)
        {
            $key = $row[$keyField];
            $ret[$key][] = $row;
        }
        return $ret;
    }

    /**
     * 将一个平面的二维数组按照指定的字段转换为树状结构
     *
     *
     * 如果要获得任意节点为根的子树，可以使用 $refs 参数：
     * @code php
     * $refs = null;
     * $tree = ArrayHelper::tree($rows, 'id', 'parent', 'nodes', $refs);
     *
     * // 输出 id 为 3 的节点及其所有子节点
     * $id = 3;
     * dump($refs[$id]);
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $keyNodeId 节点ID字段名
     * @param string $keyParentId 节点父ID字段名
     * @param string $keyChildrens 保存子节点的字段名
     * @param boolean $refs 是否在返回结果中包含节点引用
     *
     * return array 树形结构的数组
     */
    public static function toTree($arr, $keyNodeId, $keyParentId = 'parent_id', $keyChildrens = 'childrens', & $refs = NULL)
    {
        $refs = array();
        foreach ($arr as $offset => $row)
        {
            $arr[$offset][$keyChildrens] = array();
            $refs[$row[$keyNodeId]] =& $arr[$offset];
        }

        $tree = array();
        foreach ($arr as $offset => $row)
        {
            $parentId = $row[$keyParentId];
            if ($parentId)
            {
                if (!isset($refs[$parentId]))
                {
                    $tree[] =& $arr[$offset];
                    continue;
                }
                $parent =& $refs[$parentId];
                $parent[$keyChildrens][] =& $arr[$offset];
            }
            else
            {
                $tree[] =& $arr[$offset];
            }
        }
        return $tree;
    }

    /**
     * 将树形数组展开为平面的数组
     *
     * 这个方法是 tree() 方法的逆向操作。
     *
     * @param array $tree 树形数组
     * @param string $keyChildrens 包含子节点的键名
     *
     * @return array 展开后的数组
     */
    public static function treeToArray($tree, $keyChildrens = 'childrens')
    {
        $ret = array();
        if (isset($tree[$keyChildrens]) && is_array($tree[$keyChildrens]))
        {
            foreach ($tree[$keyChildrens] as $child)
            {
                $ret = array_merge($ret, self::treeToArray($child, $keyChildrens));
            }
            unset($ret[$keyChildrens]);
            $ret[] = $tree;
        }
        else
        {
            $ret[] = $tree;
        }
        return $ret;
    }

    /**
     * 根据指定的键对数组排序
     *
     * @endcode
     *
     * @param array $array 要排序的数组
     * @param string $keyname 排序的键
     * @param int $dir 排序方向
     *
     * @return array 排序后的数组
     */
    public static function sortByCol($array, $keyname, $dir = SORT_ASC)
    {
        return self::sortByMultiCols($array, array($keyname => $dir));
    }

    /**
     * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
     *
     * 用法：
     * @code php
     * $rows = ArrayHelper::sortByMultiCols($rows, array(
     *     'parent' => SORT_ASC,
     *     'name' => SORT_DESC,
     * ));
     * @endcode
     *
     * @param array $rowset 要排序的数组
     * @param array $args 排序的键
     *
     * @return array 排序后的数组
     */
    public static function sortByMultiCols($rowset, $args)
    {
        $sortArray = array();
        $sortRule = '';
        foreach ($args as $sortField => $sortDir)
        {
            foreach ($rowset as $offset => $row)
            {
                $sortArray[$sortField][$offset] = $row[$sortField];
            }
            $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
        }
        if (empty($sortArray) || empty($sortRule)) {
            return $rowset;
        }
        eval('array_multisort(' . $sortRule . '$rowset);');
        return $rowset;
    }


    /*
     * 判断语句是否符合安全问题
     * @param $sql   sql语句，仅限sql查询语句
     */

    protected static function checkSql($sql) {
        $str = array('update', 'insert', 'delete', ';', '\\', '\'', '"', );
        for ($i = 0; $i <= count($str); $i++) {
            $strbool = strpos($sql, $str[$i]);
            if ($strbool) {
                return false;
            }
        }
        return false;
    }

    /**
     * 导出数据为excel表格
     *@param $sql   一条sql查询语句
     *@param $data    一个二维数组,结构如同从数据库查出来的数组
     *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
     *@param $filename 下载的文件名
     *@Edwin
     *$arr = $stu -> select();
     *exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
     */
    public static function exportexcel($sql,$title,$filename='report')
    {
        //判断语句返回属性

        if($result=self::checkSql($sql))
        {
            echo "含有非法字符,本语句只能用于查询";
            return false;
        }else{
            $data = queryMysql($sql);
            if($data)
            {
                header("Content-type:application/octet-stream");
                header("Accept-Ranges:bytes");
                header("Content-type:application/vnd.ms-excel");
                header("Content-Disposition:attachment;filename=".$filename.".xls");
                header("Pragma: no-cache");
                header("Expires: 0");
                //导出xls 开始
                if (!empty($title)){
                    foreach ($title as $k => $v) {
                        $title[$k]=iconv("UTF-8", "GB2312",$v);
                    }
                    $title= implode("\t", $title);
                    echo "$title\n";
                }
                if (!empty($data))
                {
                    foreach($data as $key=>$val){
                        foreach ($val as $ck => $cv) {
                            $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
                        }
                        $data[$key]=implode("\t", $data[$key]);
                    }
                    echo implode("\n",$data);
                }

            }else{
                echo  "sql执行失败，请检查sql";
                }
            }
    }


    /*
*差集，二维数组内部判断
*/

    function arrayDiff($arr)
    {
        if (count($arr)>0)
        {
            foreach($arr as $key=>$val)
            {
                if ($key==0)//第一个先取出来
                {
                    $tmp_arr = $val;
                }else
                {
                    $tmp_arr = array_diff($tmp_arr,$val);
                }
            }
        }
        return $tmp_arr;
    }

    /*
    *交集，二维数组内部判断
    */
    function arrayIntersect($arr)
    {
        if (count($arr)>0)
        {
            foreach($arr as $key=>$val)
            {
                if ($key==0)//第一个先取出来
                {
                    $tmp_arr = $val;
                }else
                {
                    $tmp_arr = array_intersect_assoc($tmp_arr,$val);
                }
            }
        }
        return $tmp_arr;
    }

    /*
    *并集，二维数组内部判断
    */
    function arrayMerge($arr)
    {
        if (count($arr)>0)
        {
            foreach($arr as $key=>$val)
            {
                if ($key==0)//第一个先取出来
                {
                    $tmp_arr = $val;
                }else
                {
                    $tmp_arr = array_merge($tmp_arr,$val);
                }
            }
        }
        return $tmp_arr;
    }

    /*
    *差集,两个数组比较
    */
    function arrayDiff2($arr1,$arr2,$arr3)
    {
        foreach ($arr1 as $key => $value)
        {
            if(!in_array($value,$arr2)){
                $arr3[]=$value;
            }
        }
        return $arr3;
    }
}
