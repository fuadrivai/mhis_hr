

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

function getQueryString() {
    location.queryString = {};
    location.search.substring(1).split("&").forEach(function (pair) {
        if (pair === "") return;
        var parts = pair.split("=");
        location.queryString[parts[0]] = parts[1] && decodeURIComponent(parts[1].replace(/\+/g, " "));
    });
    return location.queryString;
}

function navigate(query) {
    let params = "?";
    for (const key in query) {
        params += `${key}=${query[key]}&`
    }
    let loc = window.location;
    params = params.replace(/.$/, "")
    window.location = `${loc.origin}${loc.pathname}${params}`
}