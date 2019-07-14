
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

function addContributor()
{
    $("#admin-contributors-list")
        .append(
            '<li><input class="admin-contrib" name="contributors[]" size="1" type="text" /></li>'
        );
    $('.admin-contrib').on('keydown', adminContribOnChange)
}

function adminContribOnChange()
{
    let el = $(this);
    let size =  el.val().length + 1;
    el.attr('size', size < 10 ? size : 10);
}

$(document).ready(function() {
    $("#author-bio").on('change', entryPreview);
    $("#add-contributor").on('click', addContributor);
    entryPreview();
    $('.admin-contrib').on('keydown', adminContribOnChange)
});
