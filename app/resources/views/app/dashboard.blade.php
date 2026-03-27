@extends('layouts.app')
@section('content')

    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/chartist.css">
    <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/rating.css">
    <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/lds-animate.css">
    <style>
        .text-camel{
            text-transform: capitalize
        }
        .fc-h-event .fc-event-title{
            white-space: normal;
        }
    </style>
    @if($DashboardType=='admin')
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-lg-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{url('/')}}/admin"><i class="f-16 fa fa-home"></i></a>
                            </li>
                            <li class="breadcrumb-item text-camel">Dashboard</li>
                            <li class="breadcrumb-item  text-camel">{{$DashboardType}}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid fs-13">
            <div class="row">
                <div class="col-sm-12 col-12 col-md-4 col-lg-4">
                    <div class="card investments">
                        <div class="card-header p-10">
                            <div class="row">
                                <div class="col-12 col-md-8"><div class="card-title fw-600 fs-15">Customer</div> </div>
                                <div class="col-12 col-md-4 d-flex justify-content-end align-items-center pr-10"><span class="full-right fs-14 fw-600"><a href="{{ route('manage_customers.index') }}">View</a></span></div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-sm-12 col-12 col-md-12 col-lg-12 pb-20 d-flex justify-content-center align-items-center mh-350" id="divCustomerCircleChart">
                                    <div id="CustomerCircleChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-0" id="divCustomerCircleChartStats">
                            <ul>
                                <li class="text-center"><span class="f-12">Last Month</span>
                                    <h6 class="f-w-600 mb-0 last-month">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">Today</span>
                                    <h6 class="f-w-600 mb-0 today">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">This Week</span>
                                    <h6 class="f-w-600 mb-0 this-week">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">This Month</span>
                                    <h6 class="f-w-600 mb-0 this-month">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">Total</span>
                                    <h6 class="f-w-600 mb-0 total">0</h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-12 col-md-4 col-lg-4">
                    <div class="card investments">
                        <div class="card-header p-10">
                            <div class="row">
                                <div class="col-12 col-md-8"><div class="card-title fw-600 fs-15">Orders</div> </div>
                                <div class="col-12 col-md-4 d-flex justify-content-end align-items-center pr-10"><span class="full-right fs-14 fw-600"><a href="{{ route('admin.order.index') }}">View</a></span></div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-sm-12 col-12 col-md-12 col-lg-12 pb-20 d-flex justify-content-center align-items-center mh-350"  id="divOrdersCircleChart">
                                    <div id="OrderCircleChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-0"  id="divOrdersCircleChartStats">
                            <ul>
                                <li class="text-center"><span class="f-12">Last Month</span>
                                    <h6 class="f-w-600 mb-0 last-month">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">Today</span>
                                    <h6 class="f-w-600 mb-0 today">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">This Week</span>
                                    <h6 class="f-w-600 mb-0 this-week">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">This Month</span>
                                    <h6 class="f-w-600 mb-0 this-month">0</h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-12 col-md-4 col-lg-4">
                    <div class="card investments">
                        <div class="card-header p-10">
                            <div class="row">
                                <div class="col-12 col-md-8"><div class="card-title fw-600 fs-15">Pending Shipments</div> </div>
                                <div class="col-12 col-md-4 d-flex justify-content-end align-items-center pr-10"><span class="full-right fs-14 fw-600"><a href="{{ route('admin.order.index') }}">View</a></span></div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-sm-12 col-12 col-md-12 col-lg-12 pb-20 d-flex justify-content-center align-items-center mh-350"  id="divShipmentCircleChart">
                                    <div id="DeliveryCircleChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-0"  id="divShipmentCircleChartStats">
                            <ul>
                                <li class="text-center"><span class="f-12">Today</span>
                                    <h6 class="f-w-600 mb-0 today">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">This Week</span>
                                    <h6 class="f-w-600 mb-0 this-week">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">This Month</span>
                                    <h6 class="f-w-600 mb-0 this-month">0</h6>
                                </li>
                                <li class="text-center"><span class="f-12">This Year</span>
                                    <h6 class="f-w-600 mb-0 this-year">0</h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-header p-10">
                            <div class="row">
                                <div class="col-12 col-md-8"><div class="card-title fw-600 fs-16">Recent Orders</div> </div>
                                <div class="col-12 col-md-4 d-flex justify-content-end align-items-center pr-10"><span class="full-right fs-14 fw-600"><a href="{{ route('admin.order.index') }}">View</a></span></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12 col-12 col-md-12 col-lg-12">
                                    <table class="table table-sm" id="tblOrders">
                                        <thead>
                                        <tr class="valign-top">
                                            <th class="text-center">Order No</th>
                                            <th class="text-center">Order Date</th>
                                            <th class="text-center">Contact Person</th>
                                            <th class="text-center">Contact Number</th>
                                            <th class="text-center">Order Amount</th>
                                            <th class="text-center">Order Status</th>
{{--                                            <th class="text-center">Payment Status</th>--}}
                                            <th class="text-center noExport">action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('scripts')
    <script src="{{url('/')}}/assets/js/chart/google/google-chart-loader.js"></script>
    <script src="{{url('/')}}/assets/js/chart/chartist/chartist.js"></script>
    <script src="{{url('/')}}/assets/js/chart/chartist/chartist-plugin-tooltip.js"></script>
    <script src="{{url('/')}}/assets/js/chart/apex-chart/apex-chart.js"></script>
    <script src="{{url('/')}}/assets/js/chart/apex-chart/stock-prices.js"></script>
    <script src="{{url('/')}}/assets/js/counter/jquery.waypoints.min.js"></script>
    <script src="{{url('/')}}/assets/js/counter/jquery.counterup.min.js"></script>
    <script src="{{url('/')}}/assets/js/counter/counter-custom.js"></script>
    <script src="{{url('/')}}/assets/js/rating/jquery.barrating.js"></script>
    <script src="{{url('/')}}/assets/js/rating/rating-script.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>
        $(document).ready(function(){
            const animationLoaders={
                ellipsis:'<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>',
                facebook:'<div class="lds-facebook"><div></div><div></div><div></div><div></div></div>',
                spinner:'<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>',
                svgSpinner:'<svg xmlns="http://www.w3.org/2000/svg"  height="200px" viewBox="0 0 200 200"><linearGradient id="a11"><stop offset="0" stop-color="#FF156D" stop-opacity="0"></stop><stop offset="1" stop-color="#FF156D"></stop></linearGradient><circle fill="none" stroke="url(#a11)" stroke-width="22" stroke-linecap="round" stroke-dasharray="0 44 0 44 0 44 0 44 0 360" cx="100" cy="100" r="70" transform-origin="center"><animateTransform type="rotate" attributeName="transform" calcMode="discrete" dur="2" values="360;324;288;252;216;180;144;108;72;36" repeatCount="indefinite"></animateTransform></circle></svg>',
                svgRipples:"<svg xmlns='http://www.w3.org/2000/svg'  height='200px' viewBox='0 0 200 200'><circle fill='none' stroke-opacity='1' stroke='#FF156D' stroke-width='.5' cx='100' cy='100' r='0'><animate attributeName='r' calcMode='spline' dur='2' values='1;80' keyTimes='0;1' keySplines='0 .2 .5 1' repeatCount='indefinite'></animate><animate attributeName='stroke-width' calcMode='spline' dur='2' values='0;25' keyTimes='0;1' keySplines='0 .2 .5 1' repeatCount='indefinite'></animate><animate attributeName='stroke-opacity' calcMode='spline' dur='2' values='1;0' keyTimes='0;1' keySplines='0 .2 .5 1' repeatCount='indefinite'></animate></circle></svg>"
            }
            const init=async()=>{
                // loadCalendar();
                // getDashboardStats();
                getRecentOrders();
                loadCustomerCircleChart();
                loadOrdersCircleChart();
                loadShipmentCircleChart();
            }
            const loadCustomerCircleChart=async()=>{
                const getData=async()=>{
                    return new Promise(async(resolve,reject)=>{
                        $.ajax({
                            type:"post",
                            url:"{{route('admin.dashboard.get.circle.stats.customer')}}",
                            headers: { 'X-CSRF-Token' : "{{ csrf_token() }}" },
                            dataType:"json",
                            async:true,
                            error:(e, x, settings, exception)=>{resolve([0, 0, 0, 0]);},
                            success:function(data){
                                resolve(data);
                            }
                        });
                    });
                }
                $('#divCustomerCircleChart').append('<div class="load-animation">'+animationLoaders.svgRipples+'</div>');
                let jsonData=await getData();
                $('#divCustomerCircleChartStats .last-month').html(jsonData.lastMonth)
                $('#divCustomerCircleChartStats .today').html(jsonData.today)
                $('#divCustomerCircleChartStats .this-week').html(jsonData.thisWeek)
                $('#divCustomerCircleChartStats .this-month').html(jsonData.thisMonth)
                $('#divCustomerCircleChartStats .total').html(jsonData.total)
                var options = {
                    chart: {
                        height: 350,
                        type: 'radialBar',
                    },
                    plotOptions: {
                        radialBar: {
                            dataLabels: {
                                name: {
                                    fontSize: '18px',
                                },
                                value: {
                                    fontSize: '14px',
                                    formatter: function (val, opts) {
                                        if (opts && opts.seriesIndex !== undefined) {
                                            return opts.w.config.series[opts.seriesIndex];
                                        }
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Today',
                                    formatter: function (w) {
                                        return jsonData.today
                                    }
                                }
                            }
                        }
                    },
                    series: [jsonData.lastMonth, jsonData.today, jsonData.thisWeek, jsonData.thisMonth],
                    labels: ['Last Month', 'Today', 'This Week', 'This Month'],
                    colors:['#655af3', '#fd2e64', '#51bb25', '#7a15f7']
                }

                var CustomerCircleChart = new ApexCharts(
                    document.querySelector("#CustomerCircleChart"),
                    options
                );
                $('#divCustomerCircleChart .load-animation').remove();
                CustomerCircleChart.render();
            }
            const loadOrdersCircleChart=async()=>{
                const getData=async()=>{
                    return new Promise(async(resolve,reject)=>{
                        $.ajax({
                            type:"post",
                            url:"{{route('admin.dashboard.get.circle.stats.orders')}}",
                            headers: { 'X-CSRF-Token' : "{{ csrf_token() }}" },
                            dataType:"json",
                            async:true,
                            error:(e, x, settings, exception)=>{resolve([0, 0, 0, 0]);},
                            success:function(data){
                                resolve(data);
                            }
                        });
                    });
                }
                $('#divOrdersCircleChart').append('<div class="load-animation">'+animationLoaders.svgRipples+'</div>');
                let jsonData=await getData();
                $('#divOrdersCircleChartStats .last-month').html(jsonData.lastMonth)
                $('#divOrdersCircleChartStats .today').html(jsonData.today)
                $('#divOrdersCircleChartStats .this-week').html(jsonData.thisWeek)
                $('#divOrdersCircleChartStats .this-month').html(jsonData.thisMonth)
                var options = {
                    chart: {
                        height: 350,
                        type: 'radialBar',
                    },
                    plotOptions: {
                        radialBar: {
                            dataLabels: {
                                name: {
                                    fontSize: '18px',
                                },
                                value: {
                                    fontSize: '14px',
                                    formatter: function (val, opts) {
                                        if (opts && opts.seriesIndex !== undefined) {
                                            return opts.w.config.series[opts.seriesIndex];
                                        }
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Today',
                                    formatter: function (w) {
                                        return jsonData.today
                                    }
                                }
                            }
                        }
                    },
                    series: [jsonData.lastMonth, jsonData.today, jsonData.thisWeek, jsonData.thisMonth],
                    labels: ['Last Month', 'Today', 'This Week', 'This Month'],
                    colors:['#655af3', '#fd2e64', '#51bb25', '#7a15f7']
                }

                var OrderCircleChart = new ApexCharts(
                    document.querySelector("#OrderCircleChart"),
                    options
                );
                $('#divOrdersCircleChart .load-animation').remove();
                OrderCircleChart.render();
            }
            const loadShipmentCircleChart=async()=>{
                const getData=async()=>{
                    return new Promise(async(resolve,reject)=>{
                        $.ajax({
                            type:"post",
                            url:"{{route('admin.dashboard.get.circle.stats.shipment')}}",
                            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                            dataType:"json",
                            async:true,
                            error:(e, x, settings, exception)=>{resolve([0, 0, 0, 0]);},
                            success:function(data){
                                resolve(data);
                            }
                        });
                    });
                }
                $('#divShipmentCircleChart').append('<div class="load-animation">'+animationLoaders.svgRipples+'</div>');
                let jsonData=await getData();
                $('#divShipmentCircleChartStats .this-year').html(jsonData.thisYear)
                $('#divShipmentCircleChartStats .today').html(jsonData.today)
                $('#divShipmentCircleChartStats .this-week').html(jsonData.thisWeek)
                $('#divShipmentCircleChartStats .this-month').html(jsonData.thisMonth)
                var options = {
                    chart: {
                        height: 350,
                        type: 'radialBar',
                    },
                    plotOptions: {
                        radialBar: {
                            dataLabels: {
                                name: {
                                    fontSize: '18px',
                                },
                                value: {
                                    fontSize: '14px',
                                    formatter: function (val, opts) {
                                        if (opts && opts.seriesIndex !== undefined) {
                                            return opts.w.config.series[opts.seriesIndex];
                                        }
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Today',
                                    formatter: function (w) {
                                        return jsonData.today
                                    }
                                }
                            }
                        }
                    },
                    series: [jsonData.today, jsonData.thisWeek, jsonData.thisMonth,jsonData.thisYear],
                    labels: ['Today', 'This Week','This Month','This Year'],
                    colors:['#655af3', '#51bb25', '#7a15f7', "#ff5f24"]
                }

                var DeliveryCircleChart = new ApexCharts(
                    document.querySelector("#DeliveryCircleChart"),
                    options
                );
                $('#divShipmentCircleChart .load-animation').remove();
                DeliveryCircleChart.render();
            }
            const getDashboardStats=async()=>{
                const loadCustomerPieChart=async(data)=>{
                    let ctx = document.getElementById('CustomerPieChart');
                    let pieLabels = ["Active", "Inactive", "Deleted"];
                    let pieData = [data.customer.stats.active, data.customer.stats.inactive, data.customer.stats.deleted];
                    let pieColors=["#51bb25","#ff5f24","#fd2e64"];
                    let config={
                        type: 'doughnut',
                        data: {
                            labels: pieLabels,
                            datasets: [
                                {
                                    data: pieData,
                                    backgroundColor: pieColors
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false
                        }
                    }
                    $('#divCustomerPieChart .load-animation').remove()
                    $('#CustomerPieChart').show();
                    let pieChart = new Chart(ctx, config);
                }
                const loadVendorPieChart=async(data)=>{
                    let ctx = document.getElementById('VendorPieChart');
                    let pieLabels = ["Active", "Inactive", "Deleted"];
                    let pieData = [data.vendor.stats.active, data.vendor.stats.inactive, data.vendor.stats.deleted];
                    let pieColors=["#51bb25","#ff5f24","#fd2e64"];
                    let config={
                        type: 'doughnut',
                        data: {
                            labels: pieLabels,
                            datasets: [
                                {
                                    data: pieData,
                                    backgroundColor: pieColors
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false
                        }
                    }
                    $('#divVendorPieChart .load-animation').remove()
                    $('#VendorPieChart').show();
                    let pieChart = new Chart(ctx, config);
                }
                const loadEmployeePieChart=async(data)=>{
                    let ctx = document.getElementById('EmployeePieChart');
                    let pieLabels = ["Active", "Inactive", "Deleted"];
                    let pieData = [data.vendor.stats.active, data.vendor.stats.inactive, data.vendor.stats.deleted];
                    let pieColors=["#51bb25","#ff5f24","#fd2e64"];
                    let config={
                        type: 'doughnut',
                        data: {
                            labels: pieLabels,
                            datasets: [
                                {
                                    data: pieData,
                                    backgroundColor: pieColors
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false
                        }
                    }
                    $('#divEmployeePieChart .load-animation').remove()
                    $('#EmployeePieChart').show();
                    let pieChart = new Chart(ctx, config);
                }
                $('#divCustomerPieChart').append('<div class="load-animation">'+animationLoaders.svgRipples+'</div>');
                $('#divVendorPieChart').append('<div class="load-animation">'+animationLoaders.svgRipples+'</div>');
                $('#divEmployeePieChart').append('<div class="load-animation">'+animationLoaders.svgRipples+'</div>');
                $('#CustomerPieChart').hide();
                $('#VendorPieChart').hide();
                $('#EmployeePieChart').hide();
                $.ajax({
                    type:"post",
                    url:"{{route('admin.dashboard.get.dashboard-stats')}}",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    dataType:"json",
                    async:true,
                    beforeSend:function(){
                        $('.ldsEllipsis').html(animationLoaders.ellipsis);
                    },
                    success:function(data){
                        // loadCustomerPieChart(data);
                        // loadVendorPieChart(data);
                        // loadEmployeePieChart(data);
                        ///Customers stats
                        $('#divCustomerPieChartStats .active').html(data.customer.stats.active);
                        $('#divCustomerPieChartStats .inactive').html(data.customer.stats.inactive);
                        $('#divCustomerPieChartStats .deleted').html(data.customer.stats.deleted);
                        //vendor stats
                        $('#divVendorPieChartStats .active').html(data.vendor.stats.active);
                        $('#divVendorPieChartStats .inactive').html(data.vendor.stats.inactive);
                        $('#divVendorPieChartStats .deleted').html(data.vendor.stats.deleted);
                        //employee stats
                        $('#divEmployeePieChartStats .active').html(data.employee.stats.active);
                        $('#divEmployeePieChartStats .inactive').html(data.employee.stats.inactive);
                        $('#divEmployeePieChartStats .deleted').html(data.employee.stats.deleted);

                        //customer outstandings
                        $('#divCustomerOrderValues').html(data.customer.orderValues);
                        $('#divCustomerReceived').html(data.customer.received);
                        $('#divCustomerOutstandings').html(data.customer.outstanding);
                        ///Vendors outstanding
                        $('#divVendorOrderValues').html(data.vendor.orderValues);
                        $('#divVendorPaid').html(data.vendor.paid);
                        $('#divVendorOutstanding').html(data.vendor.outstanding);
                    }
                });
            }

            const getRecentOrders = async () => {
                $('#tblOrders').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "ajax": {
                        "url": "{{route('admin.dashboard.get.recent.orders')}}?_token="+$('meta[name=_token]').attr('content'),
                        "headers": {
                            'X-CSRF-Token': "{{ csrf_token() }}"
                        },
                        data:{
                            length:10
                        },
                        "type": "POST"
                    },
                    deferRender: true,
                    responsive: true,
                    dom: 'Bfrtip',
                    order: [[0, 'desc']],
                    iDisplayLength: 10,
                    lengthMenu: [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "All"]],
                    buttons: [],
                    searching: false,
                    info: false,
                    paging: false,
                    columnDefs: [
                        {"className": "dt-center", "targets": "_all"}
                    ]
                });
            }
            const loadCharts=async()=>{
                const loadQuoteEnquiryBar=async()=>{
                    const getData=async()=>{
                        return new Promise(async(resolve,reject)=>{
                            $.ajax({
                                type:"post",
                                url:"{{route('admin.dashboard.get.orders.stats')}}",
                                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                                dataType:"json",
                                async:true,
                                error:(e, x, settings, exception)=>{resolve(["Month", "Quote Enquiry", "Quotes", "Orders"]);},
                                success:function(data){
                                    resolve(data);
                                }
                            });
                        });
                    }
                    let jsonData=await getData();
                    var a = google.visualization.arrayToDataTable(jsonData),
                        b = {
                            chart: {
                                title: "",
                                subtitle: ""
                            },
                            bars: "vertical",
                            vAxis: {
                                format: "decimal"
                            },
                            height: 400,
                            width:'100%',
                            colors: ["#148df6", "#655af3", "#fd2e64", "#51bb25", "#ff5f24"]


                        },
                        c = new google.charts.Bar(document.getElementById("QuoteEnquiryBar"));
                    c.draw(a, google.charts.Bar.convertOptions(b))
                }
                const loadPaymentsBarBar=async()=>{
                    const getData=async()=>{
                        return new Promise(async(resolve,reject)=>{
                            $.ajax({
                                type:"post",
                                url:"{{route('admin.dashboard.get.payments.stats')}}",
                                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                                dataType:"json",
                                async:true,
                                error:(e, x, settings, exception)=>{resolve(["Month", "Receipts", "Payments"]);},
                                success:function(data){
                                    resolve(data);
                                }
                            });
                        });
                    }
                    let jsonData=await getData();
                    var a = google.visualization.arrayToDataTable(jsonData),
                        b = {
                            chart: {
                                title: "",
                                subtitle: ""
                            },
                            bars: "vertical",
                            vAxis: {
                                format: "decimal"
                            },
                            height: 400,
                            width:'100%',
                            colors: ["#ff5f24","#148df6", "#655af3", "#fd2e64", "#51bb25"]


                        },
                        c = new google.charts.Bar(document.getElementById("PaymentsBar"));
                    c.draw(a, google.charts.Bar.convertOptions(b))
                }
                loadQuoteEnquiryBar();
                loadPaymentsBarBar();
            }
            const loadCalendar=async()=>{
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    validRange:{
                        start:"{{date('Y-m-d',strtotime($FY->FromDate))}}",
                        end:"{{date('Y-m-d',strtotime($FY->ToDate))}}"
                    },
                    events:"{{route('admin.dashboard.get.upcoming.payments')}}",
                });
                calendar.render();
            }
            $(document).on('click','.btnView',function(){
                window.location.replace("{{url('/')}}/admin/transaction/quote-enquiry/view/"+ $(this).attr('data-id'));
            });
            google.charts.load('current', {packages: ['corechart', 'bar']});
            google.charts.load('current', {'packages':['line']});
            google.charts.load('current', {'packages':['corechart']});
            // google.charts.setOnLoadCallback(loadCharts);
            init();
        });
    </script>
@endsection
