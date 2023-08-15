/**
 * 系统登录
 * @author 牧羊人
 * @since 2020/7/4
 */
layui.use(['layer', 'form', 'index'], function () {
    var $ = layui.jquery;
    var layer = layui.layer;
    var index = layui.index;
    var form = layui.form;
    $('.login-wrapper').removeClass('layui-hide');

    // 登录事件
    form.on('submit(loginSubmit)', function (data) {
        // 设置按钮文字“登录中...”及禁止点击状态
        $(data.elem).attr('disabled', true).text('登录中...');

        // 网络请求
        var loadIndex = layer.load(2);
        $.post("Login/login", data.field, function(res){
            // 关闭加载
            layer.close(loadIndex);
            if (res.success) {
                // 清除Tab记忆
                index.clearTabCache();
                // 提示登录成功
                layer.msg('登录成功', {
                    icon: 1,
                    time: 1500
                });
                // 延迟2秒
                setTimeout(function () {
                    // 跳转后台首页
                    window.location.href = "/Index";
                }, 2000);

                return false;
            }else{
                // 延迟3秒恢复可登录状态
                setTimeout(function () {
                    // 设置按钮状态为“登陆”
                    var login_text = $(data.elem).text().replace('中...', '');
                    // 设置按钮为可点击状态
                    $(data.elem).text(login_text).removeAttr('disabled');
                }, 1000);

                // 错误提示
                layer.msg(res.msg, {
                    icon: 2,
                    time: 1000
                });
            }
        }, 'json');
        return false;
    });
});