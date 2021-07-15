var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
        "use strict";	
        var datagrid = ($.fn.datagrid !== undefined);
        
		if ($("#grid").length > 0 && datagrid) {
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminFAQ&action=pjActionUpdate&id={:fq_id}"},						  
				          {type: "delete", url: "index.php?controller=pjAdminFAQ&action=pjActionDeleteFAQ&id={:fq_id}", beforeShow: onBeforeShow}
				          ],
				columns: [{text: myLabel.title, type: "text", sortable: true, editable: false},	                          			          
                          {text: myLabel.description, type: "text", sortable: true, editable: false},
                          {text: myLabel.active, type: "text", sortable: true, editable: false}
				          ],
				dataUrl: "index.php?controller=pjAdminFAQ&action=pjActionGetFAQ",
				dataType: "json",
				fields: ['fq_title', 'fq_description', 'fq_is_active'],
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
			$grid.datagrid("load", "index.php?controller=pjAdminFAQ&action=pjActionGetFAQ", "fq_title", "ASC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=pjAdminFAQ&action=pjActionGetFAQ", "fq_title", "ASC", content.page, content.rowCount);
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
			$.post("index.php?controller=pjAdminFAQ&action=pjActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=pjAdminFAQ&action=pjActionGetFAQ");
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
			$grid.datagrid("load", "index.php?controller=pjAdminFAQ&action=pjActionGetFAQ", "fq_title", "ASC", content.page, content.rowCount);
			return false;
		});
        
        function onBeforeShow (obj) {			
			return true;
		}
		
		$(document).on("focusin", ".datepick", function (e) {
			var minDate, maxDate,
				$this = $(this),
				custom = {},
				o = {
					firstDay: $this.attr("rel"),
					dateFormat: $this.attr("rev"),				
			};
			$this.datepicker($.extend(o, custom));
		});
	});

	
})(jQuery_1_8_2);

function add_faq(){
	var fq_title = jQuery_1_8_2.trim(jQuery_1_8_2("#fq_title").val());
    var fq_description = jQuery_1_8_2.trim(jQuery_1_8_2("#fq_description").val());
    var fq_is_active = jQuery_1_8_2.trim(jQuery_1_8_2("#fq_is_active").val());

    if (!fq_title || !fq_description) {
        alert('Please enter Title and Description');
    } else {
        var post_data = {
            'fq_title': fq_title,
            'fq_description': fq_description,
            'fq_is_active': fq_is_active
        };

        jQuery_1_8_2('#frmFAQ').fadeTo('slow', 0.2, function() {
            jQuery_1_8_2.post(
            base_url+'index.php?controller=pjAdminFAQ&action=pjActionAdd&rand='+Math.random(),
            post_data,
            function(data){
                if (data['success'] == 1) {
                    alert(data['message']);                        
                    location.href = base_url+'index.php?controller=pjAdminFAQ&action=pjActionIndex';                  
                } else {
                    alert(data['message']);
                }
                jQuery_1_8_2('#frmFAQ').fadeTo('slow', 1);
            },
            'json'
            );
        });
    }
}

function edit_faq(){
	var fq_title = jQuery_1_8_2.trim(jQuery_1_8_2("#fq_title").val());
    var fq_description = jQuery_1_8_2.trim(jQuery_1_8_2("#fq_description").val());
    var fq_is_active = jQuery_1_8_2.trim(jQuery_1_8_2("#fq_is_active").val());
    var fq_id = jQuery_1_8_2.trim(jQuery_1_8_2("#fq_id").val());

    if (!fq_title || !fq_description) {
        alert('Please enter Title and Description');
    } else {
        var post_data = {
            'fq_title': fq_title,
            'fq_description': fq_description,
            'fq_is_active': fq_is_active,
            'fq_id': fq_id
        };

        jQuery_1_8_2('#frmFAQ').fadeTo('slow', 0.2, function() {
            jQuery_1_8_2.post(
            base_url+'index.php?controller=pjAdminFAQ&action=pjActionEdit&rand='+Math.random(),
            post_data,
            function(data){
                if (data['success'] == 1) {
                    alert(data['message']);                        
                    location.href = base_url+'index.php?controller=pjAdminFAQ&action=pjActionIndex';                  
                } else {
                    alert(data['message']);
                }
                jQuery_1_8_2('#frmFAQ').fadeTo('slow', 1);
            },
            'json'
            );
        });
    }
}

