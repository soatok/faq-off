
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

function addContributor(author, id)
{
    $.post('/manage/ajax/add-contributor', {
        "csrf-protect": $('input[name="csrf-protect"]').val(),
        "author": author,
        "id": id
    }, function(res) {
        if (typeof(res.added) === 'undefined') {
            res.added = false;
        }
        if (res.added) {
            $("#manage-author-contributors").append(
                `<li id="contributor-${id}">
                    <button class="btn btn-verydark delete-contributor" data-id="${id}">Remove</button> ${id}
                </li>`
            );
            $("#add-contributor-text").val("");
            addDeleteEvent();
        } else {
            alert(res.error);
        }
    }).fail(function (res) {
        alert(res.responseJSON.error);
    });
}

function removeContributor(author, id)
{
    $.post('/manage/ajax/remove-contributor', {
        "csrf-protect": $('input[name="csrf-protect"]').val(),
        "author": author,
        "id": id
    }, function(res) {
        if (typeof(res.removed) === 'undefined') {
            res.removed = false;
        }
        if (res.removed) {
            $("#contributor-" + id).remove();
        } else {
            alert(res.error);
        }
    }).fail(function (res) {
        alert(res.responseJSON.error);
    });
}

function addDeleteEvent() {
    $(".delete-contributor").on('click', function (e) {
        let author = $("#contributors-form").data('id');
        let id = $(this).data('id');
        return removeContributor(author, id);
    });
}

$(document).ready(function() {
    $("#author-bio").on('change', entryPreview);
    entryPreview();

    $("#add-contributor-button").on('click', function (e) {
        let author = $("#contributors-form").data('id');
        let id = $("#add-contributor-text").val();
        return addContributor(author, id);
    });
    addDeleteEvent();
});
