//增加String对象的去除空格操作
String.prototype.trim = function() {
　　return this.replace(/(^\s*)|(\s*$)/g, "");
}

window.onload = function() {
    /* 跟踪鼠标点击界面的代码
    document.onclick = function(ev) {
        var _event = ev||event;
        alert(_event.clientY+"px,"+_event.clientX+"px");
    }
    */
    /* 监听退出页面、刷新页面的代码
    window.onbeforeunload = onbeforeunload_handler;
    window.onunload = onunload_handler;
    function onbeforeunload_handler() {
        var warning = "确认退出?";
        return warning;
    }

    function onunload_handler() {
        var warning = "谢谢光临";
        alert(warning);
    }
    */
}
