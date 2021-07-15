var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreateUser = $("#frmCreateUser"),
            $frmUpdateUser = $("#frmUpdateUser"),
            $frmUpdateCredit = $("#frmUpdateCredit"),
			datagrid = ($.fn.datagrid !== undefined);

		if ($frmCreateUser.length > 0) {
			$frmCreateUser.validate({
				rules: {
					"email": {
						required: true,
						email: true,
						remote: "index.php?controller=pjAdminUsers&action=pjActionCheckEmail"
					}
				},
				messages: {
					"email": {
						remote: myLabel.email_taken
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		if ($frmUpdateUser.length > 0) {
			$frmUpdateUser.validate({
				rules: {
					"email": {
						required: true,
						email: true,
						remote: "index.php?controller=pjAdminUsers&action=pjActionCheckEmail&id=" + $frmUpdateUser.find("input[name='id']").val()
					}
				},
				messages: {
					"email": {
						remote: myLabel.email_taken
					}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
        }

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
        
        function onBeforeShow2 (obj) {
			if (parseInt(obj.agent_type, 10) != 2 || pjGrid.currentRoleId == 10) {
				return false;
			}
			return true;
        }
        
        function onBeforeShow3 (obj) {
			if (pjGrid.currentRoleId == 10) {
				return false;
			}
			return true;
		}
		
		if ($("#grid").length > 0 && datagrid) {
			
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminUsers&action=pjActionUpdate&id={:id}", beforeShow: onBeforeShow3},						  
                          {type: "delete", url: "index.php?controller=pjAdminUsers&action=pjActionDeleteUser&id={:id}", beforeShow: onBeforeShow},
                          {type: "search", url: "index.php?controller=pjAdminUsers&action=pjActionSubAgents&id={:id}", beforeShow: onBeforeShow2}
				          ],
				columns: [{text: myLabel.name, type: "text", sortable: true, editable: pjGrid.currentRoleId != 10},
				          {text: myLabel.email, type: "text", sortable: true, editable: pjGrid.currentRoleId != 10},
				          {text: myLabel.created, type: "date", sortable: true, editable: false, renderer: $.datagrid._formatDate, dateFormat: pjGrid.jsDateFormat},						  
                          {text: myLabel.role, type: "text", sortable: true, editable: false, renderer: formatRole},
                          {text: myLabel.at_type, type: "text", sortable: true, editable: pjGrid.currentRoleId != 10},
				          {text: myLabel.status, type: "select", sortable: true, editable: pjGrid.currentRoleId != 10, options: [
						{label: myLabel.active, value: "T"}, 
						{label: myLabel.inactive, value: "F"}
						], applyClass: "pj-status"}],
				dataUrl: "index.php?controller=pjAdminUsers&action=pjActionGetUser",
				dataType: "json",
				fields: ['name', 'email', 'created','role', 'at_type', 'status'],
				paginator: {
					actions: [
					   {text: myLabel.delete_selected, url: "index.php?controller=pjAdminUsers&action=pjActionDeleteUserBulk", render: true, confirmation: myLabel.delete_confirmation},
					   {text: myLabel.revert_status, url: "index.php?controller=pjAdminUsers&action=pjActionStatusUser", render: true},
					   {text: myLabel.exported, url: "index.php?controller=pjAdminUsers&action=pjActionExportUser", ajax: false}
					],
					gotoPage: true,
					paginate: true,
					total: true,
					rowCount: true
				},
				saveUrl: "index.php?controller=pjAdminUsers&action=pjActionSaveUser&id={:id}",
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
			$grid.datagrid("load", "index.php?controller=pjAdminUsers&action=pjActionGetUser", "name", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=pjAdminUsers&action=pjActionGetUser", "name", "ASC", content.page, content.rowCount);
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
			$.post("index.php?controller=pjAdminUsers&action=pjActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=pjAdminUsers&action=pjActionGetUser");
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
			$grid.datagrid("load", "index.php?controller=pjAdminUsers&action=pjActionGetUser", "id", "ASC", content.page, content.rowCount);
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
			base_url+'index.php?controller=pjAdminUsers&action=pjActionCreditUpdate&rand='+Math.random(),
			post_data,
			function(data){
				if (data['success']) {
					window.location.href = base_url+'index.php?controller=pjAdminUsers&action=pjActionIndex'; 
				} else {
					alert(data['message']);
				}
				jQuery_1_8_2("#frmUpdateCredit").fadeTo('slow',1);
			},
			'json'
		);
	});
}

function show_hide_agent_type() {
    var role_id = jQuery_1_8_2.trim(jQuery_1_8_2("#role_id").val());

    if (role_id == 2) {
        jQuery_1_8_2("#p_agent_type").css('display','block');
        jQuery_1_8_2("#p_agent_commission").css('display','block');
        jQuery_1_8_2("#p_agent_code").css('display','block');
        jQuery_1_8_2("#p_agent_mobile_app_status").css('display','block');
    } else {
        jQuery_1_8_2("#p_agent_type").css('display','none');
        jQuery_1_8_2("#p_agent_commission").css('display','none');
        jQuery_1_8_2("#p_agent_code").css('display','none');
        jQuery_1_8_2("#p_agent_mobile_app_status").css('display','none');
    }
}

function show_hide_open_close() {
    var agent_type = jQuery_1_8_2.trim(jQuery_1_8_2("#agent_type").val());

    if (agent_type == 4) {
        jQuery_1_8_2("#p_agent_open").css('display','block');
        jQuery_1_8_2("#p_agent_close").css('display','block');
    } else {
        jQuery_1_8_2("#p_agent_open").css('display','none');
        jQuery_1_8_2("#p_agent_close").css('display','none');
    }
    if (agent_type == 1) {
        jQuery_1_8_2("#commission_percent").removeClass('required');
    } else {
        jQuery_1_8_2("#commission_percent").addClass('required');
    }
}

