$(function () {
    $(".rankListWarp .list2").eq(0).show();
    $(".rankBtn li").on("click",function () {
        var index = $(this).index();
        $(this).addClass("check").siblings().removeClass("check");
        $(".rankListWarp .list2").eq(index).show().siblings().hide();
    })
})
