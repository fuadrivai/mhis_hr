

$(document).ready(function(){
    $('.select2').select2({})
	$('.select2').on('select2:open', function () {
		$('input.select2-search__field')[0].focus();
	})

    $('.number2').on('keyup', function(event) {
		if (event.which >= 37 && event.which <= 40) return;
		$(this).val(function(index, value) {
			return value
				.replace(/\D/g, "")
				.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		});
	});

    $('.date-picker').datepicker({
        format:"d MM yyyy",
        orientation: "top auto",
		autoclose: true,
		todayHighlight: true,
		language: 'id',
		clearBtn:true
    })

	$('.month-picker').datepicker({
		format:"MM yyyy",
		orientation: "top auto",
		autoclose: true,
		startView: "months",
		minViewMode: "months",
		language: 'id',
		clearBtn:true
	});

    $('.year-picker').datepicker({
		format: "yyyy",
		orientation: "top auto",
		autoclose: true,
		viewMode: "years",
		minViewMode: "years",
		language: 'id'
	});

    $('.time-picker').timepicker({
        timeFormat: 'HH:mm',
        interval: 30,
        minTime: '00:00',
        maxTime: '23:59',
        dynamic: true,
        dropdown: true,
        scrollbar: false
    });

})


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
function diffTime(start,end) {
    let startTime = moment(start, 'HH:mm');
    let endTime = moment(end, 'HH:mm');

    let duration = moment.duration(endTime.diff(startTime));

    let hours = duration.hours();
    let minutes = duration.minutes();

    return `${hours}h ${minutes}m`
}