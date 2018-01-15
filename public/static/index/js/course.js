/*
* @Author: Benfei Cao 
* @Date: 2018-01-10 16:40:33 
 * @Last Modified by: Benfei Cao
 * @Last Modified time: 2018-01-11 10:37:05
*/
//初始化编辑器
var quill = new Quill('#editor-container',{
    modules: {
        toolbar: [
        ['bold', 'italic', 'underline'],
        ['image', 'code-block']
        ]
    },
    placeholder: '输入您的笔记...',
    theme: 'snow'  // or 'bubble'
});
var quill = new Quill('#editor-container-two',{
    modules: {
        toolbar: [
        ['bold', 'italic', 'underline'],
        ['image', 'code-block']
        ]
    },
    placeholder: '我要提问...',
    theme: 'snow'  // or 'bubble'
});

$(function(){
    //启动bootstrap提示信息插件
    $('[data-toggle="popover"]').popover();
    //点击按钮控制
    $("#dashboard-toolbar-nav>li").on("click",function(){
        var $dashboardSidebar = $("#dashboard-sidebar"),
            $dashboardContent = $("#dashboard-content");
        var _this = this;

        $($dashboardSidebar).animate({
                right:"0px"
        });
        $($dashboardContent).animate({
                right:"395px"
        });
        if($(_this).hasClass("active")){
            $($dashboardSidebar).animate({
                right:"-360px"
            },function(){
                $(_this).removeClass("active");
            });
            $($dashboardContent).animate({
                right:"35px"
            });
        }
    });
    $("#learn-btn").on("click",function(){
        $(this).addClass('active');
    })
})