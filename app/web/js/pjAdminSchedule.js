var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var 
			$frmSchedule = $('#frmSchedule'),
			tipsy = ($.fn.tipsy !== undefined),
			datepicker = ($.fn.datepicker !== undefined);
		
		if (tipsy) {
			$(".timetable-tip").tipsy({
				offset: 1,
				opacity: 1,
				html: true,
				gravity: "nw",
				className: "tipsy-listing"
			});
		}
		
		if($(".pj-location-grid").length > 0)
		{
			var head_height = $('.content-head-row').height();
			$('.content-head-row').height(head_height + 20);
			$('.title-head-row').height(head_height + 20);
			
			$('.title-row').each(function(index) {
			    var id = $(this).attr('lang');
			    var h = $('#content_row_' + id).height();
			    if(h < 34){
			    	h = 34;
			    }
			    $(this).height(h);
			    $('#content_row_' + id).height(h);
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
		
		function getSchedule(column, direction) 
		{
			var schedule_date = $('#schedule_date').val(),
				$button_today = $('.btn-today'),
				print_href = $('#bs_print_href').val(),
				route_id = $('#route_id').val();
			if(schedule_date == $button_today.attr('data-value'))
			{
				$button_today.addClass('pj-button-active');
			}else{
				$button_today.removeClass('pj-button-active');
			}
			$('.bs-loader').css('display', 'block');
			$.get("index.php?controller=pjAdminSchedule&action=pjActionGetSchedule", {
				date: schedule_date,
				column: column,
				direction: direction,
				route_id: route_id
			}).done(function (data) {
				$("#boxSchedule").html(data);
				var sql_date = $('#bs_schedule_date').val();
				$('#bs_print_schedule').attr('href', print_href + "&date=" + sql_date + "&route_id=" + route_id + "&column=" + column + "&direction=" + direction)
				$('.bs-loader').css('display', 'none');
			});
		}
		
		function getTimetable(mode) 
		{
			var selected_date = $('#selected_date').val(),
				route_id = $('#route_id').val(),
				opts = {};
			if(mode == 'route' || mode == 'date')
			{
				opts = {
					route_id: route_id,
					selected_date: selected_date
				}
			}else if(mode == 'next'){
				var $next_link = $('#bs_next_week');
				opts = {
					route_id: route_id,
					week_start_date: $next_link.attr('data-week_start'),
					week_end_date: $next_link.attr('data-week_end'),
					selected_date: selected_date
				}
			}else if(mode == 'prev'){
				var $prev_link = $('#bs_prev_week');
				opts = {
					route_id: route_id,
					week_start_date: $prev_link.attr('data-week_start'),
					week_end_date: $prev_link.attr('data-week_end'),
					selected_date: selected_date
				}
			}
			
			$('.bs-loader').css('display', 'block');
			$.get("index.php?controller=pjAdminSchedule&action=pjActionGetTimetable", opts).done(function (data) {
				$("#boxTimetable").html(data);
				if (tipsy) {
					$(".timetable-tip").tipsy({
						offset: 1,
						opacity: 1,
						html: true,
						gravity: "nw",
						className: "tipsy-listing"
					});
				}
				$('.bs-loader').css('display', 'none');
			});
		}
		
		$(document).on("click", "*", function (e) {
			if ($(".pj-menu-list-wrap").is(":visible")) {
				$(".pj-menu-list-wrap").hide();
			}
		}).on("focusin", ".datepick", function (e) {
			var $this = $(this);
			$this.datepicker({
				firstDay: $this.attr("rel"),
				dateFormat: $this.attr("rev"),
				onSelect: function (dateText, inst) {
					if($('#boxSchedule').length > 0)
					{
						getSchedule('departure','ASC');
					}
					if($('#boxTimetable').length > 0)
					{
						getTimetable('date');
					}
				}
			});
		}).on("click", ".pj-form-field-icon-date", function (e) {
			var $dp = $(this).parent().siblings("input[type='text']");
			if ($dp.hasClass("hasDatepicker")) {
				$dp.datepicker("show");
			} else {
				$dp.trigger("focusin").datepicker("show");
			}
		}).on("click", ".btn-today", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var schedule_date = $(this).attr('data-value');
			$('#schedule_date').val(schedule_date);
			getSchedule('departure','ASC');
		}).on("change", "#route_id", function (e) {
			getSchedule('departure','ASC');
		}).on("click", ".pj-table-row-clickable", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var href = $(this).attr('data-href');
			window.location.href = href;
		}).on("change", "#location_id", function (e) {
			var href = $(this).attr('data-href') + '&location_id=' + $(this).val(),
				print_href = href.replace("pjActionGetBookings", "pjActionPrintBookings");
			$('.bs-loader').css('display', 'block');
			$.get(href, {
				
			}).done(function (data) {
				$("#boxBookings").html(data);
				$("#bs_print_booking").attr('href', print_href);
				$('.bs-loader').css('display', 'none');
			});
		}).on("click", ".pj-table-sort-up", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			getSchedule($(this).attr('data-column'),'ASC');
		}).on("click", ".pj-table-sort-down", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			getSchedule($(this).attr('data-column'),'DESC');
		}).on("change", "#route_id", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			getTimetable('route');
		}).on("click", "#bs_next_week", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			getTimetable('next');
		}).on("click", "#bs_prev_week", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			getTimetable('prev');
		}).on("click", ".pj-table-icon-menu", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var diff, lf,
				$this = $(this),
				$list = $this.siblings(".pj-menu-list-wrap");
			$list.css({
				"right": -56
			});
		
			$list.toggle();
			$(".pj-menu-list-wrap").not($list).hide();
			return false;
		});
	});
})(jQuery_1_8_2);