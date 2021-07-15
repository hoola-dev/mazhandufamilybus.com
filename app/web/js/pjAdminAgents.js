var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
        var $frmUpdateCredit = $("#frmUpdateCredit"),
			datagrid = ($.fn.datagrid !== undefined);		

        if ($frmUpdateCredit.length > 0) {
			$frmUpdateCredit.validate({
				rules: {
					"credit_password": "required"
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		
		function formatDefault (str, obj) {
			if (obj.role_id == 3) {
				return '<a href="#" class="pj-status-icon pj-status-' + (str == 'F' ? '0' : '1') + '" style="cursor: ' +  (str == 'F' ? 'pointer' : 'default') + '"></a>';
			} else {
				return '<a href="#" class="pj-status-icon pj-status-1" style="cursor: default"></a>';
			}
		}
		function formatRole (str) {
			return ['<span class="label-status user-role-', str, '">', str, '</span>'].join("");
		}
		
		function onBeforeShow (obj) {
			if (parseInt(obj.id, 10) === pjGrid.currentUserId || parseInt(obj.id, 10) === 1 || pjGrid.currentRoleId == 10) {
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
				buttons: [
                            {type: "money", url: "index.php?controller=pjAdminAgents&action=pjActionCredit&id={:id}", beforeShow: onBeforeShow},
                            {type: "search", url: "index.php?controller=pjAdminAgents&action=pjActionCommission&id={:id}", beforeShow: onBeforeDeleteShow}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: true, editable: false},
                          {text: myLabel.email, type: "text", sortable: true, editable: false},	
                          {text: myLabel.user_code, type: "text", sortable: true, editable: false},			          
                          {text: myLabel.credit, type: "text", sortable: true, editable: false},
                          {text: myLabel.commission_percent, type: "text", sortable: true, editable: false},
                          {text: myLabel.total_commission, type: "text", sortable: true, editable: false},
				          {text: myLabel.status, type: "select", sortable: true, editable: false, options: [
						{label: myLabel.active, value: "T"}, 
						{label: myLabel.inactive, value: "F"}
						], applyClass: "pj-status"}],
				dataUrl: "index.php?controller=pjAdminAgents&action=pjActionGetAgent",
				dataType: "json",
				fields: ['name', 'email', 'user_code', 'credit', 'commission_percent', 'total_commission', 'status'],
				paginator: {					
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
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
			$grid.datagrid("load", "index.php?controller=pjAdminAgents&action=pjActionGetAgent", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=pjAdminAgents&action=pjActionGetAgent", "name", "ASC", content.page, content.rowCount);
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
			$.post("index.php?controller=pjAdminAgents&action=pjActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=pjAdminAgents&action=pjActionGetAgent");
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
			$grid.datagrid("load", "index.php?controller=pjAdminAgents&action=pjActionGetAgent", "id", "ASC", content.page, content.rowCount);
			return false;
		});
	});

	
})(jQuery_1_8_2);

function save_credit(){
	var credit_update = jQuery_1_8_2.trim(jQuery_1_8_2("#credit_update").val());
	var credit = jQuery_1_8_2.trim(jQuery_1_8_2("#credit").val());
	var user_id = jQuery_1_8_2.trim(jQuery_1_8_2("#user_id").val());

	var post_data= {
	  'credit_update': credit_update,
	  'credit': credit,
	  'user_id': user_id
	};
	jQuery_1_8_2("#frmUpdateCredit").fadeTo('slow',0.2,function(){
		jQuery_1_8_2.post(
			base_url+'index.php?controller=pjAdminAgents&action=pjActionCreditUpdate&rand='+Math.random(),
			post_data,
			function(data){
				if (data['success']) {
					window.location.href = base_url+'index.php?controller=pjAdminAgents&action=pjActionIndex'; 
				} else {
					alert(data['message']);
				}
				jQuery_1_8_2("#frmUpdateCredit").fadeTo('slow',1);
			},
			'json'
		);
	});
}

