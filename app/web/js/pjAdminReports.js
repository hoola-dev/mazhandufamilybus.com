var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var 
			$frmBusReport = $('#frmBusReport'),
			$frmRouteReport = $('#frmRouteReport'),
			datepicker = ($.fn.datepicker !== undefined),
			tabs = ($.fn.tabs !== undefined),
			$tabs = $("#tabs"),
			tOpt = {
				select: function (event, ui) {
					$(":input[name='tab_id']").val(ui.panel.id);
				}
			};
		
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs(tOpt);
		}
		
		if ($frmBusReport.length > 0) {
			$frmBusReport.validate({
				rules: {
					"bus_period": {
						required: function(){
							if($('#bus_time_scale').val() == 'period')
							{
								if($('#bus_start_date').val() == '' || $('#bus_end_date').val() == '')
								{
									return true;
								}else{
									return false;
								}
							}else{
								return false;
							}
						}
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ""
			});
		}
		
		if ($frmRouteReport.length > 0) {
			$frmRouteReport.validate({
				rules: {
					"route_period": {
						required: function(){
							if($('#route_time_scale').val() == 'period')
							{
								if($('#route_start_date').val() == '' || $('#route_end_date').val() == '')
								{
									return true;
								}else{
									return false;
								}
							}else{
								return false;
							}
						}
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ""
			});
		}
		
		$(document).on("focusin", '.pj-grid-field', function(e){
			$(this).select();
		}).on("focusin", ".datepick", function (e) {
			var $this = $(this);
			$this.datepicker({
				firstDay: $this.attr("rel"),
				dateFormat: $this.attr("rev")
			});
		}).on("click", ".pj-form-field-icon-date", function (e) {
			var $dp = $(this).parent().siblings("input[type='text']");
			if ($dp.hasClass("hasDatepicker")) {
				$dp.datepicker("show");
			} else {
				$dp.trigger("focusin").datepicker("show");
			}
		}).on("change", "#bus_time_scale", function (e) {
			if($(this).val() == 'period')
			{
				$('.boxBusPeriod').css('display', 'block');
			}else{
				$('#bus_start_date').val("");
				$('#bus_end_date').val("");
				$('.boxBusPeriod').css('display', 'none');
			}
		}).on("change", "#route_time_scale", function (e) {
			if($(this).val() == 'period')
			{
				$('.boxRoutePeriod').css('display', 'block');
			}else{
				$('#route_start_date').val("");
				$('#route_end_date').val("");
				$('.boxRoutePeriod').css('display', 'none');
			}
		}).on("change", "#bus_route_id", function (e) {
			$.get("index.php?controller=pjAdminReports&action=pjActionGetBuses&route_id=" + $(this).val()).done(function (data) {
				$('#bus_container').html(data);
			});
		}).on("click", ".bs-print-report", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			if (window.print) {
				window.print();
			}
		});
	});
})(jQuery_1_8_2);