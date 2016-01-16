//右侧iframe框架高度自适应
$(function(){
    var height = $(".tabs-panels").innerHeight();
    height = height - 20;
    $("#mainFrame").css("height",height);
})

// dataTables 汉化
var dataTables_zh_CN = {
    "sProcessing": "处理中...",
    "sLengthMenu": "显示 _MENU_ 项结果",
    "sZeroRecords": "没有匹配结果",
    "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
    "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
    "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
    "sInfoPostFix": "",
    "sSearch": "搜索:",
    "sUrl": "",
    "sEmptyTable": "表中数据为空",
    "sLoadingRecords": "载入中...",
    "sInfoThousands": ",",
    "oPaginate": {
        "sFirst": "首页",
        "sPrevious": "上页",
        "sNext": "下页",
        "sLast": "末页"
    },
    "oAria": {
        "sSortAscending": ": 以升序排列此列",
        "sSortDescending": ": 以降序排列此列"
    }
}

//退出登录
function logout(){
    layer.confirm('你确定要退出吗？', {icon: 3}, function(index){
        layer.close(index);
        window.location.href="{:U('Public/logout')}";
    });
}

//修改密码
function update_pwd(){
    layer.open({
        type: 2,
        closeBtn: 2,
        area: ['450px', '280px'],
        title: '修改密码',
        content: "{:U('Index/edit_pwd')}"
    });
}

//清除缓存
function clear_cache(){
    layer.open({
        type: 2,
        closeBtn: 2,
        area: ['520px', '160px'],
        shadeClose: true,
        title: '清除缓存（ 点击遮罩可以关闭窗口哦！ ）',
        content: "{:U('Index/clear_cache')}"
    });
}