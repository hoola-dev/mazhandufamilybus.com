var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateRoute = $("#frmCreateRoute"),
			$frmUpdateRoute = $("#frmUpdateRoute"),
			$dialogPrompt = $("#dialogPrompt"),
			datagrid = ($.fn.datagrid !== undefined);
		
		function setLocations()
		{
			var index_arr = new Array();
			
			$('#bs_location_list').find(".bs-location-row").each(function (index, row) {
				index_arr.push($(row).attr('data-index'));
			});
			$('#index_arr').val(index_arr.join("|"));
		}
		
		if($(".pj-location-grid").length > 0)
		{
			var head_height = $('.content-head-row').height();
			$('.content-head-row').height(head_height + 20);
			$('.title-head-row').height(head_height + 20);
			
			$('.title-row').each(function(index) {
			    var id = $(this).attr('lang');
			    var h = $('#content_row_' + id).height();
			    if(h < 56){
			    	h = 56;
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
		
		if ($frmCreateRoute.length > 0) {
			$frmCreateRoute.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ''
			});
			
			$frmCreateRoute.submit(function(e){
				if($frmCreateRoute.valid())
				{
					setLocations();
				}
			});
		}
		if ($frmUpdateRoute.length > 0) {
			$frmUpdateRoute.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ''
			});
			
			$frmUpdateRoute.submit(function(e){
				if($frmUpdateRoute.valid())
				{
					setLocations();
				}
			});
		}
		
		$("#bs_location_list").sortable({
			handle : '.location-move-icon',
			stop: function(e){
				$('#bs_location_list').find(".bs-location-row").each(function (order, row) {
					var index = $(row).attr('data-index'),
						title = myLabel.location + " " + (order + 1) + ":";
					$('.bs-title-' + index).html(title);
				});
			}
	    });
		
		if ($dialogPrompt.length > 0) {
			$dialogPrompt.dialog({
				autoOpen: false,
				resizable: false,
				draggable: false,
				modal: true,
				buttons: (function () {
					var buttons = {};
					
					buttons[bsApp.locale.button.ok] = function () {
						$dialogPrompt.dialog('close');
					};
					
					return buttons;
				})()
			});
		}
        
        function onBeforeEditShow (obj) {
			if (pjGrid.currentRoleId == 10) {
				return false;
			}
			return true;
        }

        function onBeforeDeleteShow (obj) {
			if (pjGrid.currentRoleId == 10) {
				return false;
			}
			return true;
        }

        function onBeforeMenuShow (obj) {
			if (pjGrid.currentRoleId == 10) {
				return false;
			}
			return true;
        }

		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminRoutes&action=pjActionUpdate&id={:id}", beforeShow: onBeforeEditShow},
				          {type: "delete", url: "index.php?controller=pjAdminRoutes&action=pjActionDeleteRoute&id={:id}", beforeShow: onBeforeDeleteShow},
				          {type: "menu", url: "#", text: myLabel.more, items:[
				               {text: myLabel.copy_route, url: "index.php?controller=pjAdminRoutes&action=pjActionCreate&type=copy&id={:id}", beforeShow: onBeforeMenuShow},
				               {text: myLabel.reverse_route, url: "index.php?controller=pjAdminRoutes&action=pjActionCreate&type=reverse&id={:id}", beforeShow: onBeforeMenuShow}
				          ]}
				          ],
				columns: [{text: myLabel.title, type: "text", sortable: true, editable: false, width: 200},
				          {text: myLabel.from, type: "text", sortable: true, editable: false, width: 130},
				          {text: myLabel.to, type: "text", sortable: true, editable: false, width: 130},
				          {text: myLabel.status, type: "select", sortable: true, editable: pjGrid.currentRoleId != 10, width: 90, options: [
				                                                                                     {label: myLabel.active, value: "T"}, 
				                                                                                     {label: myLabel.inactive, value: "F"}
				                                                                                     ], applyClass: "pj-status"}],
				dataUrl: "index.php?controller=pjAdminRoutes&action=pjActionGetRoute",
				dataType: "json",
				fields: ['title','from', 'to', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=pjAdminRoutes&action=pjActionDeleteRouteBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.revert_status, url: "index.php?controller=pjAdminRoutes&action=pjActionStatusRoute", render: true},
					   {text: myLabel.exported, url: "index.php?controller=pjAdminRoutes&action=pjActionExportRoute", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminRoutes&action=pjActionSaveRoute&id={:id}",
				select: {
					field: "id",
					name: "record[]"
				}
			});
		}
		
		$(document).on("click", ".btn-all", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).addClass("pj-button-active").siblings(".pj-button").removeClass("pj-button-active");
			var content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				status: "",
				q: ""
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminRoutes&action=pjActionGetRoute", "title", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=pjAdminRoutes&action=pjActionGetRoute", "title", "ASC", content.page, content.rowCount);
			return false;
		}).on("click", ".pj-status-1", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			return false;
		}).on("click", ".pj-status-0", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$.post("index.php?controller=pjAdminRoutes&action=pjActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=pjAdminRoutes&action=pjActionGetRoute");
			});
			return false;
		}).on("submit", ".frm-filter", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $this = $(this),
				content = $grid.datagrid("option", "content"),
				cache = $grid.datagrid("option", "cache");
			$.extend(cache, {
				q: $this.find("input[name='q']").val()
			});
			$grid.datagrid("option", "cache", cache);
			$grid.datagrid("load", "index.php?controller=pjAdminRoutes&action=pjActionGetRoute", "id", "ASC", content.page, content.rowCount);
			return false;
		}).on("click", '.pj-add-location', function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var clone_text = $('#bs_location_clone').html(),
				index = Math.ceil(Math.random() * 999999),
				number_of_locations = $('#bs_location_list').find(".bs-location-row").length,
				order = parseInt(number_of_locations, 10) + 1;
			if(number_of_locations < myLabel.number_of_cities)
			{
				clone_text = clone_text.replace(/\{INDEX\}/g, 'bs_' + index);
				clone_text = clone_text.replace(/\{ORDER\}/g, order);
				$('#bs_location_list').append(clone_text);
			}
		}).on("click", '.location-delete-icon', function(e){
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var $location = $(this).parent().parent();
			$location.remove();
			
			$('#bs_location_list').find(".bs-location-row").each(function (order, row) {
				var index = $(row).attr('data-index'),
					title = myLabel.location + " " + (order + 1) + ":";
				$('.bs-title-' + index).html(title);
			});
		}).on("focusin", '.pj-grid-field', function(e){
			$(this).select();
		}).on("change", '.bs-city', function(e){
			var $this = $(this);
			$('#bs_location_list').find(".bs-city").each(function (order, ele) {
				if($(ele).attr('name') != $this.attr('name') && $this.val() == $(ele).val())
				{
					$dialogPrompt.dialog('open');
					$this.val('');
				}
			});
		});
	});
})(jQuery_1_8_2);