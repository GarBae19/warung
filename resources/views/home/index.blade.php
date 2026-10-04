@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <style>
        #dt-sum-sales tfoot th {
            font-size: 14px;
            vertical-align: middle;
        }

        #dt-sum-sales tbody td {
            vertical-align: middle;
        }

        #dt-sum-piutang tfoot th {
            font-size: 14px;
            vertical-align: middle;
        }

        #dt-sum-piutang tbody td {
            vertical-align: middle;
        }
    </style>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard v1</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Small boxes (Stat box) -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3 id="jumlahSales">{{ $sales }}</h3>

                                <p>New Sales</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-bag"></i>
                            </div>
                            <a href="#" class="small-box-footer" data-toggle="modal" data-target="#modalInfoSales">
                                More info <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3 id="sumSales"><sup
                                        style="font-size: 20px">Rp.</sup>{{ number_format($sumsales, 0, ',', '.') }}</h3>

                                <p>Keuntungan Sementara</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars"></i>
                            </div>
                            <a href="#" class="small-box-footer" data-toggle="modal" data-target="#modalInfoSumSales">
                                More info <i class="fas fa-arrow-circle-right"></i>
                            </a>

                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3 id="jumlahStockHabis">{{ $stock }}</h3>

                                <p>Stock Habis</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <a href="#" class="small-box-footer" data-toggle="modal"
                                data-target="#modalInfoStockHabis">
                                More info <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3 id="sumPiutang"><sup
                                        style="font-size: 20px">Rp.</sup>{{ number_format($sumpiutang, 0, ',', '.') }}</h3>

                                <p>Piutang</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                            <a href="#" class="small-box-footer" data-toggle="modal"
                                data-target="#modalInfoSumPiutang">
                                More info <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <!-- /.row -->
                <!-- Main row -->
                <div class="row">
                    <!-- Left col -->
                    <section class="col-lg-7 connectedSortable">
                        <!-- Custom tabs (Charts with tabs)-->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie mr-1"></i>
                                    Sales
                                </h3>
                                <div class="card-tools">
                                    <ul class="nav nav-pills ml-auto">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- /.card-header -->
                            <div class="card-body">
                                <div class="tab-content p-0">
                                    <!-- Morris chart - Sales -->
                                    <div class="chart tab-pane active" id="revenue-chart"
                                        style="position: relative; height: 300px;">
                                        <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                                    </div>
                                    <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                                        <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
                                    </div>
                                </div>
                            </div><!-- /.card-body -->
                        </div>
                        <!-- /.card -->

                        <!-- DIRECT CHAT -->
                        <div class="card direct-chat direct-chat-primary">
                            <div class="card-header">
                                <h3 class="card-title">Direct Chat</h3>

                                <div class="card-tools">
                                    <span title="3 New Messages" class="badge badge-primary">3</span>
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-tool" title="Contacts"
                                        data-widget="chat-pane-toggle">
                                        <i class="fas fa-comments"></i>
                                    </button>
                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <!-- Conversations are loaded here -->
                                <div class="direct-chat-messages">
                                    <!-- Message. Default to the left -->
                                    <div class="direct-chat-msg">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-left">Alexander Pierce</span>
                                            <span class="direct-chat-timestamp float-right">23 Jan 2:00 pm</span>
                                        </div>
                                        <!-- /.direct-chat-infos -->
                                        <img class="direct-chat-img" src="dist/img/user1-128x128.jpg"
                                            alt="message user image">
                                        <!-- /.direct-chat-img -->
                                        <div class="direct-chat-text">
                                            Is this template really for free? That's unbelievable!
                                        </div>
                                        <!-- /.direct-chat-text -->
                                    </div>
                                    <!-- /.direct-chat-msg -->

                                    <!-- Message to the right -->
                                    <div class="direct-chat-msg right">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-right">Sarah Bullock</span>
                                            <span class="direct-chat-timestamp float-left">23 Jan 2:05 pm</span>
                                        </div>
                                        <!-- /.direct-chat-infos -->
                                        <img class="direct-chat-img" src="dist/img/user3-128x128.jpg"
                                            alt="message user image">
                                        <!-- /.direct-chat-img -->
                                        <div class="direct-chat-text">
                                            You better believe it!
                                        </div>
                                        <!-- /.direct-chat-text -->
                                    </div>
                                    <!-- /.direct-chat-msg -->

                                    <!-- Message. Default to the left -->
                                    <div class="direct-chat-msg">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-left">Alexander Pierce</span>
                                            <span class="direct-chat-timestamp float-right">23 Jan 5:37 pm</span>
                                        </div>
                                        <!-- /.direct-chat-infos -->
                                        <img class="direct-chat-img" src="dist/img/user1-128x128.jpg"
                                            alt="message user image">
                                        <!-- /.direct-chat-img -->
                                        <div class="direct-chat-text">
                                            Working with AdminLTE on a great new app! Wanna join?
                                        </div>
                                        <!-- /.direct-chat-text -->
                                    </div>
                                    <!-- /.direct-chat-msg -->

                                    <!-- Message to the right -->
                                    <div class="direct-chat-msg right">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-right">Sarah Bullock</span>
                                            <span class="direct-chat-timestamp float-left">23 Jan 6:10 pm</span>
                                        </div>
                                        <!-- /.direct-chat-infos -->
                                        <img class="direct-chat-img" src="dist/img/user3-128x128.jpg"
                                            alt="message user image">
                                        <!-- /.direct-chat-img -->
                                        <div class="direct-chat-text">
                                            I would love to.
                                        </div>
                                        <!-- /.direct-chat-text -->
                                    </div>
                                    <!-- /.direct-chat-msg -->

                                </div>
                                <!--/.direct-chat-messages-->

                                <!-- Contacts are loaded here -->
                                <div class="direct-chat-contacts">
                                    <ul class="contacts-list">
                                        <li>
                                            <a href="#">
                                                <img class="contacts-list-img" src="dist/img/user1-128x128.jpg"
                                                    alt="User Avatar">

                                                <div class="contacts-list-info">
                                                    <span class="contacts-list-name">
                                                        Count Dracula
                                                        <small class="contacts-list-date float-right">2/28/2015</small>
                                                    </span>
                                                    <span class="contacts-list-msg">How have you been? I
                                                        was...</span>
                                                </div>
                                                <!-- /.contacts-list-info -->
                                            </a>
                                        </li>
                                        <!-- End Contact Item -->
                                        <li>
                                            <a href="#">
                                                <img class="contacts-list-img" src="dist/img/user7-128x128.jpg"
                                                    alt="User Avatar">

                                                <div class="contacts-list-info">
                                                    <span class="contacts-list-name">
                                                        Sarah Doe
                                                        <small class="contacts-list-date float-right">2/23/2015</small>
                                                    </span>
                                                    <span class="contacts-list-msg">I will be waiting for...</span>
                                                </div>
                                                <!-- /.contacts-list-info -->
                                            </a>
                                        </li>
                                        <!-- End Contact Item -->
                                        <li>
                                            <a href="#">
                                                <img class="contacts-list-img" src="dist/img/user3-128x128.jpg"
                                                    alt="User Avatar">

                                                <div class="contacts-list-info">
                                                    <span class="contacts-list-name">
                                                        Nadia Jolie
                                                        <small class="contacts-list-date float-right">2/20/2015</small>
                                                    </span>
                                                    <span class="contacts-list-msg">I'll call you back at...</span>
                                                </div>
                                                <!-- /.contacts-list-info -->
                                            </a>
                                        </li>
                                        <!-- End Contact Item -->
                                        <li>
                                            <a href="#">
                                                <img class="contacts-list-img" src="dist/img/user5-128x128.jpg"
                                                    alt="User Avatar">

                                                <div class="contacts-list-info">
                                                    <span class="contacts-list-name">
                                                        Nora S. Vans
                                                        <small class="contacts-list-date float-right">2/10/2015</small>
                                                    </span>
                                                    <span class="contacts-list-msg">Where is your new...</span>
                                                </div>
                                                <!-- /.contacts-list-info -->
                                            </a>
                                        </li>
                                        <!-- End Contact Item -->
                                        <li>
                                            <a href="#">
                                                <img class="contacts-list-img" src="dist/img/user6-128x128.jpg"
                                                    alt="User Avatar">

                                                <div class="contacts-list-info">
                                                    <span class="contacts-list-name">
                                                        John K.
                                                        <small class="contacts-list-date float-right">1/27/2015</small>
                                                    </span>
                                                    <span class="contacts-list-msg">Can I take a look at...</span>
                                                </div>
                                                <!-- /.contacts-list-info -->
                                            </a>
                                        </li>
                                        <!-- End Contact Item -->
                                        <li>
                                            <a href="#">
                                                <img class="contacts-list-img" src="dist/img/user8-128x128.jpg"
                                                    alt="User Avatar">

                                                <div class="contacts-list-info">
                                                    <span class="contacts-list-name">
                                                        Kenneth M.
                                                        <small class="contacts-list-date float-right">1/4/2015</small>
                                                    </span>
                                                    <span class="contacts-list-msg">Never mind I found...</span>
                                                </div>
                                                <!-- /.contacts-list-info -->
                                            </a>
                                        </li>
                                        <!-- End Contact Item -->
                                    </ul>
                                    <!-- /.contacts-list -->
                                </div>
                                <!-- /.direct-chat-pane -->
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <form action="#" method="post">
                                    <div class="input-group">
                                        <input type="text" name="message" placeholder="Type Message ..."
                                            class="form-control">
                                        <span class="input-group-append">
                                            <button type="button" class="btn btn-primary">Send</button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                            <!-- /.card-footer-->
                        </div>
                        <!--/.direct-chat -->

                        <!-- TO DO List -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ion ion-clipboard mr-1"></i>
                                    To Do List
                                </h3>

                                <div class="card-tools">
                                    <ul class="pagination pagination-sm">
                                        <li class="page-item"><a href="#" class="page-link">&laquo;</a>
                                        </li>
                                        <li class="page-item"><a href="#" class="page-link">1</a></li>
                                        <li class="page-item"><a href="#" class="page-link">2</a></li>
                                        <li class="page-item"><a href="#" class="page-link">3</a></li>
                                        <li class="page-item"><a href="#" class="page-link">&raquo;</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <ul class="todo-list" data-widget="todo-list">
                                    <li>
                                        <!-- drag handle -->
                                        <span class="handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <!-- checkbox -->
                                        <div class="icheck-primary d-inline ml-2">
                                            <input type="checkbox" value="" name="todo1" id="todoCheck1">
                                            <label for="todoCheck1"></label>
                                        </div>
                                        <!-- todo text -->
                                        <span class="text">Design a nice theme</span>
                                        <!-- Emphasis label -->
                                        <small class="badge badge-danger"><i class="far fa-clock"></i> 2
                                            mins</small>
                                        <!-- General tools such as edit or delete-->
                                        <div class="tools">
                                            <i class="fas fa-edit"></i>
                                            <i class="fas fa-trash-o"></i>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <div class="icheck-primary d-inline ml-2">
                                            <input type="checkbox" value="" name="todo2" id="todoCheck2"
                                                checked>
                                            <label for="todoCheck2"></label>
                                        </div>
                                        <span class="text">Make the theme responsive</span>
                                        <small class="badge badge-info"><i class="far fa-clock"></i> 4
                                            hours</small>
                                        <div class="tools">
                                            <i class="fas fa-edit"></i>
                                            <i class="fas fa-trash-o"></i>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <div class="icheck-primary d-inline ml-2">
                                            <input type="checkbox" value="" name="todo3" id="todoCheck3">
                                            <label for="todoCheck3"></label>
                                        </div>
                                        <span class="text">Let theme shine like a star</span>
                                        <small class="badge badge-warning"><i class="far fa-clock"></i> 1
                                            day</small>
                                        <div class="tools">
                                            <i class="fas fa-edit"></i>
                                            <i class="fas fa-trash-o"></i>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <div class="icheck-primary d-inline ml-2">
                                            <input type="checkbox" value="" name="todo4" id="todoCheck4">
                                            <label for="todoCheck4"></label>
                                        </div>
                                        <span class="text">Let theme shine like a star</span>
                                        <small class="badge badge-success"><i class="far fa-clock"></i> 3
                                            days</small>
                                        <div class="tools">
                                            <i class="fas fa-edit"></i>
                                            <i class="fas fa-trash-o"></i>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <div class="icheck-primary d-inline ml-2">
                                            <input type="checkbox" value="" name="todo5" id="todoCheck5">
                                            <label for="todoCheck5"></label>
                                        </div>
                                        <span class="text">Check your messages and notifications</span>
                                        <small class="badge badge-primary"><i class="far fa-clock"></i> 1
                                            week</small>
                                        <div class="tools">
                                            <i class="fas fa-edit"></i>
                                            <i class="fas fa-trash-o"></i>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <div class="icheck-primary d-inline ml-2">
                                            <input type="checkbox" value="" name="todo6" id="todoCheck6">
                                            <label for="todoCheck6"></label>
                                        </div>
                                        <span class="text">Let theme shine like a star</span>
                                        <small class="badge badge-secondary"><i class="far fa-clock"></i> 1
                                            month</small>
                                        <div class="tools">
                                            <i class="fas fa-edit"></i>
                                            <i class="fas fa-trash-o"></i>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer clearfix">
                                <button type="button" class="btn btn-primary float-right"><i class="fas fa-plus"></i>
                                    Add item</button>
                            </div>
                        </div>
                        <!-- /.card -->
                    </section>
                    <!-- /.Left col -->
                    <!-- right col (We are only adding the ID to make the widgets sortable)-->
                    <section class="col-lg-5 connectedSortable">

                        <!-- Map card -->
                        <div class="card bg-gradient-primary">
                            <div class="card-header border-0">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    Visitors
                                </h3>
                                <!-- card tools -->
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary btn-sm daterange" title="Date range">
                                        <i class="far fa-calendar-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                                <!-- /.card-tools -->
                            </div>
                            <div class="card-body">
                                <div id="world-map" style="height: 250px; width: 100%;"></div>
                            </div>
                            <!-- /.card-body-->
                            <div class="card-footer bg-transparent">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <div id="sparkline-1"></div>
                                        <div class="text-white">Visitors</div>
                                    </div>
                                    <!-- ./col -->
                                    <div class="col-4 text-center">
                                        <div id="sparkline-2"></div>
                                        <div class="text-white">Online</div>
                                    </div>
                                    <!-- ./col -->
                                    <div class="col-4 text-center">
                                        <div id="sparkline-3"></div>
                                        <div class="text-white">Sales</div>
                                    </div>
                                    <!-- ./col -->
                                </div>
                                <!-- /.row -->
                            </div>
                        </div>
                        <!-- /.card -->

                        <!-- solid sales graph -->
                        <div class="card bg-gradient-info">
                            <div class="card-header border-0">
                                <h3 class="card-title">
                                    <i class="fas fa-th mr-1"></i>
                                    Sales Graph
                                </h3>

                                <div class="card-tools">
                                    <button type="button" class="btn bg-info btn-sm" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn bg-info btn-sm" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas class="chart" id="line-chart"
                                    style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer bg-transparent">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <input type="text" class="knob" data-readonly="true" value="20"
                                            data-width="60" data-height="60" data-fgColor="#39CCCC">

                                        <div class="text-white">Mail-Orders</div>
                                    </div>
                                    <!-- ./col -->
                                    <div class="col-4 text-center">
                                        <input type="text" class="knob" data-readonly="true" value="50"
                                            data-width="60" data-height="60" data-fgColor="#39CCCC">

                                        <div class="text-white">Online</div>
                                    </div>
                                    <!-- ./col -->
                                    <div class="col-4 text-center">
                                        <input type="text" class="knob" data-readonly="true" value="30"
                                            data-width="60" data-height="60" data-fgColor="#39CCCC">

                                        <div class="text-white">In-Store</div>
                                    </div>
                                    <!-- ./col -->
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- /.card-footer -->
                        </div>
                        <!-- /.card -->

                        <!-- Calendar -->
                        <div class="card bg-gradient-success">
                            <div class="card-header border-0">

                                <h3 class="card-title">
                                    <i class="far fa-calendar-alt"></i>
                                    Calendar
                                </h3>
                                <!-- tools card -->
                                <div class="card-tools">
                                    <!-- button with a dropdown -->
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm dropdown-toggle"
                                            data-toggle="dropdown" data-offset="-52">
                                            <i class="fas fa-bars"></i>
                                        </button>
                                        <div class="dropdown-menu" role="menu">
                                            <a href="#" class="dropdown-item">Add new event</a>
                                            <a href="#" class="dropdown-item">Clear events</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="#" class="dropdown-item">View calendar</a>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <!-- /. tools -->
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body pt-0">
                                <!--The calendar -->
                                <div id="calendar" style="width: 100%"></div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </section>
                    <!-- right col -->
                </div>
                <!-- /.row (main row) -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->

        <div class="modal fade" id="modalInfoStockHabis" tabindex="-1" role="dialog"
            aria-labelledby="modalInfoStockHabisLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInfoStockHabisLabel">Stock Habis Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="dt-stock-habis" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Kode</th>
                                    <th>Item Nama</th>
                                    <th>Min Stock</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="btnBatal">Batal</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalInfoSales" tabindex="-1" role="dialog" aria-labelledby="modalInfoSalesLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInfoSalesLabel">Sales Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="dt-sales" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Kode</th>
                                    <th>Item Nama</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="btnBatal">Batal</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalInfoSumSales" tabindex="-1" role="dialog"
            aria-labelledby="modalInfoSumSalesLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInfoSumSalesLabel">Sales Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="dt-sum-sales" class="table table-bordered table-striped w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Item Nama</th>
                                    <th>Satuan</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Beli</th>
                                    <th class="text-end">Harga Jual</th>
                                    <th class="text-end">Total Beli</th>
                                    <th class="text-end">Total Jual</th>
                                </tr>
                            </thead>

                            <tbody></tbody>

                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="6" class="text-end">
                                        GRAND TOTAL
                                    </th>

                                    <th id="grand_total_beli" class="text-end"></th>

                                    <th id="grand_total_jual" class="text-end"></th>
                                </tr>

                                <tr class="table-warning">
                                    <th colspan="7" class="text-end">
                                        PROFIT
                                    </th>

                                    <th id="grand_profit" class="text-end"></th>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="btnBatal">Batal</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalInfoSumPiutang" tabindex="-1" role="dialog"
            aria-labelledby="modalInfoSumPiutangLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInfoSumPiutangLabel">Piutang Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="dt-sum-piutang" class="table table-bordered table-striped w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Item Nama</th>
                                    <th>Satuan</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Beli</th>
                                    <th class="text-end">Harga Jual</th>
                                    <th class="text-end">Total Beli</th>
                                    <th class="text-end">Total Jual</th>
                                </tr>
                            </thead>

                            <tbody></tbody>

                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="6" class="text-end">
                                        GRAND TOTAL
                                    </th>

                                    <th id="grand_total_beli_piutang" class="text-end"></th>

                                    <th id="grand_total_jual_piutang" class="text-end"></th>
                                </tr>

                                <tr class="table-warning">
                                    <th colspan="7" class="text-end">
                                        PIUTANG
                                    </th>

                                    <th id="grand_piutang" class="text-end"></th>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="btnBatal">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            async function realtimeCountStockHabis() {
                const baseUrl = "{{ url('/') }}";

                try {
                    let res = await fetch(baseUrl + '/countBarangHabis');
                    let data = await res.json();

                    document.getElementById('jumlahStockHabis').innerText = data.stockHabis;
                } catch (e) {}

                // langsung panggil lagi (loop)
                setTimeout(realtimeCountStockHabis, 3000);
            }

            async function realtimeCountSales() {
                const baseUrl = "{{ url('/') }}";

                try {
                    let res = await fetch(baseUrl + '/countSales');
                    let data = await res.json();

                    document.getElementById('jumlahSales').innerText = data.sales;
                } catch (e) {}

                // langsung panggil lagi (loop)
                setTimeout(realtimeCountSales, 3000);
            }

            async function realTimeSumSales() {
                const baseUrl = "{{ url('/') }}";

                try {
                    let res = await fetch(baseUrl + '/sumSales');
                    let data = await res.json();

                    document.getElementById('sumSales').innerHTML =
                        "<sup style='font-size:20px'>Rp.</sup> " +
                        formatInputRibuan(data.sumsales);
                } catch (e) {}

                // langsung panggil lagi (loop)
                setTimeout(realTimeSumSales, 3000);
            }

            async function realTimeSumPiutang() {
                const baseUrl = "{{ url('/') }}";

                try {
                    let res = await fetch(baseUrl + '/sumPiutang');
                    let data = await res.json();

                    document.getElementById('sumPiutang').innerHTML =
                        "<sup style='font-size:20px'>Rp.</sup> " +
                        formatInputRibuan(data.sumpiutang);
                } catch (e) {}

                // langsung panggil lagi (loop)
                setTimeout(realTimeSumPiutang, 3000);
            }

            realtimeCountStockHabis();
            realtimeCountSales();
            realTimeSumSales();
            realTimeSumPiutang();

            $(document).ready(function() {

                let tableStockHabis;
                $('#modalInfoStockHabis').on('shown.bs.modal', function() {

                    if ($.fn.DataTable.isDataTable('#dt-stock-habis')) {
                        tableStockHabis.ajax.reload(); // kalau sudah ada, reload saja
                    } else {
                        tableStockHabis = $("#dt-stock-habis").DataTable({
                            dom: 'Bfrtip',
                            responsive: true,
                            searching: true,
                            ordering: true,
                            info: true,
                            lengthChange: false,
                            autoWidth: false,
                            processing: true,

                            buttons: [{
                                    extend: 'csv',
                                    filename: 'info_stock_habis',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    }
                                },
                                {
                                    extend: 'excel',
                                    filename: 'info_stock_habis',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    filename: 'info_stock_habis',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    },
                                    customize: function(doc) {
                                        doc.footer = function(currentPage, pageCount) {
                                            return {
                                                text: 'Halaman ' + currentPage + ' dari ' +
                                                    pageCount,
                                                alignment: 'center',
                                                margin: [0, 10, 0, 0]
                                            };
                                        };


                                        var body = doc.content[1].table.body;

                                        // Nomor urut + teks center
                                        for (var i = 1; i < body.length; i++) {
                                            if (typeof body[i][0] === 'object') {
                                                body[i][0].text = (i).toString();
                                            } else {
                                                body[i][0] = {
                                                    text: (i).toString()
                                                };
                                            }

                                            body[i][0].alignment = 'center';
                                        }

                                        body[0][0].alignment =
                                            'center'; // Header nomor urut center

                                        // Atur lebar kolom otomatis
                                        doc.content[1].table.widths = Array(body[0]
                                                .length)
                                            .fill(
                                                '*');

                                        // Tambahkan garis pembatas
                                        doc.content[1].layout = {
                                            hLineWidth: function() {
                                                return 0.5;
                                            },
                                            vLineWidth: function() {
                                                return 0.5;
                                            },
                                            hLineColor: function() {
                                                return '#aaa';
                                            },
                                            vLineColor: function() {
                                                return '#aaa';
                                            },
                                        };
                                    }
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    }
                                },
                            ],

                            ajax: "{{ url('/stock-habis') }}",

                            columns: [{
                                    data: null,
                                    name: 'no',
                                    render: function(data, type, row, meta) {
                                        return meta.row + meta.settings._iDisplayStart + 1;
                                    },
                                    className: 'text-center',
                                },
                                {
                                    data: 'item_kode',
                                    name: 'item_kode'
                                },
                                {
                                    data: 'item_nama',
                                    name: 'item_nama'
                                },
                                {
                                    data: 'min_stock',
                                    name: 'min_stock'
                                },
                                {
                                    data: 'stock',
                                    name: 'stock'
                                }
                            ],
                        });
                        tableStockHabis.buttons().container().appendTo(
                            '#dt-stock-habis_wrapper .col-md-6:eq(0)');
                    }

                });

                let tableSales;
                $('#modalInfoSales').on('shown.bs.modal', function() {

                    if ($.fn.DataTable.isDataTable('#dt-sales')) {
                        tableSales.ajax.reload(); // kalau sudah ada, reload saja
                    } else {
                        tableSales = $("#dt-sales").DataTable({
                            dom: 'Bfrtip',
                            responsive: true,
                            searching: true,
                            ordering: true,
                            info: true,
                            lengthChange: false,
                            autoWidth: false,
                            processing: true,

                            buttons: [{
                                    extend: 'csv',
                                    filename: 'info_sales',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    }
                                },
                                {
                                    extend: 'excel',
                                    filename: 'info_sales',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    filename: 'info_sales',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    },
                                    customize: function(doc) {
                                        doc.footer = function(currentPage, pageCount) {
                                            return {
                                                text: 'Halaman ' + currentPage + ' dari ' +
                                                    pageCount,
                                                alignment: 'center',
                                                margin: [0, 10, 0, 0]
                                            };
                                        };


                                        var body = doc.content[1].table.body;

                                        // Nomor urut + teks center
                                        for (var i = 1; i < body.length; i++) {
                                            if (typeof body[i][0] === 'object') {
                                                body[i][0].text = (i).toString();
                                            } else {
                                                body[i][0] = {
                                                    text: (i).toString()
                                                };
                                            }

                                            body[i][0].alignment = 'center';
                                        }

                                        body[0][0].alignment =
                                            'center'; // Header nomor urut center

                                        // Atur lebar kolom otomatis
                                        doc.content[1].table.widths = Array(body[0].length)
                                            .fill(
                                                '*');

                                        // Tambahkan garis pembatas
                                        doc.content[1].layout = {
                                            hLineWidth: function() {
                                                return 0.5;
                                            },
                                            vLineWidth: function() {
                                                return 0.5;
                                            },
                                            hLineColor: function() {
                                                return '#aaa';
                                            },
                                            vLineColor: function() {
                                                return '#aaa';
                                            },
                                        };
                                    }
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: ':not(.not-export)'
                                    }
                                },
                            ],

                            ajax: "{{ url('/salesInfo') }}",

                            columns: [{
                                    data: null,
                                    name: 'no',
                                    render: function(data, type, row, meta) {
                                        return meta.row + meta.settings._iDisplayStart + 1;
                                    },
                                    className: 'text-center',
                                },
                                {
                                    data: 'item_kode',
                                    name: 'item_kode'
                                },
                                {
                                    data: 'item_nama',
                                    name: 'item_nama'
                                },
                                {
                                    data: 'qty',
                                    name: 'qty'
                                }
                            ],
                        });
                        tableSales.buttons().container().appendTo('#dt-sales_wrapper .col-md-6:eq(0)');
                    }

                });

                let tableSumSales;

                $('#modalInfoSumSales').on('shown.bs.modal', function() {

                    if ($.fn.DataTable.isDataTable('#dt-sum-sales')) {

                        tableSumSales.ajax.reload();

                    } else {

                        tableSumSales = $("#dt-sum-sales").DataTable({

                            dom: 'Bfrtip',

                            responsive: true,
                            searching: true,
                            ordering: true,
                            info: true,
                            paging: false,
                            lengthChange: false,
                            autoWidth: false,
                            processing: true,
                            serverSide: true,

                            buttons: [{
                                    extend: 'csv',
                                    filename: 'info_sales'
                                },
                                {
                                    extend: 'excel',
                                    filename: 'info_sales'
                                },
                                {
                                    extend: 'pdf',
                                    filename: 'info_sales'
                                },
                                {
                                    extend: 'print'
                                }
                            ],

                            ajax: {
                                url: "{{ url('/sumSalesInfo') }}",

                                dataSrc: function(json) {

                                    $('#grand_total_beli').html(
                                        'Rp ' + Number(json.grand_total_beli)
                                        .toLocaleString('id-ID')
                                    );

                                    $('#grand_total_jual').html(
                                        'Rp ' + Number(json.grand_total_jual)
                                        .toLocaleString('id-ID')
                                    );

                                    $('#grand_profit').html(
                                        'Rp ' + Number(json.grand_profit)
                                        .toLocaleString('id-ID')
                                    );

                                    return json.data;
                                }
                            },

                            columns: [{
                                    data: null,
                                    className: 'text-center',
                                    render: function(data, type, row, meta) {
                                        return meta.row + 1;
                                    }
                                },

                                {
                                    data: 'item_nama'
                                },

                                {
                                    data: 'satuan',
                                    className: 'text-center'
                                },

                                {
                                    data: 'qty',
                                    className: 'text-center'
                                },

                                {
                                    data: 'harga_beli',
                                    className: 'text-end'
                                },

                                {
                                    data: 'harga_jual',
                                    className: 'text-end'
                                },

                                {
                                    data: 'total_beli',
                                    className: 'text-end fw-bold'
                                },

                                {
                                    data: 'total_jual',
                                    className: 'text-end fw-bold'
                                }
                            ],

                        });

                        tableSumSales.buttons().container()
                            .appendTo('#dt-sum-sales_wrapper .col-md-6:eq(0)');
                    }
                });

                let tableSumPiutang;

                $('#modalInfoSumPiutang').on('shown.bs.modal', function() {

                    if ($.fn.DataTable.isDataTable('#dt-sum-piutang')) {

                        tableSumPiutang.ajax.reload();

                    } else {

                        tableSumPiutang = $("#dt-sum-piutang").DataTable({

                            dom: 'Bfrtip',

                            responsive: true,
                            searching: true,
                            ordering: true,
                            info: true,
                            paging: false,
                            lengthChange: false,
                            autoWidth: false,
                            processing: true,
                            serverSide: true,

                            buttons: [{
                                    extend: 'csv',
                                    filename: 'info_sales'
                                },
                                {
                                    extend: 'excel',
                                    filename: 'info_sales'
                                },
                                {
                                    extend: 'pdf',
                                    filename: 'info_sales'
                                },
                                {
                                    extend: 'print'
                                }
                            ],

                            ajax: {
                                url: "{{ url('/sumPiutangInfo') }}",

                                dataSrc: function(json) {

                                    $('#grand_total_beli_piutang').html(
                                        'Rp ' + Number(json.grand_total_beli)
                                        .toLocaleString('id-ID')
                                    );

                                    $('#grand_total_jual_piutang').html(
                                        'Rp ' + Number(json.grand_total_jual)
                                        .toLocaleString('id-ID')
                                    );

                                    $('#grand_piutang').html(
                                        'Rp ' + Number(json.grand_piutang)
                                        .toLocaleString('id-ID')
                                    );

                                    return json.data;
                                }
                            },

                            columns: [{
                                    data: null,
                                    className: 'text-center',
                                    render: function(data, type, row, meta) {
                                        return meta.row + 1;
                                    }
                                },

                                {
                                    data: 'item_nama'
                                },

                                {
                                    data: 'satuan',
                                    className: 'text-center'
                                },

                                {
                                    data: 'qty',
                                    className: 'text-center'
                                },

                                {
                                    data: 'harga_beli',
                                    className: 'text-end'
                                },

                                {
                                    data: 'harga_jual',
                                    className: 'text-end'
                                },

                                {
                                    data: 'total_beli',
                                    className: 'text-end fw-bold'
                                },

                                {
                                    data: 'total_jual',
                                    className: 'text-end fw-bold'
                                }
                            ],

                        });

                        tableSumPiutang.buttons().container()
                            .appendTo('#dt-sum-piutang_wrapper .col-md-6:eq(0)');
                    }
                });
            });
        </script>
    @endpush
@endsection
