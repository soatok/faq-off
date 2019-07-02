
function entryPreview()
{
    $.post('/manage/ajax/preview', {
        "markdown": $("#entry-contents").val()
    }, function (res) {
        if (res['status'] !== 'SUCCESS') {
            console.log(res);
            return;
        }
        $("#contents-preview").html(res['preview']);
    });
}

$(document).ready(function() {
    $("#entry-contents").on('change', entryPreview);
});
