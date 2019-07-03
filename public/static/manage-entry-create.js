$("#entry-attach-to").select2({
    "ajax": {
        "url": "/manage/ajax/entry-search",
        "dataType": "json"
    },
    "allowClear": true,
    "placeholder": "Attach this new entry as a follow-up to other questions"
});
