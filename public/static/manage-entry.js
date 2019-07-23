let requestPending = false;

function entryPreview()
{
    requestPending = true;
    $.post('/manage/ajax/preview', {
        "markdown": $("#entry-contents").val()
    }, function (res) {
        requestPending = false;
        if (res['status'] !== 'SUCCESS') {
            console.log(res);
            return;
        }
        $("#contents-preview").html(res['preview']);
    });
}

function entryKeyUp() {
    if (requestPending) {
        return;
    }
    entryPreview();
}

$(document).ready(function() {
    let el = $("#entry-contents");
    el.on('change',  entryPreview);
    el.on('keyup', entryKeyUp);
});
