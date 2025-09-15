<?php $page_id = "orders"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once('head.php'); ?>

    <style>
    .subject-done {
        color: green;
        text-decoration: line-through;
    }

    .subject-pending {
        color: red;
    }
    </style>

    <script src="<?= base_url();?>assets/jsgrid/jquery.min.js"></script>
    <link type="text/css" rel="stylesheet" href="<?= base_url();?>assets/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="<?= base_url();?>assets/jsgrid/jsgrid-theme.min.css" />

    <style>
    .jsgrid-cell {
        overflow: hidden;
        word-wrap: break-word;
    }

    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }
    </style>

</head>

<body>
    <!-- begin #page-loader -->
    <div id="page-loader" class="fade in"><span class="spinner"></span></div>
    <!-- end #page-loader -->

    <!-- begin #page-container -->
    <div id="page-container" class="fade page-sidebar-minified page-sidebar-fixed page-header-fixed"
        style="padding-top:0;">
        <!-- begin #header -->

        <!-- end #header -->

        <!-- begin #sidebar -->
        <?php require_once('sidebar.php'); ?>
        <!-- end #sidebar -->

        <!-- begin #content -->
        <div id="content" class="content">
            <ol class="breadcrumb pull-right">
                <li><a href="javascript:;">Home</a></li>
                <li class="active">Subject</li>
            </ol>
            <h1 class="page-header">Manage Orders</h1>

            <!-- begin row -->
            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-inverse" data-sortable-id="ui-widget-1">
                        <div class="panel-heading">
                            <div class="panel-heading-btn">
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default"
                                    data-click="panel-expand" data-original-title="" title=""><i
                                        class="fa fa-expand"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success"
                                    data-click="panel-reload" data-original-title="" title=""><i
                                        class="fa fa-repeat"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning"
                                    data-click="panel-collapse" data-original-title="" title=""><i
                                        class="fa fa-minus"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-info"
                                    data-click="panel-home"><i class="fa fa-home"></i></a>
                            </div>
                            <h4 class="panel-title">Manage Orders</h4>
                        </div>
                        <div class="panel-body">

                            <form class="form-inline" data-parsley-validate="true" id="form-orders" method="POST" onsubmit="return false;">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Date" data-date-format="dd-M-yyyy" id="order_date" name="order_date" value="<?php echo date('d-M-Y') ?>">
                                    <!-- <input type="email" class="form-control" id="exampleInputEmail2" placeholder="Enter email"> -->
                                </div>
                                <div class="form-group">
                                    <input type="number" id="order-cmobile" name="order-cmobile" class="form-control" placeholder="Mobile" data-parsley-required="true" data-parsley-type="number" maxlength="10"/>
                                </div>
                                <div class="form-group">
                                    <input type="text" id="order-cname" name="order-cname" class="form-control" placeholder="Name"  />
                                </div>
                                <div class="form-group">
                                    <input type="text" id="order-cemail" name="order-cemail" class="form-control" placeholder="Email"  />
                                </div>
                                <div class="form-group">
                                    <select class="form-control selectpicker" id="university_id" name="university_id" data-size="10" data-live-search="true" data-style="btn-white" onchange="getJsGridData(this.value)">
                                        <option selected value="X">Select University</option>
                                        <?php
                                        $result=$this->db->get('university');
                                        foreach($result->result() as $row)
                                        {?>
                                        <option value="<?php echo $row->university_id; ?>"><?php echo $row->university_name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select class="form-control" id="session_id" name="session_id">
                                        <option value="" selected>Select Session</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select onchange="checkWorktype(this.value)" class="form-control" id="worktype" name="worktype">
                                        <option value="" selected>Select Worktype</option>
                                    </select>
                                </div>
                                <div class="form-group" style="width:18%;">
                                    <ul id="order-subjects" name="order-subjects" placeholder="Enter Subjects"></ul>
                                    <input type="hidden" id="subjects" name="subjects"/>
                                </div>

                                <hr style="margin-top: 10px;margin-bottom: 10px;"/>

                                <div class="form-group" style="width:24%;">
                                    <textarea class="form-control" id="order-remark" name="order-remark" placeholder="Enter Remark or Any info required for order details" rows="3" style="width:100%;"></textarea>
                                </div>
                                <div class="form-group">
                                    <input type="text" id="order_amt" name="order_amt" value="0" class="form-control" placeholder="Order Amt"  />
                                </div>
                                <div class="form-group">
                                    <input type="text" id="paid_amt" name="paid_amt" value="0" class="form-control" placeholder="Paid Amt"  />
                                </div>
                                <div class="form-group">
                                    <input type="text" id="course" name="course" class="form-control" placeholder="Course" />
                                </div>
                                <div class="form-group">
                                    <input type="text" id="semester" name="semester" class="form-control" placeholder="Semester"/>
                                </div>
                                <div class="form-group">
                                    <input type="text" id="specialization" name="specialization" class="form-control" placeholder="Specialization"  />
                                </div>
                                
								<!-- <div class="checkbox m-r-10">
									<label>
										<input type="checkbox"> Remember me
									</label>
								</div>
								<button type="submit" class="btn btn-sm btn-primary m-r-5">Sign in</button>-->
								<button type="submit" class="btn btn-sm btn-default">Save</button> 
							</form>
                            <hr>

                            <div id="jsGrid"></div>

                            <!-- #modal-dialog -->
                            <div class="modal fade" id="modal-dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
											<h4 class="modal-title">Add University Details</h4>
										</div>
										<div class="modal-body">
											<form data-parsley-validate="true" id="form-universitydata" method="POST" onsubmit="return false;">
                                                <fieldset>
                                                    <div class="form-group">
                                                        <div class="col-md-5">
                                                            <label for="exampleInputEmail1">Choose University</label>
                                                            <select class="form-control selectpicker" data-size="10" data-live-search="true" data-parsley-required="true" data-style="btn-white" id="modal-university_id" name="modal-university_id" onchange="ModalData(this.value)">
                                                                <option selected value="X">Select University</option>
                                                                <?php
                                                                $result=$this->db->get('university');
                                                                foreach($result->result() as $row)
                                                                {?>
                                                                <option value="<?php echo $row->university_id; ?>"><?php echo $row->university_name; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <input type="hidden" id="order_id" name="order_id"/>

                                                        <div class="col-md-3">
                                                            <label for="modal-worktype">Select Session</label>
                                                            <select class="form-control" id="modal-session_id" name="modal-session_id" data-parsley-required="true">
                                                                <option value="" selected>Select Session</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label for="modal-worktype">Select Worktype</label>
                                                            <select onchange="checkWorktype(this.value)" class="form-control" id="modal-worktype" name="modal-worktype" data-parsley-required="true">
                                                            <option value="" selected>Select Worktype</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <label for="modal-order-subjects">Select Subject</label>
                                                            <select class="multiple-select2 form-control" multiple="multiple" id="modal-order-subjects" name="modal-order-subjects[]" data-parsley-required="true">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </fieldset><hr/>
                                                <a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Close</a>
                                                <button type="submit" class="btn btn-sm btn-success" style="float: right">Update</button>
                                            </form>




                            <hr>

                            <div id="jsGrid"></div>

                            <!-- #modal-dialog -->
                            <div class="modal fade" id="modal-dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-hidden="true">×</button>
                                            <h4 class="modal-title">Add University Details</h4>
                                        </div>
                                        <div class="modal-body">
                                            <form data-parsley-validate="true" id="form-universitydata" method="POST"
                                                onsubmit="return false;">
                                                <fieldset>
                                                    <div class="form-group">
                                                        <div class="col-md-5">
                                                            <label for="exampleInputEmail1">Choose University</label>
                                                            <select class="form-control selectpicker" data-size="10" data-live-search="true" 
                                                                data-parsley-required="true" data-style="btn-white" id="modal-university_id" 
                                                                name="modal-university_id" onchange="ModalData(this.value)">
                                                          
                                                                <option selected value="X">Select University</option>
                                                                <?php
                                                                $result=$this->db->get('university');
                                                                foreach($result->result() as $row)
                                                                {?>
                                                                <option value="<?php echo $row->university_id; ?>">
                                                                    <?php echo $row->university_name; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <input type="hidden" id="order_id" name="order_id" />

                                                        <div class="col-md-3">
                                                            <label for="modal-worktype">Select Session</label>
                                                            <select class="form-control" id="modal-session_id"
                                                                name="modal-session_id" data-parsley-required="true">
                                                                <option value="" selected>Select Session</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label for="modal-worktype">Select Worktype</label>
                                                            <select onchange="checkWorktype(this.value)" class="form-control" id="modal-worktype"
                                                                name="modal-worktype" data-parsley-required="true">
                                                                <option value="" selected>Select Worktype</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <label for="modal-order-subjects">Select Subject</label>
                                                            <select class="multiple-select2 form-control"
                                                                multiple="multiple" id="modal-order-subjects"
                                                                name="modal-order-subjects[]"
                                                                data-parsley-required="true">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                                <hr />
                                                <a href="javascript:;" class="btn btn-sm btn-white"
                                                    data-dismiss="modal">Close</a>
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    style="float: right">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end #content -->

        <!-- begin scroll to top btn -->
        <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade"
            data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
        <!-- end scroll to top btn -->
    </div>
    <!-- end page container -->


    <!-- #modal-username-start -->
	<div class="modal fade" id="modal-start">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">Enter Portal's Username & Password</h4>
				</div>
				<div class="modal-body">
					<form  method="POST" action="" id="username_info" data-parsley-validate="true" onsubmit="return false;" >
                        <fieldset>
                            <div class="row">
                                <div class="col-md-12" id="loader" style="margin-left: 48%; display:none;">
                                    <div class="loader"></div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Enter Username</label>
                                        <input type="text" class="form-control" name="username" data-parsley-required="true" placeholder="Enter Username" required />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Enter Password</label>
                                        <input type="text" class="form-control" name="password" data-parsley-required="true" placeholder="Enter Password" required />
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" id="savebtn" class="btn btn-sm btn-inverse m-r-5">Save</button>
                            </div>
                        </fieldset>
                    </form>
				</div>
			</div>
		</div>
	</div>

    <!-- ================== BEGIN JS ================== -->
    <script src="<?= base_url();?>assets/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
    <!-- <script src="<?= base_url();?>assets/plugins/jquery-ui/ui/minified/jquery-ui.min.js"></script> -->
    <script src="<?= base_url();?>assets/plugins/jquery-ui/jquery-ui.js"></script>
    <script src="<?= base_url();?>assets/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!--[if lt IE 9]>
        <script src="<?= base_url();?>assets/crossbrowserjs/html5shiv.js"></script>
        <script src="<?= base_url();?>assets/crossbrowserjs/respond.min.js"></script>
        <script src="<?= base_url();?>assets/crossbrowserjs/excanvas.min.js"></script>
    <![endif]-->
    <script src="<?= base_url();?>assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/jquery-cookie/jquery.cookie.js"></script>
    <!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="<?= base_url();?>assets/plugins/parsley/dist/parsley.js"></script>
    <script src="<?= base_url();?>assets/plugins/gritter/js/jquery.gritter.js"></script>
    <script src="<?= base_url();?>assets/js/ui-modal-notification.demo.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/sparkline/jquery.sparkline.js"></script>
    <!-- <script src="<?= base_url();?>assets/plugins/DataTables/js/jquery.dataTables.js"></script> -->
    <script src="<?= base_url();?>assets/js/table-manage-default.demo.min.js"></script>
    <script src="<?= base_url();?>assets/jsgrid/jsgrid.min.js"></script>

    <script src="<?= base_url();?>assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="<?= base_url();?>assets/plugins/ionRangeSlider/js/ion-rangeSlider/ion.rangeSlider.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/masked-input/masked-input.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/password-indicator/js/password-indicator.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-combobox/js/bootstrap-combobox.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput-typeahead.js"></script>
    <script src="<?= base_url();?>assets/plugins/jquery-tag-it/js/tag-it.min.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-daterangepicker/moment.js"></script>
    <script src="<?= base_url();?>assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="<?= base_url();?>assets/plugins/select2/dist/js/select2.min.js"></script>
    <script src="<?= base_url();?>assets/js/form-plugins.demo.min.js"></script>

    <script src="<?= base_url();?>assets/js/customjs.js"></script>
    <script src="<?= base_url();?>assets/js/punch-order.js"></script>
    <script src="<?= base_url();?>assets/js/apps.min.js"></script>
    <!-- ================== END JS ================== -->

    <script>
    const BASE_URL = "<?= base_url(); ?>";

    function checkWorktype(univ_worktype_id){
        if (univ_worktype_id) {
                $.ajax({
                    url: "<?php echo base_url('Actiongetdata/order_punch_worktype_check'); ?>",
                    type: "POST",
                    data: { univ_worktype_id: univ_worktype_id },
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'ok' && (res.worktype_id == 2 || res.worktype_id == 4)) {
                            $('#modal-start').modal('show');
                        }
                    }
                });
        }
    }

    const intervalId = setInterval(() => {
        const inputElement = document.getElementById('worktype');
        
        if (inputElement && inputElement.value) {
            checkWorktype(inputElement.value);
            clearInterval(intervalId);
        }
    }, 1000);

    const intervalId_two = setInterval(() => {
        const inputElement = document.getElementById('modal-worktype');
        
        if (inputElement && inputElement.value) {
            checkWorktype(inputElement.value);
            clearInterval(intervalId_two);
        }
    }, 1000);

    $(document).ready(function() {

        App.init();
        Notification.init();
        TableManageDefault.init();
        FormPlugins.init();
        $("#order-subjects").tagit();


        var MyDateField = function(config) {
            jsGrid.Field.call(this, config);
        };
        MyDateField.prototype = new jsGrid.Field({
            sorter: function(date1, date2) {
                return moment(date1) - moment(date2);
            },
            itemTemplate: function(value) {
                return moment(value).locale('en').format('DD-MMM-YY');
            },

            // editTemplate: function (value) {
            //     return moment(value).locale('en').format('DD-MMM-YY');
            // },

            // editValue: function (value) {
            //     return moment(value).locale('en').format('DD-MMM-YY');
            // }
        });
        jsGrid.fields.date = MyDateField;


        const inputElement = document.getElementById('order-cmobile');
        inputElement.addEventListener('keyup', handleInputEvent);
        inputElement.addEventListener('blur', handleInputEvent);

        function handleInputEvent(event) {
            // alert(event.type)
            switch (event.type) {
                case 'keyup': // Your code to handle keyup event
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: BASE_URL + "Actiongetdata/getCustomerData",
                        data: {
                            'cmobile': inputElement.value,
                            'table': 'orders',
                            'event': event.type
                        },
                        success: function(data) {
                            json = JSON.parse(JSON.stringify(data));
                            $("#order-cmobile").autocomplete({
                                source: json.split(',').map((tag) => tag.trim()).filter((
                                    tag) => tag.length !== 0)
                            })
                        }
                    });
                    break;

                case 'blur': // Your code to handle blur event
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: BASE_URL + "Actiongetdata/getCustomerData",
                        data: {
                            'cmobile': inputElement.value,
                            'table': 'orders',
                            'event': event.type
                        },
                        success: function(response) {
                            json = JSON.parse(JSON.stringify(response));
                            console.log(json);
                            var res = response.split("#");
                            $("#order-cname").val(res[0].trim());
                            $("#order-cemail").val(res[1].trim());
                        }
                    });
                    break;
            }
        }

        $("#jsGrid").jsGrid({
            width: "100%",
            height: "auto",

            autoload: true,
            heading: true,
            filtering: false,
            inserting: false,
            editing: false,
            selecting: true,
            sorting: true,
            paging: true,
            pageLoading: true,

            confirmDeleting: true,
            deleteConfirm: "Are you sure?",
            // data: clients,

            rowDoubleClick: function(args) {
                $("#jsGrid").jsGrid("option", 'editing', true);
            },
            // rowClick: function(args) {
            //     console.log(args.itemIndex);
            // },

            controller: {
                loadData: function(filter) {
                    var def = $.Deferred();
                    $.ajax({
                        url: "<?= base_url();?>actiongetdata/orders",
                        dataType: "json"
                    }).done(function(response) {
                        var startIndex = (filter.pageIndex - 1) * filter.pageSize;
                        var da = {
                            data: response.slice(startIndex, startIndex + filter
                                .pageSize),
                            itemsCount: response.length
                        };
                        def.resolve(da);
                    });
                    return def.promise();
                },
                    // keep these here (top-level inside controller), not inside any function
                    updateItem: $.noop,
                    deleteItem: $.noop
                },
        
                fields:
                [
                    { name: "order_date", title: "Date", type: "date",width: 75},

                    {
                        name: "university_name",
                        title: "University",
                        type: "text",
                        width: 120,
                        readOnly: true,
                        itemTemplate: function (value, item) {
                            if (!value) {
                            // show a link to open modal when university is empty
                            return $('<a href="#modal-dialog" data-toggle="modal">')
                                .attr("data-book-id", item.order_id)
                                .text("Select");
                            }
                            return value;
                        }
                    },

                    { name: "session_name", title: "Session", type: "text", width: 80, readOnly: true },
                    { name: "cmobile", title: "Mobile", type: "number", width: 90, readOnly: true },

                    {
                        name: "cname",
                        title: "Name",
                        type: "text",
                        width: 100,
                        editTemplate: function (value, item) {
                            var $inp = $('<input type="text">').val(value);
                            $inp.on("change", function () {
                            updatejsGridData("cname", this.value, item.order_id);
                            });
                            return $inp;
                        }
                    },

                    {
                        name: "cemail",
                        title: "Email",
                        type: "text",
                        width: 100,
                        editTemplate: function (value, item) {
                            var $inp = $('<input type="text">').val(value);
                            $inp.on("change", function () {
                            updatejsGridData("cemail", this.value, item.order_id);
                            });
                            return $inp;
                        }
                    },

                    {
                        name: "subjects",
                        title: "Subjects",
                        type: "textarea",
                        width: 200,
                        readOnly: true,
                        itemTemplate: function (value, item) {
                            var $container = $("<div>");
                            if (item.subjects) {
                            item.subjects.split(",").forEach(function (sub) {
                                sub = sub.trim();
                                var $s = $("<span>");
                                if (sub === "check") $s.addClass("subject-done");
                                $s.text(sub + ", ");
                                $container.append($s);
                            });
                            }
                            return $container;
                        }
                    },

                    {
                        name: "remark",
                        title: "Remark",
                        type: "textarea",
                        editTemplate: function (value, item) {
                            var $inp = $('<input type="text">').val(value);
                            $inp.on("change", function () {
                            updatejsGridData("remark", this.value, item.order_id);
                            });
                            return $inp;
                        }
                    },
                {
                name: "order_amt",
                title: "Order Amt",
                type: "number",
                width: 75,
                editTemplate: function (value, item) {
                    var $inp = $('<input type="text">').val(value);
                    if (Number(value) >= Number(item.paid_amt)) {
                    $inp.on("change", function () {
                        updatejsGridData("order_amt", this.value, item.order_id);
                    });
                    } else {
                    // if invalid, keep it non-updating; you can also show a tooltip
                    }
                    return $inp;
                }
                },

                {
                name: "paid_amt",
                title: "Paid Amt",
                type: "number",
                width: 75,
                editTemplate: function (value, item) {
                    var $inp = $('<input type="text">').val(value);
                    $inp.on("change", function () {
                    updatejsGridData("paid_amt", this.value, item.order_id);
                    });
                    return $inp;
                }
                },

                { name: "due_amt", title: "Due Amt", type: "number", width: 75, readOnly: true },
                
                {
                    type: "control",
                    editButton: true,
                    deleteButton: false,
                    width: 100,
                    itemTemplate: function (value, item) {
                        var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);

                        var $userBtn = $('<button class="btn btn-success btn-icon btn-circle" title="User">')
                        .append($("<i>").addClass("fa fa-user"))
                        .on("click", function (e) {
                            e.stopPropagation();
                            alert("ID: " + item.order_id);
                        });

                        var $banBtn = $('<button class="btn btn-danger btn-xs" title="Ban">')
                        .append($("<i>").addClass("fa fa-ban"))
                        .on("click", function (e) {
                            e.stopPropagation();
                            alert("ID: " + item.id);
                        });

                        return $('<div class="btn-toolbar">').append($userBtn, $banBtn);
                    }
                    }
             ]
        });


        $('#form-orders').submit(function(e) {
            if ($("#form-orders").parsley().isValid()) {
                // if ($("#order-subjects").tagit("assignedTags").length !== 0)
                $("#subjects").val($('#order-subjects').tagit('assignedTags'));

                ins_data("form-orders", "<?= base_url();?>actioninsert/punch_order");
            } else
                e.preventDefault();
        });

        //triggered when modal is about to be shown
        $('#modal-dialog').on('show.bs.modal', function(e) {
            //get data-id attribute of the clicked element
            var bookId = $(e.relatedTarget).data('book-id');
            //populate the textbox
            $(e.currentTarget).find('input[name="order_id"]').val(bookId);
        });


        $('#form-universitydata').submit(function(e)
        {
            if($("#form-universitydata").parsley().isValid())
                update_data("form-universitydata","<?= base_url();?>actionupdate/orders");
            else
                e.preventDefault();
        });

        $('#username_info').submit(function(e)
        {
            if($("#username_info").parsley().isValid())
                update_data_order("username_info","<?= base_url();?>actionupdate/punch_order_username_set");
            else
                e.preventDefault();
        });




    });
    </script>
</body>

</html>
