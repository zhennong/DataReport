//右侧iframe框架高度自适应
$(function(){
    var height = $(".tabs-panels").innerHeight();
    height = height - 20;
    $("#mainFrame").css("height",height);
})