var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateBus = $("#frmCreateBus"),
			$frmUpdateTime = $("#frmUpdateTime"),
			$frmNotOperating = $("#frmNotOperating"),
			$frmUpdateTicket = $("#frmUpdateTicket"),
			$frmUpdatePrice = $("#frmUpdatePrice"),
			$frmCopyPrice = $("#frmCopyPrice"),
			$dialogValidate = $("#dialogValidate"),
			$dialogCopy = $("#dialogCopy"),
			tipsy = ($.fn.tipsy !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			datepicker = ($.fn.datepicker !== undefined),
			remove_arr = new Array();
		
		if (tipsy) {
			$(".listing-tip").tipsy({
				offset: 1,
				opacity: 1,
				html: true,
				gravity: "nw",
				className: "tipsy-listing"
			});
		}
		
		function initializeTimePicker()
		{
			/*if($('.timepick').length > 0)
			{
				$('.timepick').timepicker({
					showPeriod: myLabel.showperiod,
					defaultTime: '',
					minutes: {
		                starts: 0,                 
		                ends: 59,                
		                interval: 1,             
		            },
				});
			}*/
		}
		
		function setTickets()
		{
			var index_arr = new Array();
				
			$('#bs_ticket_list').find(".bs-ticket-row").each(function (index, row) {
				index_arr.push($(row).attr('data-index'));
			});
			$('#index_arr').val(index_arr.join("|"));
		}
		
		function initializePriceGrid()
		{
			if($(".pj-location-grid").length > 0)
			{
				var head_height = $('.content-head-row').height();
				$('.content-head-row').height(head_height + 20);
				$('.title-head-row').height(head_height + 20);
				
				$('.title-row').each(function(index) {
				    var id = $(this).attr('lang');
				    var h = $('.content_row_' + id).height();
				    if(h < 56){
				    	h = 56;
				    }
				    $(this).height(h);
				    $('.content_row_' + id).height(h);
				});
				$(".wrapper1").scroll(function(){
			        $(".wrapper2")
			            .scrollLeft($(".wrapper1").scrollLeft());
			    });
			    $(".wrapper2").scroll(function(){
			        $(".wrapper1")
			            .scrollLeft($(".wrapper2").scrollLeft());
			    });
			    
			    $(".wrapper2").height($("#compare_table").height() + 24);
			}
		}
		if($frmNotOperating.length > 0)
		{
			if($('#bs_date_container').find('span.block').length > 0)
			{
				$('.pjBrsNoDates').hide();
			}else{
				$('.pjBrsNoDates').show();
			}
		}
		if ($frmCreateBus.length > 0) {
			$frmCreateBus.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
			initializeTimePicker();
		}
		
		if ($frmUpdateTime.length > 0) {
			$frmUpdateTime.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
			initializeTimePicker();
		}
		
		if ($frmUpdateTicket.length > 0) {
			if($dialogValidate.length > 0)
			{
				$dialogValidate.dialog({
					autoOpen: false,
					resizable: false,
					draggable: false,
					modal: true,
					width: 400,
					buttons: (function () {
						var buttons = {};
						buttons[bsApp.locale.button.ok] = function () {
							$dialogValidate.dialog("close");
						};
						
						return buttons;
					})()
				});
			}
			$frmUpdateTicket.validate({
				errorPlacement: function (error, element) {
					if(element.attr('name') == 'total_counts')
					{
						element.parent().css('display', 'none');
						element.parent().parent().css('display', 'block');
					}
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: "",
				invalidHandler: function (event, validator) {
				    $(".pj-multilang-wrap").each(function( index ) {
						if($(this).attr('data-index') == myLabel.localeId)
						{
							$(this).css('display','block');
						}else{
							$(this).css('display','none');
						}
					});
					$(".pj-form-langbar-item").each(function( index ) {
						if($(this).attr('data-index') == myLabel.localeId)
						{
							$(this).addClass('pj-form-langbar-item-active');
						}else{
							$(this).removeClass('pj-form-langbar-item-active');
						}
					});
				}
			});
			
			$frmUpdateTicket.submit(function(e){
				if($('#set_seats_count').attr("checked"))
				{
					var total_counts = 0,
						seats_available = parseInt($('#seats_available').val(), 10);
					$('#bs_ticket_list').find('.pj-ticket-count').each(function( index ) {
						total_counts += parseInt($(this).val(), 10);
					});
					if(total_counts == seats_available)
					{
						if($frmUpdateTicket.valid())
						{
							setTickets();
						}
					}else{
						$dialogValidate.dialog("open");
						return false;
					}
				}else{
					if($frmUpdateTicket.valid())
					{
						setTickets();
					}
				}	
			});
		}
		
		if ($frmUpdatePrice.length > 0) {
			$frmUpdateTime.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
			initializePriceGrid();
			
			if($dialogCopy.length > 0)
			{
				$dialogCopy.dialog({
					autoOpen: false,
					resizable: false,
					draggable: false,
					modal: true,
					width: 500,
					buttons: (function () {
						var buttons = {};
						if(myLabel.buses > 0)
						{
							buttons[bsApp.locale.button.copy] = function () {
								if($('#source_bus_id').val() != '' && $('#source_ticket_id').val() != '')
								{
									$.post("index.php?controller=pjAdminBuses&action=pjActionCopyPrices&bus_id="+$('#id').val()+"&ticket_id=" + $('#ticket_id').val(), $frmCopyPrice.serialize()).done(function (data) {
										if(data.code == '200')
										{
											$dialogCopy.dialog("close");
											window.location.href = "index.php?controller=pjAdminBuses&action=pjActionPrice&id="+$('#id').val()+"&ticket_id=" + $('#ticket_id').val() + "&err=APC01";
										}
									});
								}
							};
						}
						buttons[bsApp.locale.button.cancel] = function () {
							$dialogCopy.dialog("close");
						};
						
						return buttons;
					})()
				});
			}
		}        

        function onBeforeBusEditShow (obj) {
			if (pjGrid.currentRoleId == 10) {
				return false;
			}
			return true;
        }

        function onBeforeBusDeleteShow (obj) {
			if (is_editor === 1 || pjGrid.currentRoleId == 10) {
				return false;
			}
			return true;
        }

		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminBuses&action=pjActionTime&id={:id}", beforeShow: onBeforeBusEditShow},
				          {type: "delete", url: "index.php?controller=pjAdminBuses&action=pjActionDeleteBus&id={:id}", beforeShow: onBeforeBusDeleteShow}
				          ],
				columns: [{text: myLabel.route, type: "text", sortable: true, editable: false, width: 300},
				          {text: myLabel.depart_arrive, type: "text", sortable: true, editable: false, width: 120},
				          {text: myLabel.from_to, type: "text", sortable: true, editable: false, width: 180}],
				dataUrl: "index.php?controller=pjAdminBuses&action=pjActionGetBus",
				dataType: "json",
				fields: ['route', 'depart_arrive', 'from_to'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=pjAdminBuses&action=pjActionDeleteBusBulk", render: true, confirmation: myLabel.delete_confirmation}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminBuses&action=pjActionSaveBus&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		$(document).on("submit", ".frm-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val(),
				bus_id: $this.find("input[name='bus_id']").val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminBuses&action=pjActionGetBus", "route", "ASC", content.page, content.rowCount);
			return false;
		}).on("change", "#filter_route_id", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.closest("form").find("input[name='q']").val(),
				route_id: $this.val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminBuses&action=pjActionGetBus", "route", "ASC", content.page, content.rowCount);
			return false;
		}).on("change", "#route_id", function (e) {
			var route_id = $(this).val();
			if(route_id == '')
			{
				$('#bs_bus_locations').html('');
			}else{
				var qs = '';
				if ($frmUpdateTime.length > 0) 
				{
					qs = '&bus_id=' + $('#id').val();
				}
				$('.bs-loader').css('display', 'block');
				$.get("index.php?controller=pjAdminBuses&action=pjActionGetLocations&route_id=" + route_id + qs).done(function (data) {
					$('#bs_bus_locations').html(data);
					$('.bs-loader').css('display', 'none');
					initializeTimePicker();
				});
			}
		}).on("focusin", ".timepick", function (e) {
			var $this = $(this);
			$this.timepicker({
				timeFormat: $this.attr("lang"),
				stepMinute: 1,
				controlType: 'select',
				beforeShow: function(input, inst) {
					$('#ui-datepicker-div').addClass("pjBrsjQueryUI");
				}
			});
			
		}).on("focusin", ".datepick-period", function (e) {
			var minDate, maxDate,
				$this = $(this),
				custom = {},
				o = {
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev")
				};
			switch ($this.attr("name")) {
			case "start_date":
				if($(".datepick-period[name='end_date']").val() != '')
				{
					maxDate = $(".datepick-period[name='end_date']").datepicker({
						firstDay: $this.attr("rel"),
						dateFormat: $this.attr("rev")
					}).datepicker("getDate");
					$(".datepick-period[name='end_date']").datepicker("destroy").removeAttr("id");
					if (maxDate !== null) {
						custom.maxDate = maxDate;
					}
				}
				break;
			case "end_date":
				if($(".datepick-period[name='start_date']").val() != '')
				{
					minDate = $(".datepick-period[name='start_date']").datepicker({
						firstDay: $this.attr("rel"),
						dateFormat: $this.attr("rev")
					}).datepicker("getDate");
					$(".datepick-period[name='start_date']").datepicker("destroy").removeAttr("id");
					if (minDate !== null) {
						custom.minDate = minDate;
					}
				}
				break;
			}
			$(this).datepicker($.extend(o, custom));
			
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
		}).on("click", ".pj-button-add-date", function (e) {
			var clone_text = $('#bs_date_clone').html();
			$('#bs_date_container').append(clone_text);
			if($('#bs_date_container').find('span.block').length > 0)
			{
				$('.pjBrsNoDates').hide();
			}else{
				$('.pjBrsNoDates').show();
			}
		}).on("click", ".pj-button-remove-date", function (e) {
			$(this).parent().remove();
			if($('#bs_date_container').find('span.block').length > 0)
			{
				$('.pjBrsNoDates').hide();
			}else{
				$('.pjBrsNoDates').show();
			}
		}).on("click", '.pj-add-ticket', function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var clone_text = $('#bs_ticket_clone').html(),
				index = Math.ceil(Math.random() * 999999),
				number_of_tickets = $('#bs_ticket_list').find(".bs-ticket-row").length,
				order = parseInt(number_of_tickets, 10) + 1,
				pj_class = ' pj-right-250';
			if($('#set_seats_count').attr("checked"))
			{
				pj_class = ' pj-right-168';
			}
			clone_text = clone_text.replace(/\{INDEX\}/g, 'bs_' + index);
			clone_text = clone_text.replace(/\{ORDER\}/g, order);
			clone_text = clone_text.replace(/\{CLASS\}/g, pj_class);
			$('#bs_ticket_list').append(clone_text);
			
		}).on("click", '.pj-remove-ticket', function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $ticket = $(this).parent().parent(),
				id = $ticket.attr('data-index');
			if(id.indexOf("bs") == -1)
			{
				remove_arr.push(id);
			}
			$('#remove_arr').val(remove_arr.join("|"));
			$ticket.remove();
			
			$('#bs_ticket_list').find(".bs-ticket-row").each(function (order, row) {
				var index = $(row).attr('data-index'),
					title = myLabel.ticket + " " + (order + 1) + ":";
				$('.bs-title-' + index).html(title);
			});
		}).on("change", "#ticket_id", function (e) {
			var ticket_id = $(this).val(),
			    qs = '&bus_id=' + $('#id').val();
			$('.bs-loader').css('display', 'block');
			$.get("index.php?controller=pjAdminBuses&action=pjActionGetPriceGrid&ticket_id=" + ticket_id + qs).done(function (data) {
				$('#bs_price_grid').html(data);
				$('.bs-loader').css('display', 'none');
				initializePriceGrid();
			});
			$.get("index.php?controller=pjAdminBuses&action=pjActionGetReturnPriceGrid&ticket_id=" + ticket_id + qs).done(function (data) {
				$('#bs_return_price_grid').html(data);
				$('.bs-loader').css('display', 'none');
				initializePriceGrid();
			});
		}).on("focusin", ".pj-grid-field", function(e){
			$(this).select();
		}).on("click", "#set_seats_count", function(e){
			if($(this).attr("checked"))
			{
				$('.pj-ticket-count').removeClass('pj-hide-count');
				$('.ticket-icons').removeClass('pj-right-250').addClass('pj-right-168');
			}else{
				$('.pj-ticket-count').addClass('pj-hide-count');
				$('.ticket-icons').removeClass('pj-right-168').addClass('pj-right-250');
			}
		}).on("click", ".pj-copy-ticket", function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$dialogCopy.dialog("open");	
		}).on("change", "#source_bus_id", function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.get("index.php?controller=pjAdminBuses&action=pjActionGetTickets&bus_id=" + $(this).val()).done(function (data) {
				$('#ticketTypeBox').html(data);
			});
		});
	});
})(jQuery_1_8_2);

function check_booked_seats(ele){
    var bus_id = jQuery_1_8_2.trim(jQuery_1_8_2("#id").val());
    var date = jQuery_1_8_2(ele).val();
    if (date) {
        var post_data= {
            'bus_id' : bus_id,
            'date': date
        };
        jQuery_1_8_2("#frmNotOperating").fadeTo('slow',0.2,function(){
            jQuery_1_8_2.post(
                'index.php?controller=pjAdminBuses&action=pjActionCheckBookedSeats&rand='+Math.random(),
                post_data,
                function(data){
                    if (data['success'] == 1) {
                        jQuery_1_8_2('#btn_submit').click();
                    } else {
                        var r = confirm("Seats are already booked for this date. Are you sure to proceed?");
                        if (r == true) {
                            jQuery_1_8_2('#btn_submit').click();
                        } else {
                            jQuery_1_8_2(ele).val('');
                        }
                    }
                    jQuery_1_8_2("#frmNotOperating").fadeTo('slow',1);
                },
                'json'
            );
        });
    }
}