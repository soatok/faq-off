function checkPassword() {
    let result = zxcvbn(
        $("#passphrase").val(),
        [
            $("#username").val(),
            $("#email").val()
        ]
    );
    let pwHint = $("#password-hint");
    if (result.score < 3) {
        if (result.feedback.warning) {
            pwHint.html(`<strong class="error">${result.feedback.warning}</strong>`);
        } else {
            pwHint.html(`<strong class="error">${result.feedback.suggestions}</strong>`);
        }
    } else if (result.score === 3) {
        if (result.feedback.suggestions.length > 0) {
            pwHint.html(result.feedback.suggestions);
        } else {
            pwHint.html('Acceptable passphrase');
        }
    } else {
        pwHint.html('<span class="success">Good passphrase</span>');
    }
    return result;
}

$(document).ready(function() {
    $("#passphrase").on('change', checkPassword);
});
