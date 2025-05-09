

function ajax(data, url, method, callback, callbackError) {
    $.ajax({
        url: url,
        data: data,
        type: method,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (json, text) {
            json = json;
            callback(json);
        },
        error: function (err) {
            callbackError == null ?
                toastr.error(err?.responseJSON?.message ?? "Tidak Dapat Mengakses Server")
                : callbackError(err);
        }
    });
}

function reloadJsonDataTable(dtable, json) {
    dtable.clear().draw();
    dtable.rows.add(json).draw();
}