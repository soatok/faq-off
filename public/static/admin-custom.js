let lastDir = '';
function escape_attr(inVal) {
    return inVal
        .split('"').join('&#034;')
        .split("'").join('&#039;')
        .split("`").join('&#060;');
}

function makeDir() {
    let csrf = $("#file-manager-wrapper").data('csrf');
    let dirname = $("#file-mkdir-text").val();
    $.post('/admin/ajax/mkdir', {"parent": lastDir, "dirname": dirname, "csrf-protect": csrf}, function (result) {
        $("#file-mkdir-text").val('');
        getFilesInDirectory(lastDir);
    });
}

function changeDir() {
    let dir = $(this).data('subdir');
    lastDir = dir;
    getFilesInDirectory(dir);
}

function getFilesInDirectory(dir) {
    let csrf = $("#file-manager-wrapper").data('csrf');
    $.post("/admin/ajax/ls", {"directory": dir, "csrf-protect": csrf}, function (result) {
        let html = '';
        let subpath = '';
        if (dir !== '' && dir !== '/') {
            let pieces = dir.split('/');
            pieces.pop();
            subpath = escape_attr(pieces.join('/'));
            html = `<tr>
                <td><a class="file-manager-subdir-link" href="#" data-subdir="${subpath}">(Parent Directory)</a></td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td></td>
            </tr>\n`;
        }
        let options = "";
        let file;
        for (let i = 0; i < result.files.length; i++) {
            if (result.status !== 'OK') {
                alert(result.error);
                return;
            }
            file = result.files[i];
            subpath = escape_attr(dir + "/" + file.name);
            if (file.dir) {
                html += `<tr>
                    <td><a class="file-manager-subdir-link" href="#" data-subdir="${subpath}">${file['name']}</a></td>
                    <td>--</td>
                    <td>${file['created']}</td>
                    <td>${file['modified']}</td>
                    <td></td>
                </tr>\n`;
            } else {
                options = `<a class="editlink" href="/admin/custom/edit?file=${encodeURI(subpath)}"><i class="fas fa-pencil-alt"></i> Edit</a>`;
                html += `<tr>
                    <td>${file['name']}</td>
                    <td>${file['size']}</td>
                    <td>${file['created']}</td>
                    <td>${file['modified']}</td>
                    <td>${options}</td>
                </tr>\n`;
            }
        }
        $("#file-listing").html(html);
        $("#current-dir").html(escape_attr(dir));
        $("#create-file-link").attr('href', '/admin/custom/create?dir=' + encodeURI(dir));
        $(".file-manager-subdir-link").on('click', changeDir);
        $("#file-mkdir-button").on('click', makeDir);
    });
}

$(document).ready(function () {
    getFilesInDirectory('');
    $(".file-manager-subdir-link").on('click', changeDir);
    $("#file-mkdir-button").on('click', makeDir);
});
