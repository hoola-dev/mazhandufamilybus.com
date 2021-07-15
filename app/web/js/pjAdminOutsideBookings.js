var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var 
			$frmSearchOutSideBooking = $('#frmSearchOutSideBooking'),
			$dialogSelect = $("#dialogSelect"),
			$dialogReturnSelect = $("#dialogReturnSelect"),
			datepicker = ($.fn.datepicker !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			spinner = ($.fn.spinner !== undefined),
			chosen = ($.fn.chosen !== undefined),
			multiselect = ($.fn.multiselect !== undefined),
			tabs = ($.fn.tabs !== undefined),
			$tabs = $("#tabs"),
			tOpt = {
				select: function (event, ui) {
					$(":input[name='tab_id']").val(ui.panel.id);
				}
			},
			reselect = null,
			return_reselect = null;
	
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs(tOpt);
		}
		$(".field-int").spinner({
			min: 0
		});
		if (chosen) {
			$("#pickup_id").chosen();
			$("#return_id").chosen();
			$("#c_country").chosen();
		}				
        
        if ($frmSearchOutSideBooking.length > 0) {
			$frmSearchOutSideBooking.validate({
				rules: {
					"unique_id": "required"
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		
		if($dialogSelect.length > 0)
		{
			var $frm = null;
			if ($frmCreateBooking.length > 0) 
			{
				$frm = $frmCreateBooking;
			}
			if ($frmUpdateBooking.length > 0) 
			{
				$frm = $frmUpdateBooking;
			}	
			$dialogSelect.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				width: 610,
				open: function (){
					if($('#reload_map').val() == '1' )
					{
						$dialogSelect.html(myLabel.loader);
						$.post("index.php?controller=pjAdminBookings&action=pjActionGetSeats", $frm.serialize()).done(function (data) {
							$dialogSelect.html(data);
							reselect = null;
						});
					}
				},
				buttons: (function () {
					var buttons = {};
					buttons[bsApp.locale.button.reselect] = function () {
						$('#selected_seats').val('');
						$('#bs_selected_seat_label').html('');
						$('#reload_map').val(0);
						reselect = null;
						$(".bs-selected").each(function( index ) {
							$(this).removeClass('bs-selected');
						});
					};
					buttons[bsApp.locale.button.ok] = function () {
						$dialogSelect.dialog("close");
					};
					
					return buttons;
				})()
			});
		}
		
		if($dialogReturnSelect.length > 0)
		{
			var $frm = null;
			if ($frmCreateBooking.length > 0) 
			{
				$frm = $frmCreateBooking;
			}
			if ($frmUpdateBooking.length > 0) 
			{
				$frm = $frmUpdateBooking;
			}	
			$dialogReturnSelect.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				width: 610,
				open: function (){
					if($('#return_reload_map').val() == '1' )
					{
						$dialogReturnSelect.html(myLabel.loader);
						$.post("index.php?controller=pjAdminBookings&action=pjActionGetReturnSeats", $frm.serialize()).done(function (data) {
							$dialogReturnSelect.html(data);
							return_reselect = null;
						});
					}
				},
				buttons: (function () {
					var buttons = {};
					buttons[bsApp.locale.button.reselect] = function () {
						$('#return_selected_seats').val('');
						$('#bs_return_selected_seat_label').html('');
						$('#return_reload_map').val(0);
						return_reselect = null;
						$(".bs-return-selected").each(function( index ) {
							$(this).removeClass('bs-return-selected');
						});
					};
					buttons[bsApp.locale.button.ok] = function () {
						$dialogReturnSelect.dialog("close");
					};
					
					return buttons;
				})()
			});
		}
		
		if ($("#grid").length > 0 && datagrid) {
			var $grid = $("#grid").datagrid({
				buttons: [
                          {type: "edit", url: "index.php?controller=pjAdminBookings&action=pjActionUpdate&ob=1&id={:id}"},				          
						  ],
				columns: [
				          {text: myLabel.client, type: "text", sortable: false, width:140},
				          {text: myLabel.date_time, type: "text", sortable: false, editable: false, width:130},
				          {text: myLabel.bus_route, type: "text", sortable: false, editable: false},
				          {text: myLabel.status, type: "select", sortable: true, editable: false, width: 100, options: [
				                                                                                     {label: myLabel.pending, value: "pending"}, 
				                                                                                     {label: myLabel.confirmed, value: "confirmed"},
				                                                                                     {label: myLabel.cancelled, value: "cancelled"}
				                                                                                     ], applyClass: "pj-status"}],
				dataUrl: "index.php?controller=pjAdminBookings&action=pjActionGetOutsideBooking" + pjGrid.queryString,
				dataType: "json",
				fields: ['client', 'date_time', 'route_details', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=pjAdminBookings&action=pjActionDeleteBookingBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.exported, url: "index.php?controller=pjAdminBookings&action=pjActionExportBooking", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminBookings&action=pjActionSaveBooking&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
        }
				
		$(document).on("focusin", ".datepick", function (e) {
			var minDate, maxDate,
				$this = $(this),
				custom = {},
				o = {
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev"),
					onSelect: function (dateText, inst) {
						if($this.attr("id") == 'booking_date'){
							var frm = null;
							if($frmCreateBooking.length > 0)
							{
								frm = $frmCreateBooking;
							}
							if($frmUpdateBooking.length > 0)
							{
								frm = $frmUpdateBooking;
							}
							$('.bs-loader').css('display', 'block');
							$.post("index.php?controller=pjAdminBookings&action=pjActionChangeDate", frm.serialize()).done(function (data) {
								$('#busBox').html(data.bus);
								$('#fromToBox').html(data.location);
								if (chosen) {
									$('#pickup_id').chosen();
									$("#return_id").chosen();
								}
								$('#total').val('');
								$('#selected_seats').val('');
								$('#ticketBox').css('display', 'none');
								$('#seatsBox').css('display', 'none');
								$('#selectSeatsBox').css('display', 'none');
								$('.bs-loader').css('display', 'none');
							});
						}
						if($this.attr("id") == 'return_date'){
							getReturnBuses();
						}
					}
					
			};
			switch ($this.attr("name")) {
				case "date_from":
					if($(".datepick[name='date_to']").val() != '')
					{
						maxDate = $(".datepick[name='date_to']").datepicker({
							firstDay: $this.attr("rel"),
							dateFormat: $this.attr("rev")
						}).datepicker("getDate");
						$(".datepick[name='date_to']").datepicker("destroy").removeAttr("id");
						if (maxDate !== null) {
							custom.maxDate = maxDate;
						}
					}
					break;
				case "date_to":
					if($(".datepick[name='date_from']").val() != '')
					{
						minDate = $(".datepick[name='date_from']").datepicker({
							firstDay: $this.attr("rel"),
							dateFormat: $this.attr("rev")
						}).datepicker("getDate");
						$(".datepick[name='date_from']").datepicker("destroy").removeAttr("id");
						if (minDate !== null) {
							custom.minDate = minDate;
						}
					}
					break;
			}
			$this.datepicker($.extend(o, custom));
		}).on("click", ".pj-form-field-icon-date", function (e) {
			var $dp = $(this).parent().siblings("input[type='text']");
			if ($dp.hasClass("hasDatepicker")) {
				$dp.datepicker("show");
			} else {
				$dp.trigger("focusin").datepicker("show");
			}
		}).on("click", ".pj-button-detailed, .pj-button-detailed-arrow", function (e) {
			e.stopPropagation();
			$(".pj-form-filter-advanced").toggle();
		}).on("submit", ".frm-filter-advanced", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var obj = {},
				$this = $(this),
				arr = $this.serializeArray(),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			for (var i = 0, iCnt = arr.length; i < iCnt; i++) {
				obj[arr[i].name] = arr[i].value;
			}
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminBookings&action=pjActionGetOutsideBooking", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("reset", ".frm-filter-advanced", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$('input[name=date_from]').val('');
			$('input[name=date_to]').val('');
			$("#route_id").val('');
			$("#filter_bus_id").val('');
			$(".pj-button-detailed").trigger("click");
		}).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("pj-button-active").siblings(".pj-button").removeClass("pj-button-active");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				status: "",
				q: "",
				date_from: "",
				date_to: "",
				route_id: "",
				bus_id: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminBookings&action=pjActionGetOutsideBooking", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("click", ".btn-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache"),
				obj = {};
			$this.addClass("pj-button-active").siblings(".pj-button").removeClass("pj-button-active");
			obj.status = "";
			obj[$this.data("column")] = $this.data("value");
			$.extend(cache, obj);
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminBookings&action=pjActionGetOutsideBooking", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("submit", ".frm-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val(),
				date_from: "",
				date_to: "",
				route_id: "",
				bus_id: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminBookings&action=pjActionGetOutsideBooking", "created", "DESC", content.page, content.rowCount);
			return false;
		}).on("change", "#payment_method", function (e) {
			switch ($("option:selected", this).val()) {
				case 'creditcard':
					$(".boxCC").show();
					break;
				default:
					$(".boxCC").hide();
			}
		}).on("change", "#pickup_id", function (e) {
			$('.bs-loader').css('display', 'block');
			$.get("index.php?controller=pjAdminBookings&action=pjActionGetLocations&pickup_id=" + $(this).val()).done(function (data) {
				$('#returnContainer').html(data);
				$("#return_id").chosen();
				$('.bs-loader').css('display', 'none');
			});
		}).on("change", "#return_id", function (e) {
			if($('#pickup_id').val() == '')
			{
				$('.bs-loader').css('display', 'block');
				$.get("index.php?controller=pjAdminBookings&action=pjActionGetLocations&return_id=" + $(this).val()).done(function (data) {
					$('#pickupContainer').html(data);
					$("#pickup_id").chosen();
					$('.bs-loader').css('display', 'none');
				});
			}else{
				getBuses();
			}
		}).on("change", "#bus_id", function (e) {
			var frm = null;
			if($('#bus_id'). val() != '')
			{
				if($frmCreateBooking.length > 0)
				{
					frm = $frmCreateBooking;
				}
				if($frmUpdateBooking.length > 0)
				{
					frm = $frmUpdateBooking;
				}
				$('.bs-loader').css('display', 'block');
				$.post("index.php?controller=pjAdminBookings&action=pjActionGetTickets", frm.serialize()).done(function (data) {
					$('#ticketBox').html(data.ticket);
					$('#bsDepartureTime').html(data.departure_time);
					$('#bsArrivalTime').html(data.arrival_time);
					$('#ticketBox').css('display', 'block');
					clearPrice();
					$('#selected_seats').val('');
					$('#bs_selected_seat_label').html('');
					$('#seatsBox').css('display', 'none');
					$('#selectSeatsBox').css('display', 'none');
					$('.bs-loader').css('display', 'none');
				
					setBookingRoute();
				});
			}
		}).on("change", "#return_bus_id", function (e) {
			var frm = null;
			if($(this). val() != '')
			{
				if($frmCreateBooking.length > 0)
				{
					frm = $frmCreateBooking;
				}
				if($frmUpdateBooking.length > 0)
				{
					frm = $frmUpdateBooking;
				}
				$('.bs-loader').css('display', 'block');
				$.post("index.php?controller=pjAdminBookings&action=pjActionGetReturnTickets", frm.serialize()).done(function (data) {
					$('#returnTicketBox').html(data.ticket);
					$('#returnTicketBox').css('display', 'block');
					clearPrice();
					$('#return_selected_seats').val('');
					$('#bs_return_selected_seat_label').html('');
					$('#seatsReturnBox').css('display', 'none');
					$('#selectReturnSeatsBox').css('display', 'none');
					$('.bs-loader').css('display', 'none');
				
					setBookingReturnRoute();
				});
			}
		}).on("click", "#is_return", function (e) {
			$(".returnBox").hide();
			if ($(this).attr("checked") == "checked" && $('#bus_id'). val() != '') 
			{
				$(".returnBox").show();
				$('#return_selected_seats').addClass('required');
			}else{
				$('#return_selected_seats').removeClass('required');
			}
			
		}).on("change", ".bs-ticket", function (e) {
			var sub_total = 0,
				tax = 0,
				total, 
				deposit = 0,
				total_tickets = 0,
				$this = $(this),
				number_of_seats = parseInt($('#bs_number_of_seats').val(), 10),
				max_seats = 0;
			$( ".bs-ticket" ).each(function( index ) {
				var qty = parseInt($( this ).val(), 10),
					price = parseFloat($(this).attr('data-price'));
				sub_total += qty * price;
				total_tickets += qty;
			});
			$( ".bs-return-ticket" ).each(function( index ) {
				var qty = parseInt($( this ).val(), 10),
					price = parseFloat($(this).attr('data-price'));
				sub_total += qty * price;
			});
			max_seats = number_of_seats - total_tickets;
			reCalculatingTickets($this, max_seats);
			if(sub_total > 0)
			{
				tax = (sub_total * parseFloat($('#tax').attr('data-tax'))) / 100;
				total = sub_total + tax;
				deposit = (total * parseFloat($('#deposit').attr('data-deposit'))) / 100;
				$('#sub_total').val(sub_total.toFixed(2));
				$('#tax').val(tax.toFixed(2));
				$('#total').val(total.toFixed(2));
				$('#deposit').val(deposit.toFixed(2));
				setPickupPrice();
				setReturnPrice();
				if($('#bus_id').find(':selected').attr('data-set') == 'T')
				{
					$('#seatsBox').css('display', 'block');
					$('#selectSeatsBox').css('display', 'none');
					$('#selected_seats').addClass('required');
				}else{
					$('#selected_seats').removeClass('required');
					var frm = null;
					if($frmCreateBooking.length > 0)
					{
						frm = $frmCreateBooking;
					}
					if($frmUpdateBooking.length > 0)
					{
						frm = $frmUpdateBooking;
					}
					$('.bs-loader').css('display', 'block');
					$.post("index.php?controller=pjAdminBookings&action=pjActionGetSeats", frm.serialize()).done(function (data) {
						$('#selectSeatsBox').html(data);
						if (multiselect) {
							$("#assigned_seats").multiselect();
						}
						$('#seatsBox').css('display', 'none');
						$('#selectSeatsBox').css('display', 'block');
						$('.bs-loader').css('display', 'none');
					});
				}
			}else{
				clearPrice();
				$('#seatsBox').css('display', 'none');
			}
		}).on("change", ".bs-return-ticket", function (e) {
			var sub_total = 0,
				tax = 0,
				total, 
				deposit = 0,
				total_tickets = 0,
				$this = $(this),
				number_of_seats = parseInt($('#bs_return_number_of_seats').val(), 10),
				max_seats = 0;
			$( ".bs-ticket" ).each(function( index ) {
				var qty = parseInt($( this ).val(), 10),
					price = parseFloat($(this).attr('data-price'));
				sub_total += qty * price;
				total_tickets += qty;
			});
			$( ".bs-return-ticket" ).each(function( index ) {
				var qty = parseInt($( this ).val(), 10),
					price = parseFloat($(this).attr('data-price'));
				sub_total += qty * price;
			});
			
			max_seats = number_of_seats - total_tickets;
			reCalculatingReturnTickets($this, max_seats);
			
			if(sub_total > 0)
			{
				tax = (sub_total * parseFloat($('#tax').attr('data-tax'))) / 100;
				total = sub_total + tax;
				deposit = (total * parseFloat($('#deposit').attr('data-deposit'))) / 100;
				$('#sub_total').val(sub_total.toFixed(2));
				$('#tax').val(tax.toFixed(2));
				$('#total').val(total.toFixed(2));
				$('#deposit').val(deposit.toFixed(2));
				setPickupPrice();
				setReturnPrice();
				if($('#return_bus_id').find(':selected').attr('data-set') == 'T')
				{
					$('#seatsReturnBox').css('display', 'block');
					$('#selectReturnSeatsBox').css('display', 'none');
					$('#return_selected_seats').addClass('required');
				}else{
					$('#return_selected_seats').removeClass('required');
					
					var frm = null;
					if($frmCreateBooking.length > 0)
					{
						frm = $frmCreateBooking;
					}
					if($frmUpdateBooking.length > 0)
					{
						frm = $frmUpdateBooking;
					}
					$('.bs-loader').css('display', 'block');
					$.post("index.php?controller=pjAdminBookings&action=pjActionGetReturnSeats", frm.serialize()).done(function (data) {
						$('#selectReturnSeatsBox').html(data);
						if (multiselect) {
							$("#assigned_return_seats").multiselect();
						}
						$('#seatsReturnBox').css('display', 'none');
						$('#selectReturnSeatsBox').css('display', 'block');
						$('.bs-loader').css('display', 'none');
						
						setBookingReturnRoute();
					});
				}
			}else{
				clearPrice();
				$('#seatsBox').css('display', 'none');
			}
		}).on("click", ".bs-select-seats", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$dialogSelect.dialog('open');
		}).on("click", ".bs-select-return-seats", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$dialogReturnSelect.dialog('open');
		}).on("click", ".bs-available", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			
			var seat_id = $(this).attr('data-id'),
				seat_name = $(this).attr('data-name'),
				seat_arr = getSeatsArray(),
				seat_name_arr = getSeatsNameArray(),
				quantity = 0;
			$( ".bs-ticket" ).each(function( index ) {
				quantity += parseInt($( this ).val(), 10);
			});
			if(quantity > seat_arr.length && jQuery.inArray( seat_id, seat_arr ) == -1)
			{
				$(this).addClass('bs-selected');
				seat_arr.push(seat_id);
				$('#selected_seats').val(seat_arr.join("|"));
				seat_name_arr.push(seat_name);
				$('#bs_selected_seat_label').html(seat_name_arr.join(", "));
				$('#reload_map').val(0);
			}
		}).on("click", ".bs-return-available", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			
			var seat_id = $(this).attr('data-id'),
				seat_name = $(this).attr('data-name'),
				seat_arr = getReturnSeatsArray(),
				seat_name_arr = getReturnSeatsNameArray(),
				quantity = 0;
			$( ".bs-return-ticket" ).each(function( index ) {
				quantity += parseInt($( this ).val(), 10);
			});
			if(quantity > seat_arr.length && jQuery.inArray( seat_id, seat_arr ) == -1)
			{
				$(this).addClass('bs-return-selected');
				seat_arr.push(seat_id);
				$('#return_selected_seats').val(seat_arr.join("|"));
				seat_name_arr.push(seat_name);
				$('#bs_return_selected_seat_label').html(seat_name_arr.join(", "));
				$('#return_reload_map').val(0);
			}
		});
		
		function setPickupPrice()
		{
			var sub_total = 0,
				tax = 0,
				total, 
				deposit = 0;
			$( ".bs-ticket" ).each(function( index ) {
				var qty = parseInt($( this ).val(), 10),
					price = parseFloat($(this).attr('data-price'));
				sub_total += qty * price;
			});
			tax = (sub_total * parseFloat($('#tax').attr('data-tax'))) / 100;
			total = sub_total + tax;
			deposit = (total * parseFloat($('#deposit').attr('data-deposit'))) / 100;
			$('#pickup_sub_total').val(sub_total.toFixed(2));
			$('#pickup_tax').val(tax.toFixed(2));
			$('#pickup_total').val(total.toFixed(2));
			$('#pickup_deposit').val(deposit.toFixed(2));
		}
		function setReturnPrice()
		{
			var sub_total = 0,
				tax = 0,
				total, 
				deposit = 0;
			$( ".bs-return-ticket" ).each(function( index ) {
				var qty = parseInt($( this ).val(), 10),
					price = parseFloat($(this).attr('data-price'));
				sub_total += qty * price;
			});
			tax = (sub_total * parseFloat($('#tax').attr('data-tax'))) / 100;
			total = sub_total + tax;
			deposit = (total * parseFloat($('#deposit').attr('data-deposit'))) / 100;
			$('#return_sub_total').val(sub_total.toFixed(2));
			$('#return_tax').val(tax.toFixed(2));
			$('#return_total').val(total.toFixed(2));
			$('#return_deposit').val(deposit.toFixed(2));
		}
		function clearPrice()
		{
			$('#sub_total').val('');
			$('#tax').val('');
			$('#total').val('');
			$('#deposit').val('');
			$('#pickup_sub_total').val('');
			$('#pickup_tax').val('');
			$('#pickup_total').val('');
			$('#pickup_deposit').val('');
			$('#return_sub_total').val('');
			$('#return_tax').val('');
			$('#return_total').val('');
			$('#return_deposit').val('');
		}
		function getSeatsArray()
		{
			var selected_seats = $('#selected_seats').val(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split("|");
			}
			return seat_arr;
		}
		function getReturnSeatsArray()
		{
			var selected_seats = $('#return_selected_seats').val(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split("|");
			}
			return seat_arr;
		}
		function getSeatsNameArray()
		{
			var selected_seats = $('#bs_selected_seat_label').html(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split(", ");
			}
			return seat_arr;
		}
		function getReturnSeatsNameArray()
		{
			var selected_seats = $('#bs_return_selected_seat_label').html(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split(", ");
			}
			return seat_arr;
		}
		function setBookingRoute()
		{
			var booking_route = '';
			if($('#bus_id').val() != '' && $('#pickup_id').val() != '' && $('#return_id').val() != '')
			{
				booking_route += $('#bus_id option:selected').text() + '<br/>';
				booking_route += myLabel.from + ' ' + $('#pickup_id option:selected').text() + ' ';
				booking_route += myLabel.to + ' ' + $('#return_id option:selected').text();
			}
			$('#booking_route').val(booking_route);
		}
		function setBookingReturnRoute()
		{
			var booking_route = '';
			if($('#return_bus_id').val() != '' && $('#pickup_id').val() != '' && $('#return_id').val() != '')
			{
				booking_route += $('#return_bus_id option:selected').text() + '<br/>';
				booking_route += myLabel.from + ' ' + $('#return_id option:selected').text();
				booking_route += myLabel.to + ' ' + $('#pickup_id option:selected').text() + ' ';
			}
			$('#booking_return_route').val(booking_route);
		}
		function reCalculatingTickets($this, max_seats)
		{
			var current_value = parseInt($this.val(), 10),
			number_of_seats = parseInt($('#bs_number_of_seats').val(), 10);

			$('.bs-ticket').each(function( index ) {
				
				if($this.attr('name') != $(this).attr('name'))
				{
					var selected_value = parseInt($(this).val(), 10),
						new_options = {},
						$that = $(this);
					$that.empty();
					if(selected_value > 0)
					{
						max_seats = (number_of_seats - current_value);
					}
					for(var i = 0; i <= max_seats; i++)
					{
						new_options[i] = i;
					}
					$.each(new_options, function(key, value) {
						$that.append($("<option></option>").attr("value", value).text(key));
					});
					$that.val(selected_value);
				}
			});
		}
		function reCalculatingReturnTickets($this, max_seats)
		{
			var current_value = parseInt($this.val(), 10),
			number_of_seats = parseInt($('#bs_return_number_of_seats').val(), 10);

			$('.bs-return-ticket').each(function( index ) {
				
				if($this.attr('name') != $(this).attr('name'))
				{
					var selected_value = parseInt($(this).val(), 10),
						new_options = {},
						$that = $(this);
					$that.empty();
					if(selected_value > 0)
					{
						max_seats = (number_of_seats - current_value);
					}
					for(var i = 0; i <= max_seats; i++)
					{
						new_options[i] = i;
					}
					$.each(new_options, function(key, value) {
						$that.append($("<option></option>").attr("value", value).text(key));
					});
					$that.val(selected_value);
				}
			});
		}
		function getBuses()
		{
			if($('#pickup_id'). val() != '' && $('#return_id'). val() != '')
			{
				var frm = null;
				if($frmCreateBooking.length > 0)
				{
					frm = $frmCreateBooking;
				}
				if($frmUpdateBooking.length > 0)
				{
					frm = $frmUpdateBooking;
				}
				$('.bs-loader').css('display', 'block');
				$.post("index.php?controller=pjAdminBookings&action=pjActionGetBuses", frm.serialize()).done(function (data) {
					$('#busBox').html(data);
					$('.bs-loader').css('display', 'none');
				
					clearPrice();
					$('#selected_seats').val('');
					$('#bs_selected_seat_label').html('');
					$('#ticketBox').css('display', 'none');
					$('#seatsBox').css('display', 'none');
					$('#selectSeatsBox').css('display', 'none');
					
					setBookingRoute();
				});
			}
		}
		function getReturnBuses()
		{
			if($('#pickup_id'). val() != '' && $('#return_id'). val() != '')
			{
				var frm = null;
				if($frmCreateBooking.length > 0)
				{
					frm = $frmCreateBooking;
				}
				if($frmUpdateBooking.length > 0)
				{
					frm = $frmUpdateBooking;
				}

				$.post("index.php?controller=pjAdminBookings&action=pjActionGetReturnBuses", frm.serialize()).done(function (data) {
					$('#returnBox').html(data);
					
					setBookingReturnRoute();
				});
			}
		}
    });    
})(jQuery_1_8_2);