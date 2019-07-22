let requestPending = false;

function preview()
{
    requestPending = true;
    $.post('/manage/ajax/preview', {
        "markdown": $("#notice-body").val()
    }, function (res) {
        requestPending = false;
        if (res['status'] !== 'SUCCESS') {
            console.log(res);
            return;
        }
        $("#contents-preview").html(res['preview']);
    });
}

function previewKeyUp()
{
    if (requestPending) {
        return;
    }
    requestPending = true;
    $.post('/manage/ajax/preview', {
        "markdown": $("#notice-body").val()
    }, function (res) {
        requestPending = false;
        if (res['status'] !== 'SUCCESS') {
            console.log(res);
            return;
        }
        $("#contents-preview").html(res['preview']);
    });
}

$(document).ready(function() {
    let el = $("#notice-body");
    el.on('change', preview);
    el.on('keyup', previewKeyUp);
    preview();
});
