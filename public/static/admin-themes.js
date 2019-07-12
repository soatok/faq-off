$(document).ready(function () {
    $("#theme-add-css-file").on('click', function () {
        let list = $("#theme-css-files-list");
        let count = list.data('count');
        list.append(
            '<li id="theme-css-' + count + '">\n' +
                '<input\n' +
                    'class="form-control"\n' +
                    'name="css_files[]"\n' +
                    'type="text"\n' +
                '/>\n' +
            '</li>'
        );
        list.data('count', count + 1);
        list.attr('data-count', count + 1);
    });
    $("#theme-add-js-file").on('click', function () {
        let list = $("#theme-js-files-list");
        let count = list.data('count');
        list.append(
            '<li id="theme-js-' + count + '">\n' +
                '<input\n' +
                    'class="form-control"\n' +
                    'name="js_files[]"\n' +
                    'type="text"\n' +
                '/>\n' +
            '</li>'
        );
        list.data('count', count + 1);
        list.attr('data-count', count + 1);
    });
});
