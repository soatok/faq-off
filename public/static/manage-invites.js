function createInvite() {
    $.get('/manage/invite/create', function (res) {
        let ic = $("#invite-codes");
        let url = ic.data('baseurl');
        ic.prepend(
            "<li class='invite-code'>" +
                url + "/" + res['code'].replace(/[^A-Za-z0-9]/, '') +
            "</li>"
        );
    });
}

$(document).ready(function () {
    $("#create-invite-form").submit(function (e) {
        e.preventDefault();
        createInvite();
        return false;
    });
});
