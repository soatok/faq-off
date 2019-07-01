
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
