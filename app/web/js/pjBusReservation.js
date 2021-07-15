/*!
 * Bus Reservation System v1.0
 * https://www.parezagroup.com/bus-reservation-system/
 * 
 * Copyright 2014, StivaSoft Ltd.
 * 
 */
(function (window, undefined){
	"use strict";
	pjQ.$.ajaxSetup({
		xhrFields: {
			withCredentials: true
		}
	});
	var document = window.document,
		validate = (pjQ.$.fn.validate !== undefined),
		datepicker = (pjQ.$.fn.datepicker !== undefined),
		dialog = (pjQ.$.fn.dialog !== undefined),
		routes = [
		          	{pattern: /^#!\/Search$/, eventName: "loadSearch"},
		          	{pattern: /^#!\/Seats$/, eventName: "loadSeats"},
		          	{pattern: /^#!\/Checkout$/, eventName: "loadCheckout"},
		          	{pattern: /^#!\/Preview$/, eventName: "loadPreview"}
		          ];
	
	function log() {
		if (window.console && window.console.log) {
			for (var x in arguments) {
				if (arguments.hasOwnProperty(x)) {
					window.console.log(arguments[x]);
				}
			}
		}
	}
	
	function assert() {
		if (window && window.console && window.console.assert) {
			window.console.assert.apply(window.console, arguments);
		}
	}
	
	function hashBang(value) {
		if (value !== undefined && value.match(/^#!\//) !== null) {
			if (window.location.hash == value) {
				return false;
			}
			window.location.hash = value;
			return true;
		}
		
		return false;
	}
	
	function onHashChange() {
		var i, iCnt, m;
		for (i = 0, iCnt = routes.length; i < iCnt; i++) {
			m = window.location.hash.match(routes[i].pattern);
			if (m !== null) {
				pjQ.$(window).trigger(routes[i].eventName, m.slice(1));
				break;
			}
		}
		if (m === null) {
			pjQ.$(window).trigger("loadSearch");
		}
	}
	pjQ.$(window).on("hashchange", function (e) {
    	onHashChange.call(null);
    });
	
	function BusReservation(opts) {
		if (!(this instanceof BusReservation)) {
			return new BusReservation(opts);
		}
				
		this.reset.call(this);
		this.init.call(this, opts);
		
		return this;
	}
	
	BusReservation.inObject = function (val, obj) {
		var key;
		for (key in obj) {
			if (obj.hasOwnProperty(key)) {
				if (obj[key] == val) {
					return true;
				}
			}
		}
		return false;
	};
	
	BusReservation.size = function(obj) {
		var key,
			size = 0;
		for (key in obj) {
			if (obj.hasOwnProperty(key)) {
				size += 1;
			}
		}
		return size;
	};
	
	BusReservation.prototype = {
		reset: function () {
			this.$container = null;
			this.container = null;
			this.pickup_id = null;
			this.bus_id = null;
			this.total_tickets = 0;
			this.opts = {};
			
			return this;
		},
		disableButtons: function () {
			this.$container.find(".btn").each(function (i, el) {
				pjQ.$(el).attr("disabled", "disabled");
			});
        },
        hideButtons: function () {
			this.$container.find(".btn").each(function (i, el) {
				pjQ.$(el).css("display", "none");
			});
		},
		enableButtons: function () {
			this.$container.find(".btn").removeAttr("disabled");
		},
		
		
		init: function (opts) {
			var self = this;
			this.opts = opts;
			this.container = document.getElementById("pjBrContainer_" + this.opts.index);
			this.$container = pjQ.$(this.container);
			
			pjQ.$("html").attr('dir',self.opts.direction);
			
			pjQ.$.validator.addMethod('checkExpired',
				    function (value) { 
						var payment_method = pjQ.$('#bsPaymentMethod_' + self.opts.index).val(),
							exp_month = pjQ.$('#bsExpMonth_' + self.opts.index).val(),
							exp_year = pjQ.$('#bsExpYear_' + self.opts.index).val();
						if(payment_method == 'creditcard')
						{
							if(exp_month != '' && exp_year != '')
							{
								var today = new Date(),
									expiry = new Date(exp_year, exp_month);
								if (today.getTime() > expiry.getTime())
								{
									return false;
								}else{
									return true;
								}
							}else{
								return true;
							}
						}else{
							return true;
						}
				    }, self.opts.validation.cc_expired);
			
			this.$container.on("click.bs", ".bsSelectorLocale", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var locale = pjQ.$(this).data("id");
				var dir = pjQ.$(this).data("dir");
				self.opts.direction = dir;
				self.opts.locale = locale;
				pjQ.$(this).addClass("bsLocaleFocus").parent().parent().find("a.bsSelectorLocale").not(this).removeClass("bsLocaleFocus");
				
				pjQ.$.get([self.opts.folder, "index.php?controller=pjFront&action=pjActionLocale"].join(""), {
					"locale_id": locale,
					"session_id": self.opts.session_id
				}).done(function (data) {
					pjQ.$("html").attr('dir',dir);
					if(window.location.hash == '')
					{
						self.loadSearch.call(self);
					}else{
						var location_hash = window.location.hash;
						if (!hashBang(location_hash))
						{
							location_hash = location_hash.replace("#!/", "load");
							pjQ.$(window).trigger(location_hash);
						}
					}
				}).fail(function () {
					log("Deferred is rejected");
				});
				return false;
			}).on("click.bs", ".bsStepClickable, .bsStepPassed", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var step = pjQ.$(this).attr('data-step');
				switch(step){
					case '1':
						hashBang("#!/Search");
				        break;
					case '2':
						hashBang("#!/Seats");
				        break;
					case '3':
						hashBang("#!/Checkout");
				        break;
					case '4':
						hashBang("#!/Preview");
				        break;
				    default:
				    	hashBang("#!/Search");
				} 
			}).on("focusin.bs", ".bsSelectorDatepick", function (e) {
				if (datepicker) {
					var $this = pjQ.$(this),
						current_date = $this.val(),
						dOpts = {
						dateFormat: $this.data("dformat"),
						firstDay: $this.data("fday"),
						minDate: 0,
						dayNames: ($this.data("day")).split(","),
					    monthNames: ($this.data("months")).split(","),
					    monthNamesShort: ($this.data("shortmonths")).split(","),
					    dayNamesMin: ($this.data("daymin")).split(","),
						onClose: function(selectedDate){
							pjQ.$('.bsCheckErrorMsg').css('display', 'none');
							pjQ.$('.bsCheckReturnErrorMsg').css('display', 'none');
						}
					};
					$this.datepicker(dOpts);
				}
			}).on("click.bs", ".bsChangeDate", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				hashBang("#!/Search");
			}).on("click.bs", ".bsChangeSeat", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				hashBang("#!/Seats");
			}).on("change.bs", "#bsPickupId_" + this.opts.index, function (e) {
				
				self.disableButtons.call(self);
				pjQ.$.get([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionGetLocations"].join(""), {
					"locale": self.opts.locale,
					"hide": self.opts.hide,
					"index": self.opts.index,
					"pickup_id": pjQ.$('#bsPickupId_' + self.opts.index).val(),
					"session_id": self.opts.session_id
				}).done(function (data) {
					pjQ.$('#bsReturnContainer_' + self.opts.index).html(data);
					pjQ.$("#bsReturnId_" + self.opts.index).select2({
						dir: self.fnRtlOrNot.call(self),
						containerCssClass: 'pjBsSelect2Preview',
						dropdownCssClass: 'pjBsSelect2Dropdown'
					});
					pjQ.$('.bsCheckErrorMsg').css('display', 'none');
					pjQ.$('.bsCheckReturnErrorMsg').css('display', 'none');
					self.enableButtons.call(self);
				});
				return false;
			}).on("change.bs", "#bsReturnId_" + this.opts.index, function (e) {
				var pickup_id = pjQ.$('#bsPickupId_' + self.opts.index).val();
				if(pickup_id == '')
				{
					self.disableButtons.call(self);
					pjQ.$.get([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionGetLocations"].join(""), {
						"locale": self.opts.locale,
						"hide": self.opts.hide,
						"index": self.opts.index,
						"return_id": pjQ.$('#bsReturnId_' + self.opts.index).val(),
						"session_id": self.opts.session_id
					}).done(function (data) {
						pjQ.$('#bsPickupContainer_' + self.opts.index).html(data);
						pjQ.$("#bsPickupId_" + self.opts.index).select2({
							dir: self.fnRtlOrNot.call(self),
							containerCssClass: 'pjBsSelect2Preview',
							dropdownCssClass: 'pjBsSelect2Dropdown'
						});
						pjQ.$('.bsCheckErrorMsg').css('display', 'none');
						pjQ.$('.bsCheckReturnErrorMsg').css('display', 'none');
						self.enableButtons.call(self);
					});
				}else{
					pjQ.$('.bsCheckErrorMsg').css('display', 'none');
					pjQ.$('.bsCheckReturnErrorMsg').css('display', 'none');
				}
				return false;
			}).on("change.bs", ".bsTicketSelect", function (e) {
				var $this = pjQ.$(this),
					bus_id = pjQ.$(this).attr('data-bus'),
					seat_map = pjQ.$(this).attr('data-set'),
					total_tickets = 0,
					seats = '',
					$seat_label = pjQ.$("#bsSeats_" + self.opts.index),
					$selected_bus = pjQ.$("#bs_selected_bus_" + self.opts.index),
					selected_bus_id = $selected_bus.val(),
					$selected_seats_label = pjQ.$('#bsSelectedSeatsLabel_' + self.opts.index),
					seat_avail = pjQ.$('#bs_avail_seats_' + bus_id).val(),
					number_of_seats = parseInt(pjQ.$('#bs_number_of_seats_' + bus_id).val(), 10);
				var return_bus_id = null;
				pjQ.$(".bsReturnTicketSelect").prop('disabled', false);
				pjQ.$('.bsReturnTicketSelect').each(function( index ) {
					var tickets = parseInt(pjQ.$( this ).val(), 10);
					if(tickets > 0)
					{
						return_bus_id = pjQ.$( this ).attr('data-bus');
					}
				});
				var $form = pjQ.$('#bsSelectSeatsForm_' + self.opts.index);
				pjQ.$.post([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionGetRoundtripPrice&bus_id=", bus_id, "&return_bus_id=", return_bus_id, "&session_id=", self.opts.session_id].join(""), $form.serialize()).done(function (data) {
					pjQ.$('#bsRoundtripPrice_' + self.opts.index).html(data);
				});
				
				if(selected_bus_id != '')
				{
					if(bus_id != selected_bus_id)
					{
						pjQ.$('.bsTicketSelect-' + selected_bus_id).val(0);
						self.bus_id = bus_id;
						$selected_bus.val(bus_id);
						if(seat_map == 'T')
						{
							$seat_label.parent().parent().css('display', 'block');
							pjQ.$('.pjBsPickupSeatsBody').show();
							pjQ.$('.pjBsPickupSeatsFoot').show();
							self.loadMap(bus_id);
						}else{
							$seat_label.parent().parent().css('display', 'none');
							pjQ.$('.pjBsPickupSeatsBody').hide();
							pjQ.$('.pjBsPickupSeatsFoot').hide();
							self.hideMap();
						}
					}
				}else{
					$selected_bus.val(bus_id);
					if(seat_map == 'T')
					{
						$seat_label.parent().parent().css('display', 'block');
						pjQ.$('.pjBsPickupSeatsBody').show();
						pjQ.$('.pjBsPickupSeatsFoot').show();
						self.loadMap(bus_id);
					}
				}
				pjQ.$('.bsTicketSelect-' + bus_id ).each(function( index ) {
					total_tickets += parseInt(pjQ.$( this ).val(), 10);
				});
				
				var max_seats = number_of_seats - total_tickets;
				self.reCalculatingTickets($this, max_seats, bus_id);
				
				pjQ.$('#bs_selected_tickets_' + self.opts.index).val(total_tickets);
				pjQ.$('#bs_has_map_' + self.opts.index).val(seat_map);
				self.onReselect();
				if(total_tickets != 1)
				{
					seats = total_tickets + ' ' + self.opts.labels.seats;
				}else{
					seats = total_tickets + ' ' + self.opts.labels.seat;
				}
				$seat_label.html(seats);
				if(total_tickets == 0)
				{
					$selected_bus.val('');
					if(seat_map == 'T')
					{
						$seat_label.parent().parent().css('display', 'none');
						pjQ.$('.pjBsPickupSeatsBody').hide();
						pjQ.$('.pjBsPickupSeatsFoot').hide();
						self.hideMap();
					}
					$seat_label.parent().parent().css('display', 'none');
					pjQ.$('.pjBsPickupSeatsBody').hide();
					pjQ.$('.pjBsPickupSeatsFoot').hide();
					pjQ.$('.bsSeatErrorMsg').css('display', 'none');
					
					$selected_seats_label.html("");
					$selected_seats_label.parent().css('display', 'none');
				}else{
					pjQ.$('.bsTicketErrorMsg').css('display', 'none');
					pjQ.$('.bsSeatErrorMsg').css('display', 'none');
					
					if(seat_map == 'F')
					{
						var seat_avail_arr = seat_avail.split("~|~"),
							selected_seats = '',
							selected_name_arr = new Array(),
							selected_id_arr = new Array();
						for(var i=0; i<seat_avail_arr.length; i++)
						{
							var sub_arr = seat_avail_arr[i].split("#");
							selected_id_arr.push(sub_arr[0])
							selected_name_arr.push(sub_arr[1]);
							if((i + 1) == total_tickets)
							{
								break;
							}
						}
						$selected_seats_label.html(selected_name_arr.join(", "));
						$selected_seats_label.parent().css('display', 'none');
						pjQ.$('#bs_selected_seats_' + self.opts.index).val(selected_id_arr.join("|"));
					}
				}
			}).on("change.bs", ".bsReturnTicketSelect", function (e) {
				var $this = pjQ.$(this),
					pickup_bus_id = null,
					bus_id = pjQ.$(this).attr('data-bus'),
					seat_map = pjQ.$(this).attr('data-set'),
					pickup_total_tickets = 0,
					total_tickets = 0,
					seats = '',
					$seat_label = pjQ.$("#bsReturnSeats_" + self.opts.index),
					$selected_bus = pjQ.$("#bs_return_selected_bus_" + self.opts.index),
					selected_bus_id = $selected_bus.val(),
					$selected_seats_label = pjQ.$('#bsReturnSelectedSeatsLabel_' + self.opts.index),
					seat_avail = pjQ.$('#bs_return_avail_seats_' + bus_id).val(),
					number_of_seats = parseInt(pjQ.$('#bs_return_number_of_seats_' + bus_id).val(), 10);
				var return_bus_id = bus_id;
				
				var $form = pjQ.$('#bsSelectSeatsForm_' + self.opts.index);
				pjQ.$('.bsTicketSelect' ).each(function( index ) {
					var tickets = parseInt(pjQ.$( this ).val(), 10);
					pickup_total_tickets += tickets;
					if(tickets > 0)
					{
						pickup_bus_id = pjQ.$(this).attr('data-bus');
					}
				});
				
				pjQ.$.post([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionGetRoundtripPrice&bus_id=", pickup_bus_id, "&return_bus_id=", return_bus_id, "&session_id=", self.opts.session_id].join(""), $form.serialize()).done(function (data) {
					pjQ.$('#bsRoundtripPrice_' + self.opts.index).html(data);
				});
				
				if(selected_bus_id != '')
				{
					if(bus_id != selected_bus_id)
					{
						pjQ.$('.bsReturnTicketSelect-' + selected_bus_id).val(0);
						self.bus_id = bus_id;
						$selected_bus.val(bus_id);
						if(seat_map == 'T')
						{
							$seat_label.parent().parent().css('display', 'inline-block');
							pjQ.$('.pjBsReturnSeatsBody').show();
							pjQ.$('.pjBsReturnSeatsFoot').show();
							self.loadReturnMap(bus_id);
						}else{
							$seat_label.parent().parent().css('display', 'none');
							pjQ.$('.pjBsReturnSeatsBody').hide();
							pjQ.$('.pjBsReturnSeatsFoot').hide();
							self.hideReturnMap();
						}
					}
				}else{
					$selected_bus.val(bus_id);
					if(seat_map == 'T')
					{
						$seat_label.parent().parent().css('display', 'inline-block');
						pjQ.$('.pjBsReturnSeatsBody').show();
						pjQ.$('.pjBsReturnSeatsFoot').show();
						self.loadReturnMap(bus_id);
					}
				}
				pjQ.$('.bsReturnTicketSelect').each(function( index ) {
					total_tickets += parseInt(pjQ.$( this ).val(), 10);
				});
				
				var max_seats = number_of_seats - total_tickets;
				self.reCalculatingReturnTickets($this, max_seats, bus_id);
				
				pjQ.$('#bs_return_selected_tickets_' + self.opts.index).val(total_tickets);
				pjQ.$('#bs_return_has_map_' + self.opts.index).val(seat_map);
				self.onReturnReselect();
				if(total_tickets != 1)
				{
					seats = total_tickets + ' ' + self.opts.labels.seats;
				}else{
					seats = total_tickets + ' ' + self.opts.labels.seat;
				}
				$seat_label.html(seats);
				if(total_tickets == 0)
				{
					$selected_bus.val('');
					if(seat_map == 'T')
					{
						$seat_label.parent().parent().css('display', 'none');
						pjQ.$('.pjBsReturnSeatsBody').hide();
						pjQ.$('.pjBsReturnSeatsFoot').hide();
						self.hideReturnMap();
					}
					$seat_label.parent().parent().css('display', 'none');
					pjQ.$('.pjBsReturnSeatsBody').hide();
					pjQ.$('.pjBsReturnSeatsFoot').hide();
					pjQ.$('.bsReturnSeatErrorMsg').css('display', 'none');
					
					$selected_seats_label.html("");
					$selected_seats_label.parent().css('display', 'none');
				}else{
					pjQ.$('.bsReturnTicketErrorMsg').css('display', 'none');
					pjQ.$('.bsReturnSeatErrorMsg').css('display', 'none');
					
					if(seat_map == 'F')
					{
						var seat_avail_arr = seat_avail.split("~|~"),
							selected_seats = '',
							selected_name_arr = new Array(),
							selected_id_arr = new Array();
						for(var i=0; i<seat_avail_arr.length; i++)
						{
							var sub_arr = seat_avail_arr[i].split("#");
							selected_id_arr.push(sub_arr[0])
							selected_name_arr.push(sub_arr[1]);
							if((i + 1) == total_tickets)
							{
								break;
							}
						}
						$selected_seats_label.html(selected_name_arr.join(", "));
						$selected_seats_label.parent().css('display', 'none');
						pjQ.$('#bs_return_selected_seats_' + self.opts.index).val(selected_id_arr.join("|"));
					}
				}
			}).on("click.bs", ".bs-available", function (e) {
				
				var seat_id = pjQ.$(this).attr('data-id'),
					seat_name = pjQ.$(this).attr('data-name'),
					seat_arr = self.getSeatsArray(),
					seat_name_arr = self.getSeatsNameArray(),
					quantity = parseInt(pjQ.$('#bs_selected_tickets_' + self.opts.index).val(), 10),
					$selected_seats_label = pjQ.$('#bsSelectedSeatsLabel_' + self.opts.index);
				
				if(quantity > seat_arr.length && pjQ.$.inArray( seat_id, seat_arr ) == -1)
				{
					pjQ.$(this).addClass('bs-selected');
					seat_arr.push(seat_id);
					pjQ.$('#bs_selected_seats_' + self.opts.index).val(seat_arr.join("|"));
					seat_name_arr.push(seat_name);
					$selected_seats_label.html(seat_name_arr.join(", "));
					
					pjQ.$('.bsReSelect').css('display', 'inline-block');
					pjQ.$('.bsSeatErrorMsg').css('display', 'none');
				}
			}).on("click.bs", ".bs-return-available", function (e) {
				
				var seat_id = pjQ.$(this).attr('data-id'),
					seat_name = pjQ.$(this).attr('data-name'),
					seat_arr = self.getReturnSeatsArray(),
					seat_name_arr = self.getReturnSeatsNameArray(),
					quantity = parseInt(pjQ.$('#bs_return_selected_tickets_' + self.opts.index).val(), 10),
					$selected_seats_label = pjQ.$('#bsReturnSelectedSeatsLabel_' + self.opts.index);
				
				if(quantity > seat_arr.length && pjQ.$.inArray( seat_id, seat_arr ) == -1)
				{
					pjQ.$(this).addClass('bs-return-selected');
					seat_arr.push(seat_id);
					pjQ.$('#bs_return_selected_seats_' + self.opts.index).val(seat_arr.join("|"));
					seat_name_arr.push(seat_name);
					$selected_seats_label.html(seat_name_arr.join(", "));
					
					pjQ.$('.bsReturnReSelect').css('display', 'inline-block');
					pjQ.$('.bsReturnSeatErrorMsg').css('display', 'none');
				}
			}).on("click.bs", ".bsReSelect", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				self.onReselect();
			}).on("click.bs", ".bsReturnReSelect", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				self.onReturnReselect();
			}).on("click.bs", "#bsBtnCancel_" + self.opts.index, function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				self.disableButtons.call(self);
				hashBang("#!/Search");
			}).on("click.bs", "#bsBtnCheckout_" + self.opts.index, function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var number_of_tickets = pjQ.$('#bs_selected_tickets_' + self.opts.index).val(),
					has_map = pjQ.$('#bs_selected_tickets_' + self.opts.index).attr('data-map'),
					valid = true;
				if(number_of_tickets == '' || number_of_tickets == '0')
				{
					pjQ.$('.bsTicketErrorMsg').css('display', 'block');
					pjQ.$('.pjBsPickupSeatsBody').show();
					valid = false;
				}else{
					if (pjQ.$('#bs_return_selected_tickets_' + self.opts.index).length > 0) 
					{
						var return_number_of_tickets = pjQ.$('#bs_return_selected_tickets_' + self.opts.index).val();
						if(return_number_of_tickets == '' || return_number_of_tickets == '0') 
						{
							pjQ.$('.bsReturnTicketErrorMsg').css('display', 'block');
							pjQ.$('.pjBsReturnSeatsBody').show();
							valid = false;
						} else {
							if(has_map == 'T')
							{
								if(parseInt(number_of_tickets, 10) >= parseInt(return_number_of_tickets, 10))
								{
									var selected_seats = pjQ.$('#bs_selected_seats_' + self.opts.index).val();
									if(selected_seats == '')
									{
										pjQ.$('.bsSeatErrorMsg').html(self.opts.validation.required_seat);
										pjQ.$('.bsSeatErrorMsg').css('display', 'block');
										pjQ.$('.pjBsPickupSeatsBody').show();
										valid = false;
									}else{
										var return_selected_seats = pjQ.$('#bs_return_selected_seats_' + self.opts.index).val();
										if(return_selected_seats == '')
										{
											pjQ.$('.bsReturnSeatErrorMsg').html(self.opts.validation.required_seat);
											pjQ.$('.bsReturnSeatErrorMsg').css('display', 'block');
											pjQ.$('.pjBsReturnSeatsBody').show();
											valid = false;
										} else {
											var seat_arr = self.getSeatsArray();
											if(seat_arr.length < parseInt(number_of_tickets, 10))
											{
												var error_msg = self.opts.validation.invalid_seat;
												pjQ.$('.bsSeatErrorMsg').html(error_msg.replace(/\{seats\}/g, number_of_tickets));
												pjQ.$('.bsSeatErrorMsg').css('display', 'block');
												pjQ.$('.pjBsPickupSeatsBody').show();
												valid = false;
											} else {
												var return_seat_arr = self.getReturnSeatsArray();
												if(return_seat_arr.length < parseInt(return_number_of_tickets, 10))
												{
													var error_msg = self.opts.validation.invalid_seat;
													pjQ.$('.bsReturnSeatErrorMsg').html(error_msg.replace(/\{seats\}/g, return_number_of_tickets));
													pjQ.$('.bsReturnSeatErrorMsg').css('display', 'block');
													pjQ.$('.pjBsReturnSeatsBody').show();
													valid = false;
												}											
											}
										}
									}
								}else{
									pjQ.$('#pjBrRoundTripModal').modal('show');
								}
							}
						}
					}else{
						if(has_map == 'T')
						{
							var selected_seats = pjQ.$('#bs_selected_seats_' + self.opts.index).val();
							if(selected_seats == '')
							{
								pjQ.$('.bsSeatErrorMsg').html(self.opts.validation.required_seat);
								pjQ.$('.bsSeatErrorMsg').css('display', 'block');
								valid = false;
							}else{
								var seat_arr = self.getSeatsArray();
								if(seat_arr.length < parseInt(number_of_tickets, 10))
								{
									var error_msg = self.opts.validation.invalid_seat;
									pjQ.$('.bsSeatErrorMsg').html(error_msg.replace(/\{seats\}/g, number_of_tickets));
									pjQ.$('.bsSeatErrorMsg').css('display', 'block');
									valid = false;
								}
							}
						}
					}
					
				}
				if(valid == true)
				{
					var $form = pjQ.$('#bsSelectSeatsForm_' + self.opts.index);
					self.disableButtons.call(self);
					pjQ.$.post([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionSaveTickets", "&session_id=", self.opts.session_id].join(""), $form.serialize()).done(function (data) {
						hashBang("#!/Checkout");
					}).fail(function () {
						log("Deferred is rejected");
					});
				}
			}).on("change.bs", "#bsPaymentMethod_" + self.opts.index, function (e) {
				var $cc_data = pjQ.$("#bsCCData_" + self.opts.index);
				if(pjQ.$(this).val() == 'creditcard'){
					$cc_data.css('display', 'block');
				}else{
					$cc_data.css('display', 'none');
				}
			}).on("click.bs", "#bsBtnTerms_" + self.opts.index, function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var $terms = pjQ.$("#bsTermContainer_" + self.opts.index);
				if($terms.is(':visible')){
					$terms.css('display', 'none');
				}else{
					$terms.css('display', 'block');
				}
			}).on("click.bs", "#bsBtnCancel_" + self.opts.index, function (e) {
				self.disableButtons.call(self);
				hashBang("#!/Search");
			}).on("click.bs", "#bsBtnBack_" + self.opts.index, function (e) {
				self.disableButtons.call(self);
				hashBang("#!/Checkout");
			}).on("click.bs", "#bsBtnBack3_" + self.opts.index, function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				self.disableButtons.call(self);
				hashBang("#!/Seats");
			}).on("click.bs", "#bsBtnBack4_" + self.opts.index, function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				self.disableButtons.call(self);
				hashBang("#!/Checkout");
			}).on("click.bs", "#bsBtnPreview_" + self.opts.index, function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}                

				var $frmCheckout = pjQ.$('#bsCheckoutForm_' + self.opts.index);
				
				$frmCheckout.validate({
					rules: {
                        "c_phone": {
                            required : function() {
                                if (pjQ.$("#bsPaymentMethod_"+self.opts.index).val() == 'cgrate') {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        },
                        "c_email": {
                            required : function() {
                                if (pjQ.$("#bsPaymentMethod_"+self.opts.index).val() == 'flutterwave') {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        },
						"cc_exp_month": {
							checkExpired: true						
                        },                        
						"captcha": {
                            required: true,
							remote: self.opts.folder + "index.php?controller=pjFrontEnd&action=pjActionCheckCaptcha&session_id=" + self.opts.session_id
						},
						"agreement": {
							required: true
                        }
					},
					messages: {
						"c_title": {
							required: self.opts.validation.required_field
						},
						"c_fname": {
							required: self.opts.validation.required_field
						},
						"c_lname": {
							required: self.opts.validation.required_field
                        },
                        "c_phone_country": {
							required: self.opts.validation.required_field
						},
						"c_phone": {
							required: self.opts.validation.required_field
						},
						"c_email": {
							required: self.opts.validation.required_field,
							email: self.opts.validation.invalid_email
						},
						"c_company": {
							required: self.opts.validation.required_field
						},
						"c_notes": {
							required: self.opts.validation.required_field
						},
						"c_address": {
							required: self.opts.validation.required_field
						},
						"c_city": {
							required: self.opts.validation.required_field
						},
						"c_state": {
							required: self.opts.validation.required_field
						},
						"c_zip": {
							required: self.opts.validation.required_field
						},
						"c_country": {
							required: self.opts.validation.required_field
						},
						"cc_type": {
							required: self.opts.validation.required_field
						},
						"cc_num": {
							required: self.opts.validation.required_field
						},
						"cc_exp_month": {
							required: self.opts.validation.exp_month						
						},
						"cc_exp_year": {
							required: self.opts.validation.exp_year
						},
						"captcha": {
							required: self.opts.validation.required_field,
							remote: self.opts.validation.incorrect_captcha
						},
						"agreement": {
							required: self.opts.validation.required_field
						}
					},
					errorElement: 'li',
					errorPlacement: function (error, element) {
						if(element.attr('name') == 'agreement' || element.attr('name') == 'captcha')
						{
							element.parent().parent().parent().addClass('has-error');
							if(element.attr('name') == 'captcha')
							{
								error.appendTo(element.parent().next().find('ul'));
							}else{
								error.appendTo(element.parent().parent().next().find('ul'));
							}
						}else{
                            if (element.attr('name') == 'c_phone_country') {
                                //$('#c_phone_country_chzn').addClass('has-error');
                                $('#err_c_phone_country').html(error);
                            } else if (element.attr('name') == 'c_country') {
                                //$('#c_country_chzn').addClass('has-error');
                                $('#err_c_country').html(error);
                            } else {
							    element.parent().parent().addClass('has-error');
                                error.appendTo(element.next().find('ul'));
                            }
						}
					},
					highlight: function(ele, errorClass, validClass) {
		            	var element = pjQ.$(ele);
		            	if(element.attr('name') == 'agreement' || element.attr('name') == 'captcha')
						{
		            		element.parent().parent().parent().removeClass('has-success').addClass('has-error');
						}else{
							element.parent().parent().removeClass('has-success').addClass('has-error');
						}
		            },
		            unhighlight: function(ele, errorClass, validClass) {
		            	var element = pjQ.$(ele);
		            	if(element.attr('name') == 'agreement' || element.attr('name') == 'captcha')
						{
		            		element.parent().parent().parent().removeClass('has-error').addClass('has-success');
						}else if(element.attr('name') == 'cc_exp_month' || element.attr('name') == 'cc_exp_year')
						{
							var exp_month = pjQ.$('#bsExpMonth_' + self.opts.index).val(),
								exp_year = pjQ.$('#bsExpYear_' + self.opts.index).val();
							if(exp_month != '' && exp_year != '')
							{
								var today = new Date(),
									expiry = new Date(exp_year, exp_month);
								if (today.getTime() <= expiry.getTime())
								{
									element.parent().parent().removeClass('has-error').addClass('has-success');
								} else {
									element.parent().parent().removeClass('has-success').addClass('has-error');
								}
							}
		            		
						}else{
							element.parent().parent().removeClass('has-error').addClass('has-success');
						}
		            },
					onkeyup: false,
					submitHandler: function(form){
						self.disableButtons.call(self);
						pjQ.$.post([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionSaveForm", "&session_id=", self.opts.session_id].join(""), $frmCheckout.serialize()).done(function (data) {
							if(data.code == '200')
							{
                                hashBang("#!/Preview");
                                $('#is_booking_loaded').val(0);
							}else{
								var $msg_container = pjQ.$('#bsBookingMsg_' + self.opts.index);
								$msg_container.html(self.opts.validation.incorrect_captcha).parent().css('display', 'block');
							}
						});
						return false;
				    }
				});
				$frmCheckout.submit();
				
			}).on("click.bs", "#bsBtnConfirm_" + self.opts.index, function (e) {
                //self.disableButtons.call(self);
                self.hideButtons.call(self);
                
				var $msg_container = pjQ.$('#bsBookingMsg_' + self.opts.index);
				$msg_container.removeClass('text-danger');
				$msg_container.html(self.opts.message_0);
				$msg_container.parent().css('display', 'block');
				
				pjQ.$.get([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionCheck"].join(""), {
					"locale": self.opts.locale,
					"hide": self.opts.hide,
					"index": self.opts.index,
					"date": pjQ.$('#bsDate_' + self.opts.index).val(),
					"pickup_id": pjQ.$('#bsPickupId_' + self.opts.index).val(),
					"return_id": pjQ.$('#bsReturnId_' + self.opts.index).val(),
					"final_check": 1,
					"session_id": self.opts.session_id
				}).done(function (data) {
					if(data.code == '200')
					{
						pjQ.$.get([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionSaveBooking", "&session_id=", self.opts.session_id].join("")).done(function (resp) {
							if (!resp.code) {
								return;
							}
							switch (parseInt(resp.code, 10)) {
								case 100:
									$msg_container.addClass('text-danger');
									$msg_container.html(self.opts.message_4);
									self.enableButtons.call(self);
									break;
								case 110:
									$msg_container.addClass('text-danger');
									$msg_container.html(self.opts.validation.incorrect_captcha);
									self.enableButtons.call(self);
									break;
								case 200:
								case 201:
									self.getPaymentForm(resp);
									break;
							}
						});
					}else{
						$msg_container.html(pjQ.$('#bsFailMessage_' + self.opts.index).val());
					}
				});
				
			}).on("click.bs", ".bsLinkCancel", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var $this = pjQ.$(this);
				
				self.removeTable();
				
			}).on("click.bs", ".bsStartOver", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				if(!hashBang("#!/Search"))
				{
					self.loadSearch.call(self);
				}
			}).on("click.bs", ".bsDateNav", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				self.disableButtons.call(self);
				var date = pjQ.$(this).attr('data-date'),
					pickup_str = pjQ.$(this).attr('data-pickup'),
					return_str = pjQ.$(this).attr('data-return'),
					is_return_str = pjQ.$(this).attr('data-is_return'),
					return_date_str = pjQ.$(this).attr('data-return_date');
				pjQ.$.get([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionCheck"].join(""), {
					"locale": self.opts.locale,
					"hide": self.opts.hide,
					"index": self.opts.index,
					"date": date,
					"pickup_id": pickup_str,
					"return_id": return_str,
					"is_return": is_return_str,
					"return_date": return_date_str,
					"session_id": self.opts.session_id
				}).done(function (data) {
					self.loadSeats.call(self);
				});
			}).on("click.bs", ".pjBrSwitch", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var is_return = pjQ.$(this).attr('data-return');
				var class_6 = 'col-lg-6 col-md-6 col-sm-6 col-xs-6 col-xss-12';
				var class_12 = 'col-lg-12 col-md-12 col-sm-12 col-xs-12';
				var $fromParent = pjQ.$('.pjBsDatePickerFrom').parent().parent();
				var $toParent = pjQ.$('.pjBsDatePickerTo').parent().parent();
				if(is_return == 'F')
				{
					$fromParent.removeClass(class_6).addClass(class_12);
					$toParent.hide();
				}else{
					$fromParent.removeClass(class_12).addClass(class_6);
					$toParent.show();
				}
				pjQ.$('.pjBrSwitch').removeClass('active');
				pjQ.$(this).addClass('active');
				pjQ.$('#bsIsReturn_' + self.opts.index).val(is_return);
			}).on("click.bs", ".pjBrDestinationTip", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var bus_id = pjQ.$(this).attr('data-id');
				var clone_html = pjQ.$('#pjBrTipClone_' + bus_id).html();
				pjQ.$('#pjBsModalRoute').find('.modal-body').html(clone_html);
				pjQ.$('#pjBsModalRoute').modal('show');
			}).on("click.bs", ".pjBrBtnMenu", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				self.disableButtons.call(self);
				var hashString = pjQ.$(this).attr('data-load');
				hashBang("#!/" + hashString);
			}).on("click.bs", "#pjBrCaptchaImage", function (e) {
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				pjQ.$(this).attr("src", pjQ.$(this).attr("src").replace(/(&rand=)\d+/g, '\$1' + Math.ceil(Math.random() * 99999)));
				pjQ.$('#pjBrCaptchaInput').val("").removeData("previousValue");
				return false;
			});
					
			pjQ.$(window).on("loadSearch", this.$container, function (e) {
				self.loadSearch.call(self);
			}).on("loadSeats", this.$container, function (e) {
				self.loadSeats.call(self);
			}).on("loadCheckout", this.$container, function (e) {
				self.loadCheckout.call(self);
			}).on("loadPreview", this.$container, function (e) {
				self.loadPreview.call(self);
			}).on("loadDone", this.$container, function (e) {
				self.loadDone.call(self);
			});
			
			if (window.location.hash.length === 0) {
				this.loadSearch.call(this);
			} else {
				onHashChange.call(null);
			}
		},
		onReselect: function()
		{
			var self = this;
			pjQ.$( ".bs-selected" ).each(function( index ) {
				pjQ.$( this ).removeClass('bs-selected');
			});
			pjQ.$('#bsSelectedSeatsLabel_' + self.opts.index).html('');
			pjQ.$('#bs_selected_seats_' + self.opts.index).val('');
			pjQ.$('.bsReSelect').css('display', 'none');
		},
		onReturnReselect: function()
		{
			var self = this;
			pjQ.$( ".bs-return-selected" ).each(function( index ) {
				pjQ.$( this ).removeClass('bs-return-selected');
			});
			pjQ.$('#bsReturnSelectedSeatsLabel_' + self.opts.index).html('');
			pjQ.$('#bs_return_selected_seats_' + self.opts.index).val('');
			pjQ.$('.bsReturnReSelect').css('display', 'none');
		},
		loadSearch: function () {
			var self = this,
				index = this.opts.index,
				params = 	{
								"locale": this.opts.locale,
								"hide": this.opts.hide,
								"index": this.opts.index,
								"session_id": this.opts.session_id
							};
			pjQ.$.get([this.opts.folder, "index.php?controller=pjFrontPublic&action=pjActionSearch"].join(""), params).done(function (data) {
				self.$container.html(data);
				var $images = pjQ.$("#pjBrContainer_" + index + " img"),
					preloaded = 0,
				    total = $images.length;
				$images.load(function() {
				    if (++preloaded === total) {
				    	var	content_height = pjQ.$('.pjBsFormArticle').height();
						if(content_height <= 200){
							pjQ.$('.pjBsFormAvailability').height(200);
						}
				    }
				});
				
				if (pjQ.$('.pjBsAutocomplete').length) {
					pjQ.$('.pjBsAutocomplete').select2({
						dir: self.fnRtlOrNot.call(self),
						containerCssClass: 'pjBsSelect2Preview',
						dropdownCssClass: 'pjBsSelect2Dropdown'
					});
				};
				if (pjQ.$('.pjBsDatePicker').length) 
				{
					moment.locale('en', {
						week: { dow: self.opts.week_start }
					});
					moment.updateLocale('en', {
						months : pjQ.$('#pjBrCalendarLocale').data('months').split("_"),
				        weekdaysMin : pjQ.$('#pjBrCalendarLocale').data('days').split("_")
					});
					
					if(pjQ.$('.pjBsDatePickerFrom').length > 0)
					{
						var currentDate = new Date();
						pjQ.$('.pjBsDatePickerFrom').datetimepicker({
							format: self.opts.momentDateFormat.toUpperCase(),
							locale: moment.locale('en'),
							allowInputToggle: true,
							minDate: new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate()),
							ignoreReadonly: true,
							tooltips: self.opts.tooltips
						});
						pjQ.$('.pjBsDatePickerFrom').on('dp.change', function (e) {
							if(pjQ.$('#bsDate_' + self.opts.index).val() != '')
							{
								var toDate = new Date(e.date);
								toDate.setDate(toDate.getDate());
								var momentDate = new moment(toDate);
								pjQ.$('.pjBsDatePickerTo').datetimepicker().children('input').val(momentDate.format(self.opts.momentDateFormat.toUpperCase()));
								pjQ.$('.pjBsDatePickerTo').data("DateTimePicker").minDate(e.date);
							}
						});
					}
					if(pjQ.$('.pjBsDatePickerTo').length > 0)
					{
						var year = parseInt(pjQ.$('.pjBsDatePickerTo').eq(0).attr('data-year'),10),
							month = parseInt(pjQ.$('.pjBsDatePickerTo').eq(0).attr('data-month'),10),
							day = parseInt(pjQ.$('.pjBsDatePickerTo').eq(0).attr('data-day'),10);
						var fromDate = new Date(year, month - 1, day);
						pjQ.$('.pjBsDatePickerTo').datetimepicker({
							format: self.opts.momentDateFormat.toUpperCase(),
							locale: moment.locale('en'),
							allowInputToggle: true,
							ignoreReadonly: true,
							tooltips: self.opts.tooltips,
							useCurrent: false,
							minDate: new Date(fromDate.getFullYear(), fromDate.getMonth(), fromDate.getDate())
						});
					}
				}
				if (validate) {
					self.$container.find("form").validate({
						errorElement: 'li',
						errorPlacement: function (error, element) {
							if(element.attr('name') == 'date' || element.attr('name') == 'return_date')
							{
								error.appendTo(element.parent().next().find('ul'));
							}else{
								error.appendTo(element.next().next().find('ul'));
							}
						},
						highlight: function(ele, errorClass, validClass) {
			            	var element = pjQ.$(ele);
			            	element.parent().parent().removeClass('has-success').addClass('has-error');

			            },
			            unhighlight: function(ele, errorClass, validClass) {
			            	var element = pjQ.$(ele);
			            	element.parent().parent().removeClass('has-error').addClass('has-success');
			            },
						submitHandler: function (form) {
							self.disableButtons.call(self);
							var $form = pjQ.$(form);
							pjQ.$.get([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionCheck"].join(""), {
								"locale": self.opts.locale,
								"hide": self.opts.hide,
								"index": self.opts.index,
								"date": pjQ.$('#bsDate_' + self.opts.index).val(),
								"pickup_id": pjQ.$('#bsPickupId_' + self.opts.index).val(),
								"return_id": pjQ.$('#bsReturnId_' + self.opts.index).val(),
								"is_return": pjQ.$('#bsIsReturn_' + self.opts.index).val(),
								"return_date": pjQ.$('#bsReturnDate_' + self.opts.index).val(),
								"session_id": self.opts.session_id
							}).done(function (data) {
								if(data.code == '200')
								{
									pjQ.$('#bs_selected_bus_' + self.opts.index).val('');
									hashBang("#!/Seats");
								}else if(data.code == '101') {
									pjQ.$('.bsCheckReturnErrorMsg').css('display', 'block');
									self.enableButtons.call(self);
									
								}else{
									pjQ.$('.bsCheckErrorMsg').css('display', 'block');
									self.enableButtons.call(self);
								}
							});
							return false;
						}
					});
				}
			}).fail(function () {
				self.enableButtons.call(self);
			});
		},
		loadSeats: function () {
			var self = this,
				index = this.opts.index,
				params = 	{
								"locale": this.opts.locale,
								"hide": this.opts.hide,
								"index": this.opts.index,
								"session_id": this.opts.session_id
							};
			pjQ.$.get([this.opts.folder, "index.php?controller=pjFrontPublic&action=pjActionSeats"].join(""), params).done(function (data) {
				self.$container.html(data);
				pjQ.$('html, body').animate({
			        scrollTop: self.$container.offset().top
			    }, 500);
				pjQ.$('.modal-dialog').css("z-index", "9999"); 
				
				var $form = pjQ.$('#bsSelectSeatsForm_' + self.opts.index);
				var bus_id = pjQ.$('#bs_selected_bus_' + self.opts.index).val(),
					return_bus_id = pjQ.$('#bs_return_selected_bus_' + self.opts.index).val();
				pjQ.$.post([self.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionGetRoundtripPrice&bus_id=", bus_id, "&return_bus_id=", return_bus_id, "&session_id=", self.opts.session_id].join(""), $form.serialize()).done(function (data) {
					pjQ.$('#bsRoundtripPrice_' + self.opts.index).html(data);
				});
			}).fail(function () {
				self.enableButtons.call(self);
			});
		},
		loadMap: function(bus_id){
			var self = this,
				index = this.opts.index,
				params = {
							"locale": this.opts.locale,
							"hide": this.opts.hide,
							"index": this.opts.index,
							"bus_id": bus_id,
							"session_id": this.opts.session_id
						};
			var $mapContaner = pjQ.$('#bsMapContainer_' + self.opts.index),
			$selected_seats = pjQ.$('#bsSelectedSeatsLabel_' + self.opts.index);
			$selected_seats.html('');
			$selected_seats.parent().css('display', 'inline-block');
			pjQ.$('#bs_selected_seats_' + self.opts.index).val('');
			
			self.disableButtons.call(self);
			pjQ.$.get([this.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionGetSeats"].join(""), params).done(function (data) {
				$mapContaner.html(data);
				
				$mapContaner.css('display', 'block');
				pjQ.$('.pjBsPickupSeatsBody').show();
				pjQ.$('.pjBsPickupSeatsFoot').show();

				pjQ.$('#bs_selected_tickets_' + self.opts.index).attr('data-map', 'T');
				pjQ.$('.bs-seats-legend').css('display', 'block');
				
				self.enableButtons.call(self);
			}).fail(function () {
				self.enableButtons.call(self);
			});
		},
		hideMap: function(){
			var self = this,
				$mapContaner = pjQ.$('#bsMapContainer_' + self.opts.index),
				$selected_seats = pjQ.$('#bsSelectedSeatsLabel_' + self.opts.index);
			$mapContaner.html('');
			$mapContaner.css('display', 'none');
			
			pjQ.$('.bsReSelect').css('display', 'none');
			pjQ.$('#bs_selected_tickets_' + self.opts.index).attr('data-map', 'F');
			pjQ.$('.bs-seats-legend').css('display', 'none');
		},
		loadReturnMap: function(bus_id){
			var self = this,
				index = this.opts.index,
				params = {
							"locale": this.opts.locale,
							"hide": this.opts.hide,
							"index": this.opts.index,
							"bus_id": bus_id,
							"session_id": this.opts.session_id
						};
			var $mapContaner = pjQ.$('#bsReturnMapContainer_' + self.opts.index),
			$selected_seats = pjQ.$('#bsReturnSelectedSeatsLabel_' + self.opts.index);
			$selected_seats.html('');
			$selected_seats.parent().css('display', 'inline-block');
			pjQ.$('#bs_return_selected_seats_' + self.opts.index).val('');
			
			self.disableButtons.call(self);
			pjQ.$.get([this.opts.folder, "index.php?controller=pjFrontEnd&action=pjActionGetReturnSeats"].join(""), params).done(function (data) {
				$mapContaner.html(data);
				$mapContaner.css('display', 'block');

				pjQ.$('#bs_return_selected_tickets_' + self.opts.index).attr('data-map', 'T');
				pjQ.$('.bs-seats-legend').css('display', 'block');
				
				self.enableButtons.call(self);
			}).fail(function () {
				self.enableButtons.call(self);
			});
		},
		hideReturnMap: function(){
			var self = this,
				$mapContaner = pjQ.$('#bsReturnMapContainer_' + self.opts.index),
				$selected_seats = pjQ.$('#bsReturnSelectedSeatsLabel_' + self.opts.index);
			$mapContaner.html('');
			$mapContaner.css('display', 'none');
			
			pjQ.$('.bsReSelect').css('display', 'none');
			pjQ.$('#bs_return_selected_tickets_' + self.opts.index).attr('data-map', 'F');
			pjQ.$('.bs-seats-legend').css('display', 'none');
		},
		getSeatsArray: function()
		{
			var self = this,
				selected_seats = pjQ.$('#bs_selected_seats_' + self.opts.index).val(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split("|");
			}
			return seat_arr;
		},
		getReturnSeatsArray: function()
		{
			var self = this,
				selected_seats = pjQ.$('#bs_return_selected_seats_' + self.opts.index).val(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split("|");
			}
			return seat_arr;
		},
		getSeatsNameArray: function()
		{
			var self = this,
				selected_seats = pjQ.$('#bsSelectedSeatsLabel_' + self.opts.index).html(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split(", ");
			}
			return seat_arr;
		},
		getReturnSeatsNameArray: function()
		{
			var self = this,
				selected_seats = pjQ.$('#bsReturnSelectedSeatsLabel_' + self.opts.index).html(),
				seat_arr = Array();
			if(selected_seats != '')
			{
				seat_arr = selected_seats.split(", ");
			}
			return seat_arr;
		},
		loadCheckout: function () {
			var self = this,
				index = this.opts.index;
			var qs = {
					"cid": this.opts.cid,
					"locale": this.opts.locale,
					"hide": this.opts.hide,
					"index": this.opts.index,
					"session_id": this.opts.session_id
				};
			pjQ.$.get([this.opts.folder, "index.php?controller=pjFrontPublic&action=pjActionCheckout"].join(""), qs).done(function (data) {
				self.$container.html(data);
				pjQ.$('html, body').animate({
			        scrollTop: self.$container.offset().top
			    }, 500);
				pjQ.$('.modal-dialog').css("z-index", "9999"); 
			});
		},
		loadPreview: function () {
			var self = this,
				index = this.opts.index;
			var qs = {
					"cid": this.opts.cid,
					"locale": this.opts.locale,
					"hide": this.opts.hide,
					"index": this.opts.index,
					"session_id": this.opts.session_id
				};
			pjQ.$.get([this.opts.folder, "index.php?controller=pjFrontPublic&action=pjActionPreview"].join(""), qs).done(function (data) {
				self.$container.html(data);
				pjQ.$('html, body').animate({
			        scrollTop: self.$container.offset().top
			    }, 500);
			});
		},
		getPaymentForm: function(obj){
			var self = this,
				index = this.opts.index;
			var qs = {
					"cid": this.opts.cid,
					"locale": this.opts.locale,
					"hide": this.opts.hide,
					"index": this.opts.index,
					"booking_id": obj.booking_id, 
					"payment_method": obj.payment,
					"session_id": this.opts.session_id
				};
			pjQ.$.get([this.opts.folder, "index.php?controller=pjFrontPublic&action=pjActionGetPaymentForm"].join(""), qs).done(function (data) {
				var $msg_container = pjQ.$('#bsBookingMsg_' + index);
				$msg_container.html(data);
				$msg_container.parent().css('display', 'block');
				self.disableMenu();
                self.disableChangeLink();

				switch (obj.payment) {
					case 'paypal':
						 self.$container.find("form[name='bsPaypal']").trigger('submit');
						break;
					case 'authorize':
						self.$container.find("form[name='bsAuthorize']").trigger('submit');
                        break;
                    case '2checkout':
                        self.$container.find("form[name='bs2Checkout']").trigger('submit');
                        break;
                    case 'flutterwave':
                        self.$container.find("form[name='bsFlutterwave']").trigger('submit');
                        break;
					case 'creditcard':
					case 'bank':
					case 'cash':
						break;
				}
			}).fail(function () {
				log("Deferred is rejected");
			});
		},
		reCalculatingTickets: function($this, max_seats, bus_id)
		{
			var current_value = parseInt($this.val(), 10),
				number_of_seats = parseInt(pjQ.$('#bs_number_of_seats_' + bus_id).val(), 10);

			pjQ.$('.bsTicketSelect-' + bus_id ).each(function( index ) {
				if($this.attr('name') != pjQ.$( this ).attr('name'))
				{
					var selected_value = parseInt(pjQ.$( this ).val(), 10),
						new_options = {},
						$that = pjQ.$( this );
					$that.empty();
					if(selected_value > 0)
					{
						max_seats = (number_of_seats - current_value);
					}
					for(var i = 0; i <= max_seats; i++)
					{
						new_options[i] = i;
					}
					pjQ.$.each(new_options, function(key, value) {
						$that.append(pjQ.$("<option></option>").attr("value", value).text(key));
					});
					$that.val(selected_value);
				}
			});
		},
		reCalculatingReturnTickets: function($this, max_seats, bus_id)
		{
			var current_value = parseInt($this.val(), 10),
				number_of_seats = parseInt(pjQ.$('#bs_return_number_of_seats_' + bus_id).val(), 10);

			pjQ.$('.bsReturnTicketSelect-' + bus_id ).each(function( index ) {
				if($this.attr('name') != pjQ.$( this ).attr('name'))
				{
					var selected_value = parseInt(pjQ.$( this ).val(), 10),
						new_options = {},
						$that = pjQ.$( this );
					$that.empty();
					if(selected_value > 0)
					{
						max_seats = (number_of_seats - current_value);
					}
					for(var i = 0; i <= max_seats; i++)
					{
						new_options[i] = i;
					}
					pjQ.$.each(new_options, function(key, value) {
						$that.append(pjQ.$("<option></option>").attr("value", value).text(key));
					});
					$that.val(selected_value);
				}
			});
		},
		disableMenu: function()
		{
			pjQ.$('.bsStepLink').each(function( index ) {
				pjQ.$(this).removeClass('bsStepClickable');
				pjQ.$(this).css( 'cursor', 'default' );
			});
			pjQ.$('.bsStep').each(function( index ) {
				pjQ.$(this).removeClass('bsStepPassed');
				pjQ.$(this).css( 'cursor', 'default' );
			});
		},
		disableChangeLink: function()
		{
			pjQ.$('.bsChangeSeat').css( 'cursor', 'default' );
			pjQ.$('.bsChangeSeat').css( 'text-decoration', 'none' );
			pjQ.$('.bsChangeSeat').removeClass('bsChangeSeat');
			
			pjQ.$('.bsChangeDate').css( 'cursor', 'default' );
			pjQ.$('.bsChangeDate').css( 'text-decoration', 'none' );
			pjQ.$('.bsChangeDate').removeClass('bsChangeDate');
		},
		fnRtlOrNot : function ()
		{
			if (pjQ.$('html').attr('dir') === 'rtl') {
				return 'rtl';
			} else {
				return 'ltr';
			};
		}
	};
	
	window.BusReservation = BusReservation;	
})(window);