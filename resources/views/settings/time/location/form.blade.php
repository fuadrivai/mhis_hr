@extends('layouts.main-layout')
@section('content-class')
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/side-drawer-modal-bootstrap/bootstrap-side-modals.css" rel="stylesheet">
    <style>
        #map {
            height: 300px;
            width: 100%;
        }
    </style>
@endsection

@section('content-child')
    <div class="col-md-12 col-sm-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label for="name">Location Setting Name</label>
                            <input required type="text" id="name"
                                value="{{ isset($location) ? $location->name : '' }}" class="form-control" name="name" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="">GPS Location</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="need_location" id="flexible"
                                    value="false">
                                <label class="form-check-label" for="flexible">
                                    Flexible
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="need_location" id="set-location"
                                    value="false">
                                <label class="form-check-label" for="set-location">
                                    Set Location
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="page-title">
            <div class="title_left">
                <h3>Location</h3>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-12">
                        <button data-toggle="modal" data-target="#right-modal-location" type="button"
                            class="btn btn-success btn-sm text-white btn-add-user"><i class="fa fa-map-marker"></i>
                            Add Location</button>
                    </div>
                    <table id="tbl-location" class="table table-striped table-bordered table-sm" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Location Name</th>
                                <th>Address</th>
                                <th>Radius</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="page-title">
            <div class="title_left">
                <h3>Assign Employee</h3>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="x_panel">
            <div class="x_content">
                <div class="row">
                    <div class="col-12">
                        <button data-toggle="modal" data-target="#right-modal-user" type="button"
                            class="btn btn-success btn-sm text-white btn-add-user"><i class="fa fa-user"></i>
                            Add Employee</button>
                    </div>
                    <table id="tbl-employee-location" class="table table-striped table-bordered table-sm"
                        style="width: 100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Branch</th>
                                <th>Level</th>
                                <th>Organization</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-right fade" id="right-modal-location" tabindex="-1" role="dialog"
        aria-labelledby="right_modal_lg">
        <div class="modal-dialog modal-lg" role="document">

            <div class="modal-content">
                <form id="form-location" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Location</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 pb-3">
                                <div id="map"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="name-detail">Name</label>
                                    <input required type="text" id="name-detail"class="form-control"
                                        name="name_detail" />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="radius">Radius (meters)</label>
                                    <input required type="text" id="radius"class="form-control" name="radius" />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input required type="text" id="latitude"class="form-control" name="latitude" />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input required type="text" id="longitude"class="form-control" name="longitude" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address">Adress</label>
                                    <textarea class="form-control" name="address" id="address" cols="30" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" name="description" id="description" cols="30" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-fixed">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal modal-right fade" id="right-modal-user" tabindex="-1" role="dialog"
        aria-labelledby="right_modal_lg">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Employee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table id="tbl-employee" class="table table-striped table-bordered table-sm"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="checkAll" class="checkAll"></th>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th>Level</th>
                                        <th>Organization</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-fixed">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content-script')
    <!-- prettier-ignore -->
    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
        (g => {
            let h, a, k, p = "The Google Maps JavaScript API",
                c = "google",
                l = "importLibrary",
                q = "__ib__",
                m = document,
                b = window;
            b = b[c] || (b[c] = {});
            let d = b.maps || (b.maps = {}),
                r = new Set,
                e = new URLSearchParams,
                u = () => h || (h = new Promise(async (f, n) => {
                    await (a = m.createElement("script"));
                    e.set("libraries", [...r] + "");
                    for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                    e.set("callback", c + ".maps." + q);
                    a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                    d[q] = f;
                    a.onerror = () => h = n(Error(p + " could not load."));
                    a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                    m.head.append(a)
                }));
            d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() =>
                d[l](f, ...n))
        })
        ({
            key: "AIzaSyAhPd4p9-KRS06GMC0pzoV_vbd7q5_kGuk",
            v: "weekly"
        });
    </script>

    <script>
        let objLocation = {
            details: [],
            employees: []
        };
        let selectedEmployess = [];
        let dataId = "<?= isset($location) ? $location->id : null ?>";
        let pinLocation = {
            id: "<?= isset($location) ? $location->id : null ?>",
            employees: [],
        };
        let map;
        $(document).ready(function() {
            tblLocation = $("#tbl-location").DataTable({
                searching: false,
                paging: false,
                lengthChange: false,
                ordering: false,
                data: objLocation.details,
                columns: [{
                        data: "name",
                        defaultContent: "--"
                    },
                    {
                        data: "address",
                        defaultContent: "--"
                    },
                    {
                        data: "radius",
                        defaultContent: "--"
                    },
                    {
                        data: 'id',
                        className: "text-center",
                        mRender: function(data, type, full) {
                            return `<a title="Edit" class="btn btn-sm btn-danger text-white btn-delete-location"><i class="fa fa-trash"></i></a>`
                        }
                    }
                ]
            })
            initMap();

            $('#form-location').on('submit', function(e) {
                e.preventDefault();
                let loc = {
                    name: $('#name-detail').val(),
                    radius: $('#radius').val(),
                    latitude: $('#latitude').val(),
                    longitude: $('#longitude').val(),
                    description: $('#description').val(),
                    address: $('#address').val(),
                }
                objLocation.details.push(loc);
                reloadJsonDataTable(tblLocation, objLocation.details);
                $("#right-modal-location").modal('hide')
            })

            $("#tbl-location").on('click', '.btn-delete-location', function() {
                let data = tblLocation.row($(this).parents('tr')).index();
                objLocation.details.splice(data, 1);
                reloadJsonDataTable(tblLocation, objLocation.details);
            })

            $('#right-modal-user').on('hidden.bs.modal', function(e) {
                $("#tbl-employee").DataTable().destroy();
            })

            $('#checkAll').click(function() {
                let checked = this.checked;
                $('#right-modal-user #tbl-employee .input-check').prop('checked', checked);
                $('#right-modal-user #tbl-employee .input-check').each(function() {
                    let id = $(this).data('id');
                    if (checked) {
                        selectedEmployess.push(id);
                    } else {
                        selectedEmployess = selectedEmployess.filter(e => e !== id);
                    }
                });
            });
            $('#right-modal-user').on('show.bs.modal', function(e) {
                tblUser = $("#tbl-employee").DataTable({
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    ajax: {
                        url: "{{ URL::to('setting/location/employee/filter') }}",
                        type: "GET",
                    },
                    columns: [{
                            data: "id",
                            defaultContent: "--",
                            mRender: function(data, type, full) {
                                return `<input type="checkbox" class="input-check" data-id="${data}">`
                            }
                        },
                        {
                            data: "personal.fullname",
                            defaultContent: "--",
                            mRender: function(data, type, full) {
                                return `<strong>${data}</strong><br>${full.personal.email}`
                            }
                        },
                        {
                            data: "employment.branch_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.job_level_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.organization_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.job_position_name",
                            defaultContent: "--"
                        },
                        {
                            data: "employment.employment_status",
                            defaultContent: "--"
                        },
                    ],
                    drawCallback: function(settings) {
                        var api = this.api();
                        var node = api.rows().nodes()
                        for (var i = 0; i < node.length; i++) {
                            let dataId = $(node[i]).find('input').attr('data-id')
                            let isExist = objLocation.employees.some(item => item.id == dataId)
                            if (isExist) {
                                $(node[i]).find('input').prop('checked', true)
                            }
                        }
                    },
                });
            })

            tblUserLocation = $("#tbl-employee-location").DataTable({
                searching: false,
                paging: false,
                lengthChange: false,
                ordering: false,
                data: location.employees,
                columns: [{
                        data: "personal.fullname",
                        defaultContent: "--",
                        mRender: function(data, type, full) {
                            return `<strong>${data}</strong><br>${full.personal.email}`
                        }
                    },
                    {
                        data: "employment.branch_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.job_level_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.organization_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.job_position_name",
                        defaultContent: "--"
                    },
                    {
                        data: "employment.employment_status",
                        defaultContent: "--"
                    },
                    {
                        data: 'id',
                        className: "text-center",
                        mRender: function(data, type, full) {
                            return `<a title="Edit" class="btn btn-sm btn-danger text-white btn-delete-employee"><i class="fa fa-trash"></i></a>`
                        }
                    }
                ],
            });

            $('#tbl-employee').on('change', 'td input[type="checkbox"]', function() {
                let employee = tblUser.row($(this).parents('tr')).data();
                let val = $(this).prop('checked');
                if (val == true) {
                    selectedEmployess.push(employee)
                } else {
                    selectedEmployess.splice(employee, 1);
                }
            })

            $("#tbl-employee-location").on('click', '.btn-delete-employee', function() {
                let data = tblUserLocation.row($(this).parents('tr')).index();
                pinLocation.employees.splice(data, 1);
                reloadJsonDataTable(tblUserLocation, pinLocation.employees);
            })
            dataId != "" ? getLocationById() : null;
        })

        function getLocationById() {
            ajax({
                    id: dataId
                }, `{{ URL::to('location/show') }}`, "GET",
                function(json) {
                    pinLocation = json;
                    reloadJsonDataTable(tblUserLocation, pinLocation.employees);
                })
        }

        function submitLocation() {
            pinLocation.branch = {
                id: $('#branch_id').val()
            };
            pinLocation.name = $('#name').val();
            pinLocation.latitude = $('#latitude').val();
            pinLocation.longitude = $('#longitude').val();
            pinLocation.radius = $('#radius').val();
            pinLocation.description = $('#description').val();
            pinLocation.employees = JSON.stringify(pinLocation.employees);
            let method = dataId == "" ? "POST" : "PUT";
            let url = dataId == "" ? "{{ route('location.store') }}" : "{{ URL::to('location/update') }}"

            ajax(pinLocation, url, method, function(json) {
                toastr.success('Success');
                reloadJsonDataTable(tblUserLocation, JSON.parse(pinLocation.employees));
                setTimeout(() => {
                    location.reload();
                }, 1000);
            })
        }
        async function initMap() {
            const {
                Map
            } = await google.maps.importLibrary("maps");
            const myLatlng = {
                lat: -6.1944,
                lng: 106.8229
            };
            map = new Map(document.getElementById("map"), {
                center: myLatlng,
                zoom: 11,
            });
            let infoWindow = new google.maps.InfoWindow({
                content: "Click the map to get Lat/Lng!",
                position: myLatlng,
            });
            infoWindow.open(map);
            map.addListener("click", (mapsMouseEvent) => {
                infoWindow.close();
                infoWindow = new google.maps.InfoWindow({
                    position: mapsMouseEvent.latLng,
                });
                let latLong = mapsMouseEvent.latLng.toJSON();
                infoWindow.setContent(
                    JSON.stringify(latLong, null, 2),
                );
                $('#latitude').val(latLong.lat)
                $('#longitude').val(latLong.lng)
                infoWindow.open(map);
            });
        }
    </script>
@endsection
