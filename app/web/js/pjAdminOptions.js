var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmTicketTemplate = $("#frmTicketTemplate"),
			$frmUpdateSettings = $("#frmUpdateSettings"),
			$dialogDelete = $("#dialogDeleteImage"),
			dialog = ($.fn.dialog !== undefined),
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
		$(".field-int").spinner({
			min: 0
		});
		if ($frmUpdateSettings.length > 0) 
		{
			$frmUpdateSettings.validate({
				rules:{
					"value-int-o_deposit_payment":{
						positiveNumber: true
					},
					"value-int-o_tax_payment":{
						positiveNumber: true
					},
					"value-int-o_min_hour":{
						positiveNumber: true
					}
				},
				errorPlacement: function (error, element) {
					if(element.attr('name') == 'value-int-o_min_hour')
					{
						error.insertAfter(element.parent().parent());
					}else{
						error.insertAfter(element.parent());
					}
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ""
			});
			$.validator.addMethod('positiveNumber',
			    function (value) { 
			        return Number(value) >= 0;
			    }, 
			    myLabel.positive_number
			);
		}
		if ($frmTicketTemplate.length > 0) 
		{
			tinymce.init({
			    selector: "textarea.mceEditor",
			    theme: "modern",
			    width: 550,
			    plugins: [
			         "advlist autolink link image lists charmap print preview hr anchor pagebreak",
			         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			         "save table contextmenu directionality emoticons template paste textcolor"
			   ],
			   toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons"
			 });
		}
		
		function reDrawCode() {
			var code = $("#hidden_code").text(),
				locale = $("select[name='install_locale']").find("option:selected").val(),
				hide = $("input[name='install_hide']").is(":checked") ? "&hide=1" : "";
			locale = parseInt(locale.length, 10) > 0 ? "&locale=" + locale : "";
						
			$("#install_code").text(code.replace(/&action=pjActionLoadJS/g, function(match) {
	            return ["&action=pjActionLoad", locale, hide].join("");
	        }));
			
			$('.pjBrsPreviewUrl').each(function(){
				var href = $(this).attr('data-href');
				href = href.replace("{LOCALE}", locale);
				href = href.replace("{HIDE}", hide);
				$(this).attr('href', href);
			});
		}
		
		if ($dialogDelete.length > 0 && dialog) 
		{
			$dialogDelete.dialog({
				modal: true,
				autoOpen: false,
				resizable: false,
				draggable: false,
				width: 400,
				buttons: (function () {
					var buttons = {};
					buttons[bsApp.locale.button.delete] = function () {
						$.ajax({
							type: "GET",
							dataType: "json",
							url: $dialogDelete.data('href'),
							success: function (res) {
								if(res.code == 200){
									$('#image_container').remove();
									$dialogDelete.dialog('close');
								}
							}
						});
					};
					buttons[bsApp.locale.button.cancel] = function () {
						$dialogDelete.dialog("close");
					};
					
					return buttons;
				})()
			});
		}
		
		$("#content").on("focusin", ".textarea_install", function (e) {
			$(this).select();
		}).on("change", "select[name='value-enum-o_send_email']", function (e) {
			switch ($("option:selected", this).val()) {
			case 'mail|smtp::mail':
				$(".boxSmtp").hide();
				break;
			case 'mail|smtp::smtp':
				$(".boxSmtp").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_allow_paypal']", function (e) {
			switch ($("option:selected", this).val()) {
			case 'Yes|No::No':
				$(".boxPaypal").hide();
				break;
			case 'Yes|No::Yes':
				$(".boxPaypal").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_allow_authorize']", function (e) {
			switch ($("option:selected", this).val()) {
			case 'Yes|No::No':
				$(".boxAuthorize").hide();
				break;
			case 'Yes|No::Yes':
				$(".boxAuthorize").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_allow_bank']", function (e) {
			switch ($("option:selected", this).val()) {
			case 'Yes|No::No':
				$(".boxBankAccount").hide();
				break;
			case 'Yes|No::Yes':
				$(".boxBankAccount").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_email_confirmation']", function (e) {
			switch ($("option:selected", this).val()) {
			case '0|1::0':
				$(".boxClientConfirmation").hide();
				break;
			case '0|1::1':
				$(".boxClientConfirmation").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_email_payment']", function (e) {
			switch ($("option:selected", this).val()) {
			case '0|1::0':
				$(".boxClientPayment").hide();
				break;
			case '0|1::1':
				$(".boxClientPayment").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_email_notify']", function (e) {
			switch ($("option:selected", this).val()) {
			case '0|1::0':
				$(".boxClientNotify").hide();
				break;
			case '0|1::1':
				$(".boxClientNotify").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_email_cancel']", function (e) {
			switch ($("option:selected", this).val()) {
			case '0|1::0':
				$(".boxClientCancel").hide();
				break;
			case '0|1::1':
				$(".boxClientCancel").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_admin_email_confirmation']", function (e) {
			switch ($("option:selected", this).val()) {
			case '0|1::0':
				$(".boxAdminConfirmation").hide();
				break;
			case '0|1::1':
				$(".boxAdminConfirmation").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_admin_email_payment']", function (e) {
			switch ($("option:selected", this).val()) {
			case '0|1::0':
				$(".boxAdminPayment").hide();
				break;
			case '0|1::1':
				$(".boxAdminPayment").show();
				break;
			}
		}).on("change", "select[name='value-enum-o_admin_email_cancel']", function (e) {
			switch ($("option:selected", this).val()) {
			case '0|1::0':
				$(".boxAdminCancel").hide();
				break;
			case '0|1::1':
				$(".boxAdminCancel").show();
				break;
			}
		}).on("change", "select[name='install_locale']", function(e) {
            
            reDrawCode.call(null);
           
		}).on("change", "input[name='install_hide']", function (e) {
			
			reDrawCode.call(null);
			
		}).on("click", ".pj-delete-image", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$dialogDelete.data('href', $(this).data('href')).dialog("open");
		}).on("click", ".pj-use-theme", function (e) {
			var theme = $(this).attr('data-theme'),
				href = $('#pj_preview_install').attr('href');
			$('.pj-loader').css('display', 'block');
			$.ajax({
				type: "GET",
				async: false,
				url: 'index.php?controller=pjAdminOptions&action=pjActionUpdateTheme&theme=' + theme,
				success: function (data) {
					$('.theme-holder').html(data);
					$('.pj-loader').css('display', 'none');
				}
			});
		});
	});
})(jQuery_1_8_2);