<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MHIS || HRIS</title>

    <!-- Bootstrap -->
    <link href="/plugins/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="/plugins/nprogress/nprogress.css" rel="stylesheet">
    <link rel="stylesheet" href="/plugins/toastr/toastr.css">
    <link href="/plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="/build/css/custom.min.css" rel="stylesheet">
    <style>
      table.dataTable tbody td {
        vertical-align: middle;
      }
    </style>
  </head>

  <body style="background-color: aliceblue">
    <div class="container body">
       <nav class="navbar navbar-expand-lg navbar-light bg-success" style="justify-content: center">
        <h4 class="mt-3 font-weight-bold text-white">Report - {{$date}}</h4>
      </nav>
      <div class="clearfix"></div>
      <div class="spacer"></div>
      <div class="row">
        <div class="col-md-6 col-sm-12">
          <div class="x_panel">
               <div class="x_title">
                    <h5>List of Late Employees - {{$date}}</h5>
                    <div class="clearfix"></div>
                </div>
              <div class="x_content">
                  <table id="tbl-latein" class="table table-striped table-bordered table-sm" style="width: 100%">
                      <thead>
                          <tr>
                              <th>No</th>
                              <th>Name</th>
                              <th>Shift Name</th>
                              <th>Schedule</th>
                              <th>Clock In</th>
                              <th>Late (Minutes)</th>
                          </tr>
                      </thead>
                      <tbody>
                          
                      </tbody>
                  </table>
              </div>
          </div>
        </div>
        <div class="col-md-6 col-sm-12">
          <div class="x_panel">
              <div class="x_content">
                  <iframe width="100%" height="500" src="https://lookerstudio.google.com/embed/reporting/70fadb29-3585-47c8-9aec-7800aba1c2fd/page/p_ow5col6j8c" frameborder="0" style="border:0" allowfullscreen sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe>
              </div>
          </div>
        </div>
      </div>
    </div>

    <!-- jQuery -->
    <script src="/plugins/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="/plugins/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FastClick -->
    <script src="/plugins/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="/plugins/nprogress/nprogress.js"></script>
    
    <!-- Custom Theme Scripts -->
    <script src="/build/js/custom.min.js"></script>

    <script src="/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/plugins/moment/min/moment.min.js"></script>
    <script src="/js/script.js?v=1.1.1"></script>

    <script>
      let dataLatein = [];
      $(document).ready(function(){
          getLatein()
          setInterval(() => getLatein(), 10000);
          tblLatein=$("#tbl-latein").DataTable({
            "paging":   false,
            "ordering": false,
            "searching":     false,
            "data":dataLatein,
            columns:[
                {
                    data:"employee_id",
                    defaultContent:"--",
                    width:"5%",
                },
                {
                    data:"full_name",
                    defaultContent:"--",
                    mRender:function(data,type,full){
                      return `<div class="text-left font-weight-bold"><h5>${data}</h5></div>`
                    }
                },
                {
                    data:"shift_name",
                    defaultContent:"--",
                    width:"8%",
                },
                {
                    data:"schedule_out",
                    defaultContent:"--",
                    width:"15%",
                    mRender:function(data,type,full){
                      return `${full.schedule_in} - ${data}`
                    }
                },
                {
                    data:"clock_in",
                    defaultContent:"--",
                    width:"15%",
                    className:'text-danger font-weight-bold text-center',
                    mRender:function(data,type,full){
                      return moment(data).format('HH:mm:ss')
                    }
                },
                {
                    data:"late_in",
                    defaultContent:"--",
                    className:'text-danger font-weight-bold text-center',
                    width:"10%",
                },
            ],
            columnDefs: [
              {
                targets: "_all",
                className: 'font-weight-bold text-center',
              },
            ],
            fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                $('td:eq(0)', nRow).html(iDisplayIndexFull +1);
            }
          })
      })

      function getLatein() {
        ajax(null, `{{URL::to('api/attendance/summary')}}`, "GET",
          function(item) {
            dataLatein = item;
            reloadJsonDataTable(tblLatein, dataLatein)
          },function(json){
            console.log(json)
          }
        )
      }
    </script>
  </body>
</html>
