
function entryPreview()
{
    $.post('/manage/ajax/preview', {
        "markdown": $("#author-bio").val()
    }, function (res) {
        if (res['status'] !== 'SUCCESS') {
            console.log(res);
            return;
        }
        $("#contents-preview").html(res['preview']);
    });
}

$(document).ready(function() {
    $("#author-bio").on('change', entryPreview);
    entryPreview();
});
