/*
 * @Author: Benfei Cao 
 * @Date: 2017-12-19 13:45:15 
 * @Last Modified by: Benfei Cao
 * @Last Modified time: 2017-12-25 16:55:58
 */
    //登录URL
var loginUrl = "http://66.112.221.116/index/login/login",
    //注册URL
    registerUrl = 'http://66.112.221.116/index/login/register';
$(function () {
    $(".login-main").hide();
    $(".login-main:eq(0)").show();

    $('.logon-tab a').on("click",function () {
        var index = $(this).index();
        loginOrRegister(index);
    });
    
    //登录+验证
    
    $("#login-form")
    .bootstrapValidator({
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            username: {
                validators: {
                    notEmpty: {
                        message: '请输入用户名'
                    }, 
                    stringLength: {
                        min: 3,
                        max: 18,
                        message: '中、英文均可，最长18个英文或9个汉字'
                    }
                }
            },
            password: {
                validators: {
                    stringLength: {
                        min: 5,
                        max: 20,
                        message: '5-20位英文、数字、符号，区分大小写'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9_\.]+$/,
                        message: '密码只能由字母，数字，点和下划线组成'
                    },
                    notEmpty: {
                        message: '请输入密码'
                    }
                }
            }
        }
    })
    .on('success.form.bv', function(e) {
        // 阻止表单提交
        e.preventDefault();
        //提交表单
        var loginData = {
            "token": 123456,
            "username": $.trim($("#login_username").val()),
            "password": $.trim($("#login_password").val())
        };
        $.ajax({
            url:loginUrl,
            type:'POST',
            data:loginData,
            crossDomain: true,
            async:true,
            timeout:5000,
            dataType:'json',
            // beforeSend: function (request){             
            //     request.setRequestHeader("token", 123456);  
            //     // 请求发起前在头部附加token
            // },
            success:function(data,textStatus){
                var login_message ={
                    "username":data.data.username,
                    "title":data.data.title
                };
                switch (data.code)   
                {   
                    case 0:
                        store.save("login_message",login_message);
                        setTimeout(function(){
                            window.location.href='./index.html';
                        },2000);
                        break;   
                    case 110:
                        $(".help-block").html('该用户（邮箱）还没有注册');
                        break;   
                    case 140:   
                        $(".help-block").html('密码错误');
                        // 用户名或者email已存在
                        $('#register_nickname-error,#register_email-error').html('邮箱或用户名已存在!');
                        $('#register_nickname-error').show();
                        break;   
                    case 150:   
                        $(".help-block").html('该用户类型不是教师或者学员'); 
                        break;
                    case 160:   
                        $(".help-block").html('该用户账号已经被锁定'); 
                        break;
                    case 900:   
                        $(".help-block").html("token验证错误"); 
                        break;
                };  
            },
            error:function(xhr,textStatus){
            }
        });
    }); 

    //注册+验证
    $('#register-form')
    .bootstrapValidator({
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            username: {
                validators: {
                    notEmpty: {
                        message: '请输入用户名'
                    }, 
                    stringLength: {
                        min: 3,
                        max: 18,
                        message: '中、英文均可，最长18个英文或9个汉字'
                    },
                    regexp: {
                        regexp: /^[\u4e00-\u9fa5]{3,9}$|^[\dA-Za-z_]{1,18}$/,
                        message: '用户名只能由字母，数字，汉字组成'
                    }
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: '邮箱地址不能为空'
                    },
                    emailAddress: {
                        message: '请输入有效的邮箱地址'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/,
                        message: '邮箱格式有误'
                    }
                }
            },
            password: {
                validators: {
                    stringLength: {
                        min: 5,
                        max: 20,
                        message: '5-20位英文、数字、符号，区分大小写'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9_\.]+$/,
                        message: '密码只能由字母，数字，点和下划线组成'
                    },
                    notEmpty: {
                        message: '请输入密码'
                    }
                }
            },
            gender: {
                validators: {
                    notEmpty: {
                        message: '勾选同意此服务协议，才能继续注册'
                    }
                }
            }
        }
    })
    .on('success.form.bv', function(e) {
        // 阻止表单提交
        e.preventDefault();
        //提交表单
        var registerData={
            "token": 123456,
            "email":$.trim($("#register_email").val()),
            "password": $.trim($("#register_password").val()),
            "username": $.trim($("#register_nickname").val())
        };
        $.ajax({
            url:registerUrl,
            type:'POST',
            data:registerData,
            crossDomain: true,
            async:true,
            timeout:5000,
            dataType:'json',
            // headers: {
            //     'Authorization': 'Bearer ' + token // 请求发起前在头部附加token

            // dataType:'json',beforeSend: function (request){             
            //     request.setRequestHeader("token", 123456);  
            //     // 请求发起前在头部附加token
            // },
            success:function(data,textStatus){
                var login_message ={
                    "username":data.data.username,
                    "title":data.data.title
                };
                switch (data.code)   
                {   
                    case 0:
                        store.save("login_message",login_message);
                        setTimeout(function(){
                            window.location.href='./index.html';
                        },2000);
                        break;   
                    case 100:
                        $(".help-block").html('添加账号到数据库错误');
                        break;   
                    case 120:   
                        // 用户名或者email已存在
                        $('#register_nickname-error,#register_email-error').html('邮箱或用户名已存在!');
                        $('#register_nickname-error').show();
                        break;   
                    case 130:   
                        $(".help-block").html(data.message); 
                        break;
                    case 900:   
                        $(".help-block").html("token验证错误"); 
                        console.log('token验证错误');   
                        break;
                };
                //提示信息控制隐藏
                $('#register_nickname,#register_email').on('focus',function(){
                    $('#register_nickname-error').hide();
                })   
            },
            error:function(xhr,textStatus){
            }
        })
    }); 
    function loginOrRegister(index) {
        $('.logon-tab a').eq(index).addClass("active").siblings().removeClass("active");
        $(".login-main").eq(index).show().siblings(".login-main").hide();
    }
});
