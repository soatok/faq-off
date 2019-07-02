
/*
let elEntrySearch = $("#entry-attach-to-search");
function searchEntries() {
    let collectionId = $("#entry-contents").data("collectionid");
    $.post(
        "/manage/ajax/entry-search",
        {
            "collection": collectionId,
            "query": elEntrySearch
        }, function (res) {

        }
    );
}

$(document).ready(function() {
    elEntrySearch.keyup(searchEntries);
    elEntrySearch.on('change', searchEntries);
});

*/

function formatResult(blob) {
    let markup = "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__meta'>" +
            "<div class='select2-result-repository__title'>" + blob.title + "</div>" +
        "</div>" +
    "</div>";
}

function formatResultSelected(blob) {

}

$("#entry-attach-to-search").select2({
    "ajax": "/manage/ajax/entry-search",
    "allowClear": true,
    "processResults": function (response) {
        return {
            results: response.data
        };
    },
    "templateResult": formatResult,
    "templateSelection": formatResultSelected
});
