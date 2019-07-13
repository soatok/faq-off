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

function updateTwoFactorQR() {
    let obj = $("#two-factor-qr");
    let secret = obj.data('secret');
    let username = $("#username").val();
    let uri = 'otpauth://totp/' +
        encodeURI(window.location.hostname) + ':' + encodeURI(username) + '?' +
        'algorithm=SHA1' + '&' +
        'secret=' + secret + '&' +
        'digits=6' + '&' +
        'period=30' + '&' +
        'issuer=' + encodeURI(window.location.hostname);
    obj.html("");
    obj.qrcode(uri);
    $("#two-factor-uri").html(uri);
}

$(document).ready(function() {
    $("#passphrase").on('change', checkPassword);
    $("#username").on('change', updateTwoFactorQR);
    updateTwoFactorQR();
});
