$("#entry-follow-ups").select2({
    "ajax": {
        "url": "/manage/ajax/entry-search",
        "dataType": "json"
    },
    "allowClear": true,
    "placeholder": "Attach additional entries to this one"
});
$("#entry-follow-ups").on("select2:unselect", function(e) {
    $("#entry-follow-ups").find('option').not(':selected').remove();
});

$(document).ready(function() {
    entryPreview();
});
