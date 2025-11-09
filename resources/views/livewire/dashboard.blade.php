<div class="page-inner" wire:init="init">
    <a href="{{route('dashboard')}}" class="btn btn-primary btn-round">Customer Portal</a>
    <a href="{{route('dashboard.agent')}}" class="btn btn-primary btn-round">Agent Portal</a>
    <a href="{{route('dashboard.lead')}}" class="btn btn-primary btn-round">Lead</a>
    <br><br>
    @if (in_array(auth()->user()->role->role_type, ['admin', 'superadmin', 'adminsales', 'leadwh']))
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <div class="card p-3">
                        <div class="d-flex align-items-center">
                            <span class="stamp stamp-md bg-secondary mr-3">
                                <i class="fa fa-shopping-cart"></i>
                            </span>
                            <div>
                                <h5 class="mb-1"><b>Active Transaction</b></h5>
                                <h5 class="mb-1"><b><a href="{{ route('transaction.list') }}">{{$transaction_active}} <small>Orders</small></a></b></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-3">
                        <div class="d-flex align-items-center">
                            <span class="stamp stamp-md bg-success mr-3">
                                <i class="fa fa-dollar-sign"></i>
                            </span>
                            <div>
                                <h5 class="mb-1"><b>Waiting Payment</b></h5>
                                <h5 class="mb-1"><b><a href="{{ route('transaction.waiting-payment') }}">{{$waiting_payment}} <small>Orders</small></a></b></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card p-3">
                        <div class="d-flex align-items-center">
                            <span class="stamp stamp-md bg-warning mr-3">
                                <i class="fab fa-product-hunt"></i>
                            </span>
                            <div>
                                <h5 class="mb-1"><b>Product Available</b></h5>
                                <h5 class="mb-1"><b><a href="{{ route('product') }}">{{$available_product}} <small>Products</small></a></b></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-md-6">
                    <div class="card p-3">
                        <div class="d-flex align-items-center">
                            <span class="stamp stamp-md bg-danger mr-3">
                                <i class="fa fa-dollar-sign"></i>
                            </span>
                            <div>
                                <h5 class="mb-1"><b>Transaction Complete</b></h5>
                                <h5 class="mb-1"><b><a href="{{ route('transaction.delivered') }}">Rp {{number_format($total_complete,0,',','.')}}</a></b></h5>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="col-md-6">
                    <div class="card p-3">
                        <div class="d-flex align-items-center">
                            <span class="stamp stamp-md bg-danger mr-3">
                                <i class="fa fa-users"></i>
                            </span>
                            <div>
                                <h5 class="mb-1"><b>Total Customer</b></h5>
                                <h5 class="mb-1"><b><a href="{{ route('customer-management') }}">{{$total_customer}} <small>Members</small></a></b></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title">Active Transaction & Waiting Payment</div>
                    <div class="row py-3">
                        <!-- <div class="col-md-4 d-flex flex-column justify-content-around">
                            <div>
                                <h6 class="fw-bold text-uppercase text-success op-8">Active Transaction</h6>
                                <h3 class="fw-bold">{{$transaction_active}}</h3>
                            </div>
                            <div>
                                <h6 class="fw-bold text-uppercase text-danger op-8">Waiting <br>Payment</h6>
                                <h3 class="fw-bold">{{$waiting_payment}}</h3>
                            </div>
                        </div> -->
                        <div class="col-md-12">
                            <div id="chart-container">
                                <canvas id="totalIncomeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-secondary mr-3">
                        <i class="fa fa-shopping-cart"></i>
                    </span>
                    <div>
                        <h5 class="mb-1"><b>Total Order Number</b></h5>
                        <h5 class="mb-1"><b><a href="{{ route('transaction.list') }}">{{$total_order}} <small>Orders</small></a></b></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-secondary mr-3">
                        <i class="fa fa-shopping-cart"></i>
                    </span>
                    <div>
                        <h5 class="mb-1"><b>Total Order Amount</b></h5>
                        <h5 class="mb-1"><b><a href="{{ route('transaction.list') }}">Rp {{number_format($total_complete,0,',','.')}}</a></b></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <span class="stamp stamp-md bg-secondary mr-3">
                        <i class="fa fa-shopping-cart"></i>
                    </span>
                    <div>
                        <h5 class="mb-1"><b>Shipping Fee</b></h5>
                        <h5 class="mb-1"><b><a href="{{ route('transaction.list') }}">Rp 0</a></b></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card full-height">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="card-title">Total transaction <br>completed by month</div>
                        </div>
                        <div class="col-md-2">
                            <div class="card-title text-success">Total Income <br><b>Rp {{number_format($total_complete,0,',','.')}}</b></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card full-height">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-title">Product Transaction Performance - new</div>
                            <div class="" style="width:100%;height:220px;" id="theChart2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Dashboard Leads -->
    <div class="row">
        <div class="col-md-6">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title">Total Lead Active</div>
                    <div class="col-md-12">
                        <div id="chart-container">
                            <canvas id="totalLeadActiveChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title">Total Lead (Qualified dan Not Qualified)</div>
                    <div class="col-md-12">
                        <div id="chart-container">
                            <canvas id="totalLeadQualifiedChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title">Total Lead By Stage</div>
                    <div class="col-md-12">
                        <div id="chart-container">
                            <canvas id="totalLeadbyStageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title">Total Activity By Lead</div>
                    <div class="col-md-12">
                        <div id="chart-container">
                            <canvas id="totalActivityLeadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card full-height">
                <div class="card-body">
                    <div class="card-title">Top Product Need 1</div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-stripped table-hover">
                                <tr>
                                    <th>No</th>
                                    <th>Product Name</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                <tr>
                                    @foreach ($product_need as $key => $prod)
                                <tr>
                                    <td>{{$key + 1}}</td>
                                    <td>{{$prod->product->name}}</td>
                                    <td>{{$prod->qty}}</td>
                                    <td>{{number_format($prod->price,0,',','.')}}</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Dashboard Leads -->
    <div class="row">
    <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Top Products</div>
                </div>
                <div class="card-body pb-0">
                    @foreach ($top_product as $topprod)
                    <div class="d-flex">
                        <div class="avatar">
                            <img src="{{getImage($topprod->image)}}" class="avatar-img rounded-circle">
                        </div>
                        <div class="flex-1 pt-1 ml-2">
                            <h6 class="fw-bold mb-1">{{$topprod->product->name}}</h6>
                            <small class="text-muted">Stock : {{$topprod->product->stock}}</small>
                        </div>
                        <div class="d-flex ml-auto align-items-center">
                            <h3 class="text-info fw-bold">{{$topprod->sum_qty}}</h3>
                        </div>
                    </div>
                    <div class="separator-dashed"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Top Likes</div>
                </div>
                <div class="card-body pb-0">
                    @foreach ($top_like as $topli)
                    <div class="d-flex">
                        <div class="avatar">
                            <img src="{{getImage($topli->image)}}" class="avatar-img rounded-circle">
                        </div>
                        <div class="flex-1 pt-1 ml-2">
                            <h6 class="fw-bold mb-1">{{$topli->product->name}}</h6>
                            <small class="text-muted">Stock : {{$topli->product->stock}}</small>
                        </div>
                        <div class="d-flex ml-auto align-items-center">
                            <h3 class="text-info fw-bold">{{$topli->count_like}}</h3>
                        </div>
                    </div>
                    <div class="separator-dashed"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Top Rating</div>
                </div>
                <div class="card-body pb-0">
                    @foreach ($top_rate as $rate)
                    <div class="d-flex">
                        <div class="avatar">
                            <img src="{{getImage($rate->product->image)}}" class="avatar-img rounded-circle">
                        </div>
                        <div class="flex-1 pt-1 ml-2">
                            <h6 class="fw-bold mb-1">{{$rate->product->name}}</h6>
                            <small class="text-muted">Stock : {{$rate->product->stock}}</small>
                        </div>
                        <div class="d-flex ml-auto align-items-center">
                            <i class="fas fa-star text-warning"></i>
                            @if ($rate->transaction && $rate->transaction->commentRating)
                            <h3 class="text-info fw-bold">{{$rate->rating}}</h3>
                            @else
                            <h3 class="text-info fw-bold">0</h3>
                            @endif
                        </div>
                    </div>
                    <div class="separator-dashed"></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Product Re-stock</div>
                </div>
                <div class="card-body pb-0">
                    @foreach ($product_restock as $prod)
                    <div class="d-flex">
                        <div class="avatar">
                            <img src="{{getImage(@$prod->image)}}" class="avatar-img rounded-circle">
                        </div>
                        <div class="flex-1 pt-1 ml-2">
                            <h6 class="fw-bold mb-1">{{$prod->name}}</h6>
                            <small class="text-muted">Stock : {{$prod->stock}}</small>
                        </div>
                    </div>
                    <div class="separator-dashed"></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>
    <script src="{{asset('assets/js/plugin/chart.js/chart.min.js')}}"></script>
    <script src="{{asset('assets/js/plugin/highcharts/highcharts.js')}}"></script>
    <script>
        @if (in_array(auth()->user()->role->role_type, ['admin', 'superadmin', 'adminsales', 'leadwh']))
        // chart transaction active
        var totalIncomeChart = document.getElementById('totalIncomeChart').getContext('2d');
        var mytotalIncomeChart = new Chart(totalIncomeChart, {
            type: 'bar',
            data: {
                labels: ["Active", "Waiting Payment"],
                datasets : [{
                    label: "Total Income",
                    backgroundColor: '#ff9e27',
                    borderColor: 'rgb(23, 125, 255)',
                    data: [{{$transaction_active}}, {{$waiting_payment}}],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 0 //this will remove only the label
                        },
                        gridLines : {
                            drawBorder: false,
                            display : false
                        }
                    }],
                    xAxes : [ {
                        gridLines : {
                            drawBorder: false,
                            display : false
                        },
                        ticks : {
                            autoSkip: false
                        }
                    }]
                },
            }
        });
        @endif
        // chart lead active
        var totalLeadActiveChart = document.getElementById('totalLeadActiveChart').getContext('2d');
        var mytotalLeadActiveChart = new Chart(totalLeadActiveChart, {
            type: 'bar',
            data: {
                labels: ["Create", "Waiting Approval"],
                datasets : [{
                    label: "Total Income",
                    backgroundColor: ['#f8ab26', '#9c39f3'],
                    borderColor: 'rgb(23, 125, 255)',
                    data: [{{$lead_active_create}}, {{$lead_active_waiting}}],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 0 //this will remove only the label
                        },
                        gridLines : {
                            drawBorder: false,
                            display : false
                        }
                    }],
                    xAxes : [ {
                        gridLines : {
                            drawBorder: false,
                            display : false
                        },
                        ticks : {
                            autoSkip: false
                        }
                    }]
                },
            }
        });

        // chart lead qualified
        var totalLeadQualifiedChart = document.getElementById('totalLeadQualifiedChart').getContext('2d');
        var mytotalLeadQualifiedChart = new Chart(totalLeadQualifiedChart, {
            type: 'bar',
            data: {
                labels: ["Qualified", "Not Qualified"],
                datasets : [{
                    label: "Total Income",
                    backgroundColor: ['#54bd53', '#ee3939'],
                    borderColor: 'rgb(23, 125, 255)',
                    data: [{{$lead_qualified}}, {{$lead_unqualified}}],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 0 //this will remove only the label
                        },
                        gridLines : {
                            drawBorder: false,
                            display : false
                        }
                    }],
                    xAxes : [ {
                        gridLines : {
                            drawBorder: false,
                            display : false
                        },
                        ticks : {
                            autoSkip: false
                        }
                    }]
                },
            }
        });

        // chart lead by stage
        var totalLeadbyStageChart = document.getElementById('totalLeadbyStageChart').getContext('2d');
        var mytotalLeadbyStageChart = new Chart(totalLeadbyStageChart, {
            type: 'bar',
            data: {
                labels: ["Create", "On Progress", "Waiting Approval", "Qualified"],
                datasets : [{
                    label: "Total Income",
                    backgroundColor: ['#f8ab26','#2839ee','#9c39f3','#54bd53'],
                    borderColor: 'rgb(23, 125, 255)',
                    data: [{{$lead_active_create}}, {{$lead_active_waiting}}, {{$lead_active_waiting}}, {{$lead_qualified}}],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 0 //this will remove only the label
                        },
                        gridLines : {
                            drawBorder: false,
                            display : false
                        }
                    }],
                    xAxes : [ {
                        gridLines : {
                            drawBorder: false,
                            display : false
                        },
                        ticks : {
                            autoSkip: false
                        }
                    }]
                },
            }
        });

        // chart activity by lead
        var totalActivityLeadChart = document.getElementById('totalActivityLeadChart').getContext('2d');
        var mytotalActivityLeadChart = new Chart(totalActivityLeadChart, {
            type: 'bar',
            data: {
                labels: ["Open", "In Progress", "Completed", "Canceled"],
                datasets : [{
                    label: "Total Income",
                    backgroundColor: ['#f8ab26','#2839ee','#54bd53','#ee3939'],
                    borderColor: 'rgb(23, 125, 255)',
                    data: [{{$activity_open}}, {{$activity_inprogress}}, {{$activity_completed}}, {{$activity_canceled}}],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 0 //this will remove only the label
                        },
                        gridLines : {
                            drawBorder: false,
                            display : false
                        }
                    }],
                    xAxes : [ {
                        gridLines : {
                            drawBorder: false,
                            display : false
                        },
                        ticks : {
                            autoSkip: false
                        }
                    }]
                },
            }
        });

        //Highchart product performance
        $(function () {
			/* var data = {"name": "Days", "data": [{"x":1647012056,"y":3309120},{"x":1647098456,"y":974880},{"x":1647184856,"y":258480},{"x":1647271256,"y":81360},{"x":1647357656,"y":25676640},{"x":1647444056,"y":9694800},{"x":1647530456,"y":1923120},{"x":1647616856,"y":4289760},{"x":1647703256,"y":1473120},{"x":1647789656,"y":1319040},{"x":1647876056,"y":95760},{"x":1647962456,"y":0},{"x":1648048856,"y":4649040},{"x":1648135256,"y":1697040},{"x":1648221656,"y":0},{"x":1648308056,"y":14983920},{"x":1648394456,"y":3482640},{"x":1648480856,"y":2400480},{"x":1648567256,"y":1206000},{"x":1648653656,"y":2980800},{"x":1648740056,"y":7539840},{"x":1648826456,"y":3270960},{"x":1648912856,"y":4339440},{"x":1648999256,"y":944640},{"x":1649085656,"y":833760},{"x":1649172056,"y":19931040},{"x":1649258456,"y":2268720},{"x":1649344856,"y":995040},{"x":1649431256,"y":2774160},{"x":1649517656,"y":2921040},{"x":1649604056,"y":1438560}] }; */
			var data = {!! json_encode($datareportperform) !!};
			chart = new Highcharts.Chart({ 
				chart: {
					renderTo: 'theChart2',
					type: 'area'
				},
				credits: {
					/* enabled: false, */
					text: "Product Transaction Performance"
				},
				title: {
					text: 'Total This Month'
				},
				subtitle: {
					text: ''
				},
				xAxis: {
					categories: {!! json_encode($datatanggal) !!}			},
				yAxis: {
					title: {
						/* text: 'Revenue (IDR)' */
						text: ''
					},
					plotLines: [
						{
							value: 0,
							width: 1,
							color: '#808080'
						}
					]
				},
				series: data
			});
		});
        //End highcharts

        // var myBarChart = new Chart(ctx, {
        // type: 'bar',
        // data: data,
        // options: {
        //     barValueSpacing: 20,
        //     scales: {
        //     yAxes: [{
        //         ticks: {
        //         min: 0,
        //         }
        //     }]
        //     }
        // }
        // });

    </script>
    @endpush
</div>