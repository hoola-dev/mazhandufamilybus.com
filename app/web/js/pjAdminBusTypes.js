var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateBusType = $("#frmCreateBusType"),
			$frmUpdateBusType = $("#frmUpdateBusType"),
			$dialogUpdate = $("#dialogUpdate"),
			$dialogDel = $("#dialogDelete"),
			$boxMap = $("#boxMap"),
			datagrid = ($.fn.datagrid !== undefined),
			validate = ($.fn.validate !== undefined),
			vOpts = {
				rules: {
					seats_count: {
						required: function(){
							if($('#seats_map').val() == '')
							{
								return true;
							}else{
								return false;
							}
						}
					}
				},
				messages: {
					number_of_seats:{
						required: myLabel.seats_required
					}
				},
				errorPlacement: function (error, element) {
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
			};
		
		function collisionDetect(o) {
			var i, pos, horizontalMatch, verticalMatch, collision = false;
			$("#mapHolder").children("span").each(function (i) {
				pos = getPositions(this);
				horizontalMatch = comparePositions([o.left, o.left + o.width], pos[0]);
				verticalMatch = comparePositions([o.top, o.top + o.height], pos[1]);			
				if (horizontalMatch && verticalMatch) {
					collision = true;
					return false;
				}
			});
			if (collision) {
				return true;
			}
			return false;
		}
		function getPositions(box) {
			var $box = $(box);
			var pos = $box.position();
			var width = $box.width();
			var height = $box.height();
			return [[pos.left, pos.left + width], [pos.top, pos.top + height]];
		}
		
		function comparePositions(p1, p2) {
			var x1 = p1[0] < p2[0] ? p1 : p2;
			var x2 = p1[0] < p2[0] ? p2 : p1;
			return x1[1] > x2[0] || x1[0] === x2[0] ? true : false;
		}
		
		function updateElem(event, ui) {
			var $this = $(this),
				rel = $this.attr("rel"),
				$hidden = $("#" + rel),
				val = $hidden.val().split("|");				
			$hidden.val([val[0], parseInt($this.width(), 10), parseInt($this.height(), 10), ui.position.left, ui.position.top, $this.text(), val[6], val[7]].join("|"));
		}
		function getMax() {
			var tmp, index = 0;
			$("span.empty").each(function (i) {
				tmp = parseInt($(this).attr("rel").split("_")[1], 10);
				if (tmp > index) {
					index = tmp;
				}
			});
			return index;
		}
		
		if ($frmCreateBusType.length > 0 && validate) {
			var validator = $frmCreateBusType.submit(function() {

			}).validate(vOpts);
		}
		if ($frmUpdateBusType.length > 0) {
			var validator = $frmUpdateBusType.submit(function() {
				if($('#hiddenHolder').length > 0)
				{
					if($("#hiddenHolder :input").length > 0)
					{
						$('#number_of_seats').val('1');
					}else{
						$('#number_of_seats').val('');
					}
				}
			}).validate(vOpts);
			
			var offset = $("#map").offset(),
				dragOpts = {
					containment: "parent",
					stop: function (event, ui) {
						updateElem.apply(this, [event, ui]);
					}
				};
			$("span.empty").draggable(dragOpts).resizable({
				resize: function(e, ui) {
					var height = $(this).height();
					$(this).css("line-height", height + "px"); 
		        },
				stop: function(e, ui) {
					var height = $(this).height();
					$(this).css("line-height", height + "px");
					updateElem.apply(this, [e, ui]);
		        }
			}).bind("click", function (e) {
				$('#pj_delete_seat').attr('data-rel', $(this).attr("rel"));
				$('#pj_delete_seat').val(myLabel.delete + " " + $(this).attr("title"))
				$('#pj_delete_seat').css('display', 'block');
				$(this).siblings(".rect").removeClass("rect-selected").end().addClass("rect-selected");
			});
			
			$("#mapHolder").click(function (e) {
				
				var $this = $(this),
				index = getMax(),
				t = Math.ceil(e.pageY - offset.top - 10),
				l = Math.ceil(e.pageX - offset.left - 10),
				w = 20,
				h = 20,
				o = {top: t, left: l, width: w, height: h};
				if (!collisionDetect(o)) {
					index++;
					$("<span>", {
						css: {
							"top": t + "px",
							"left": l + "px",
							"width": w + "px",
							"height": h + "px",
							"line-height": h + "px",
							"position": "absolute"
						},
						html: '<span class="bsInnerRect" data-name="hidden_'+index+'">'+index+'</span>',
						rel: "hidden_" + index,
						title: index
					}).addClass("rect empty new").draggable(dragOpts).resizable({
						resize: function(e, ui) {
							var height = $(this).height();
							$(this).css("line-height", height + "px"); 
				        },
						stop: function(e, ui) {
							var height = $(this).height();
							$(this).css("line-height", height + "px"); 
							updateElem.apply(this, [e, ui]);
				        }
					}).bind("click", function (e) {
						$('#pj_delete_seat').attr('data-rel', $(this).attr("rel"));
						$('#pj_delete_seat').val(myLabel.delete + " " + $(this).attr("title"))
						$('#pj_delete_seat').css('display', 'block');
						$(this).siblings(".rect").removeClass("rect-selected").end().addClass("rect-selected");
					}).appendTo($this);
					
					$("<input>", {
						type: "hidden",
						name: "seats_new[]",
						id: "hidden_" + index
					}).val(['x', w, h, l, t, index, '1', '1'].join("|")).appendTo($("#hiddenHolder"));
					
				} else {
					if (window.console && window.console.log) {
					}
				}
			});
			
			if ($dialogDel.length > 0) {
				$dialogDel.dialog({
					autoOpen: false,
					resizable: false,
					draggable: false,
					modal: true,
					buttons: (function () {
						var buttons = {};
						buttons[bsApp.locale.button.delete] = function () {
							$.ajax({
								type: "POST",
								data: {
									id: $(this).data('lang')
								},
								url: "index.php?controller=pjAdminBusTypes&action=pjActionDeleteMap",
								success: function (data) {
									if(data != '100')
									{
										$boxMap.html(data);
										$('#seats_count').parent().parent().css('display', 'block');
									}
								}
							});
							$dialogDel.dialog('close');
						};
						buttons[bsApp.locale.button.cancel] = function () {
							$dialogDel.dialog('close');
						};
						
						return buttons;
					})()
				});
			}
		}
		
		function formatMap(val, obj) {
			return val != null ? myLabel.yes : myLabel.no ;
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
		
		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminBusTypes&action=pjActionUpdate&id={:id}", beforeShow: onBeforeEditShow},
				          {type: "delete", url: "index.php?controller=pjAdminBusTypes&action=pjActionDeleteBusType&id={:id}", beforeShow: onBeforeDeleteShow}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: true, editable: false, width: 280},
				          {text: myLabel.map, type: "text", sortable: false, editable: false, renderer: formatMap, width: 100},
                          {text: myLabel.seats, type: "text", sortable: true, editable: false, width: 120},
                          {text: myLabel.layout, type: "text", sortable: false, editable: false, width: 80},
				          {text: myLabel.status, type: "select", sortable: true, editable: pjGrid.currentRoleId != 10, width: 90, options: [
				                                                                                     {label: myLabel.active, value: "T"}, 
				                                                                                     {label: myLabel.inactive, value: "F"}
				                                                                                     ], applyClass: "pj-status"}],
				dataUrl: "index.php?controller=pjAdminBusTypes&action=pjActionGetBusType",
				dataType: "json",
				fields: ['name', 'seats_map', 'seats_count', 'seat_layout', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=pjAdminBusTypes&action=pjActionDeleteBusTypeBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.revert_status, url: "index.php?controller=pjAdminBusTypes&action=pjActionStatusBusType", render: true},
					   {text: myLabel.exported, url: "index.php?controller=pjAdminBusTypes&action=pjActionExportBusType", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminBusTypes&action=pjActionSaveBusType&id={:id}",
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
			$grid.datagrid("load", "index.php?controller=pjAdminBusTypes&action=pjActionGetBusType", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=pjAdminBusTypes&action=pjActionGetBusType", "name", "ASC", content.page, content.rowCount);
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
			$.post("index.php?controller=pjAdminBusTypes&action=pjActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=pjAdminBusTypes&action=pjActionGetBusType");
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
			$grid.datagrid("load", "index.php?controller=pjAdminBusTypes&action=pjActionGetBusType", "name", "ASC", content.page, content.rowCount);
			return false;
		}).on("change", "#seats_map", function (e) {
			if($(this).val() != '')
			{
				$('#seats_count').val('');
				$('#seats_count').parent().parent().css('display', 'none');
			}
			return false;
		}).on("click", ".pj-delete-map", function (e) {
            $dialogDel.data('lang', $(this).attr('lang')).dialog('open');
		}).on("click", "#pj_delete_seat", function (e) {
			var rel = $(this).attr('data-rel');
			$("#" + rel, $("#hiddenHolder")).remove();				
			$(".rect-selected[rel='"+ rel +"']", $("#mapHolder")).remove();
			$(this).css('display', 'none');
		});
	});
})(jQuery_1_8_2);