var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
        "use strict";	
        var datagrid = ($.fn.datagrid !== undefined);
        
		if ($("#grid").length > 0 && datagrid) {
			var $grid = $("#grid").datagrid({
				buttons: [{type: "edit", url: "index.php?controller=pjAdminNews&action=pjActionUpdate&id={:nw_id}"},						  
				          {type: "delete", url: "index.php?controller=pjAdminNews&action=pjActionDeleteNews&id={:nw_id}", beforeShow: onBeforeShow}
				          ],
				columns: [{text: myLabel.image, type: "text", sortable: false, editable: false},
                          {text: myLabel.title, type: "text", sortable: true, editable: false},	
                          {text: myLabel.date, type: "text", sortable: true, editable: false},			          
                          {text: myLabel.description, type: "text", sortable: true, editable: false},
                          {text: myLabel.link, type: "text", sortable: true, editable: false},
                          {text: myLabel.active, type: "text", sortable: true, editable: false}
				          ],
				dataUrl: "index.php?controller=pjAdminNews&action=pjActionGetNews",
				dataType: "json",
				fields: ['nw_image', 'nw_title', 'nw_date', 'nw_description', 'nw_link', 'nw_is_active'],
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
			$grid.datagrid("load", "index.php?controller=pjAdminNews&action=pjActionGetNews", "nw_date", "DESC", content.page, content.rowCount);
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
			$grid.datagrid("load", "index.php?controller=pjAdminNews&action=pjActionGetNews", "nw_date", "DESC", content.page, content.rowCount);
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
			$.post("index.php?controller=pjAdminNews&action=pjActionSetActive", {
				id: $(this).closest("tr").data("object")['id']
			}).done(function (data) {
				$grid.datagrid("load", "index.php?controller=pjAdminNews&action=pjActionGetNews");
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
			$grid.datagrid("load", "index.php?controller=pjAdminNews&action=pjActionGetNews", "nw_date", "DESC", content.page, content.rowCount);
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

function add_news(){
	var nw_title = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_title").val());
	var nw_date = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_date").val());
    var nw_description = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_description").val());
    var nw_link = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_link").val());
    var nw_is_active = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_is_active").val());

    if (!nw_title || !nw_date || !nw_description) {
        alert('Please enter Title, Date and Description');
    } else {
        var post_data = new FormData(); 
        jQuery_1_8_2.each(jQuery_1_8_2('#nw_image')[0].files, function(i, file) {
            post_data.append('nw_image', file);
        });
        post_data.append('nw_title', nw_title);
        post_data.append('nw_date', nw_date);
        post_data.append('nw_description', nw_description);
        post_data.append('nw_link', nw_link);
        post_data.append('nw_is_active', nw_is_active);

        jQuery_1_8_2('#frmNews').fadeTo('slow', 0.2, function() {
            jQuery_1_8_2.ajax({
                url: base_url+'index.php?controller=pjAdminNews&action=pjActionAdd&rand='+Math.random(),
                data: post_data,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                success: function(data){
    
                    if (data['success'] == 1) {
                        alert(data['message']);                        
                        location.href = base_url+'index.php?controller=pjAdminNews&action=pjActionIndex';                  
                    } else {
                        alert(data['message']);
                    }
                    jQuery_1_8_2('#frmNews').fadeTo('slow', 1);
                },
                dataType: 'json'
            }); 
        });
    }
}

function edit_news(){
	var nw_title = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_title").val());
	var nw_date = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_date").val());
    var nw_description = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_description").val());
    var nw_link = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_link").val());
    var nw_is_active = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_is_active").val());
    var nw_id = jQuery_1_8_2.trim(jQuery_1_8_2("#nw_id").val());

    if (!nw_title || !nw_date || !nw_description) {
        alert('Please enter Title, Date and Description');
    } else {
        var post_data = new FormData(); 
        jQuery_1_8_2.each(jQuery_1_8_2('#nw_image')[0].files, function(i, file) {
            post_data.append('nw_image', file);
        });
        post_data.append('nw_title', nw_title);
        post_data.append('nw_date', nw_date);
        post_data.append('nw_description', nw_description);
        post_data.append('nw_link', nw_link);
        post_data.append('nw_is_active', nw_is_active);
        post_data.append('nw_id', nw_id);

        jQuery_1_8_2('#frmNews').fadeTo('slow', 0.2, function() {
            jQuery_1_8_2.ajax({
                url: base_url+'index.php?controller=pjAdminNews&action=pjActionEdit&rand='+Math.random(),
                data: post_data,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                success: function(data){
    
                    if (data['success'] == 1) {
                        alert(data['message']);                        
                        location.href = base_url+'index.php?controller=pjAdminNews&action=pjActionIndex';                  
                    } else {
                        alert(data['message']);
                    }
                    jQuery_1_8_2('#frmNews').fadeTo('slow', 1);
                },
                dataType: 'json'
            }); 
        });        
    }
}

