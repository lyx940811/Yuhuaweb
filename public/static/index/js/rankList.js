/*
 * @Author: Benfei Cao 
 * @Date: 2017-12-14 10:11:17 
 * @Last Modified by:   Benfei Cao 
 * @Last Modified time: 2017-12-14 10:11:17 
 */

$(function () {
    $(".rankListWarp .list2").eq(0).show();
    $(".rankBtn li").on("click",function () {
        var index = $(this).index();
        $(this).addClass("check").siblings().removeClass("check");
        $(".rankListWarp .list2").eq(index).show().siblings().hide();
    })
})
