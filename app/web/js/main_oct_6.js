/**
 * Fixed Header
 */
function fixedHeader() {
    var logoactive = base_url+'app/web/img/mazhandu-logo-active.png';
    var logowhite = base_url+'app/web/img/mazhandu-logo.png';
    var setHeader = function() {
        if ($(window).scrollTop() > 10) {
            $('#top-nav').addClass('fixed-header');
            $('#logo').attr('src', logoactive);
        } else {
            $('#top-nav').removeClass('fixed-header')
            $('#logo').attr('src', logowhite);
        }

            
        if  ($(window).width() <= 762) {
            $('#logo').attr('src', logoactive);
        }
    }
    try {
        setHeader();
        $(window).scroll(setHeader).resize(setHeader);
    } catch (error) {
        console.log(error);
    }
}

/**
 * Menu Hightlight
 */
function menuHightlight() {
    try {
        $('a[href*=#]:not([href=#])').click(function() {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html,body').animate({
                        scrollTop: target.offset().top-60
                    }, 1000);
                    return false;
                }
            }
        });

        /**
         * This part handles the highlighting functionality.
         * We use the scroll functionality again, some array creation and 
         * manipulation, class adding and class removing, and conditional testing
         */
        var aChildren = $("#menu-main-top-navigation a"); // find the a children of the list items
        var aArray = []; // create the empty aArray
        for (var i = 0; i < aChildren.length; i++) {
            var aChild = aChildren[i];
            var id = $(aChildren[i]).attr('id');
            if (id == 'play_store') {
                continue;
            }

            var ahref = $(aChild).attr('href');
            
            if (2 > ahref.length) {
                continue;
            }
            aArray.push(ahref);
        } // this for loop fills the aArray with attribute href values

        $(window).scroll(function() {
            var windowPos = $(window).scrollTop()+90; // get the offset of the window from the top of page
            var windowHeight = $(window).height(); // get the height of the window
            var docHeight = $(document).height();

            for (var i = 0; i < aArray.length; i++) {
                var theID = aArray[i];
                var div = $(theID).offset();
                if (typeof div === 'undefined') {
                    continue;
                }
                var divPos = $(theID).offset().top; // get the offset of the div from the top of page
                var divHeight = $(theID).height(); // get the height of the div in question
                if (windowPos >= divPos && windowPos < (divPos + divHeight)) {
                    $("a[href='" + theID + "']").parent().addClass("activenav");
                } else {
                    $("a[href='" + theID + "']").parent().removeClass("activenav");
                }
            }

            if (windowPos + windowHeight == docHeight) {
                console.log(windowPos + windowHeight);
                if (!$("nav li:last-child").hasClass("activenav")) {
                    var navActiveCurrent = $(".activenav").attr("href");
                    $("a[href='" + navActiveCurrent + "']").removeClass("activenav");
                    $("nav li:last-child").addClass("activenav");
                }
            }
        });
    } catch (error) {
        console.log(error);
    }
}

function carouselSwipe() {
    try {
        $("#myCarousel").swiperight(function () {
            $("#myCarousel").carousel('prev');
        });
        $("#myCarousel").swipeleft(function () {
            $("#myCarousel").carousel('next');
        });

    } catch (error) {
        console.log(error);
    }
}

function search_agents() {
    var search_district = $.trim($("#search_district").val());
    var post_data= {
        'search_district': search_district,
        'is_home': is_home
    };

    $('#agents').fadeTo('slow', 0.2, function() { 
        $.post("index.php?controller=pjFrontPublic&action=pjActionGetAgents", post_data).done(function (data) {
            $('#list_agents').html(data); 
            $('#a_agent').attr("href",base_url+'index.php?controller=pjAgent&action=pjActionList&district='+search_district);
            $('#agents').fadeTo('slow', 1);
        });
    });
}

function get_ticket() {
    var mt_phone_country = $.trim($("#mt_phone_country").val());
    var mt_phone = $.trim($("#mt_phone").val());

    if (!mt_phone_country || !mt_phone) {
        alert('Please enter both Country and Phone Number');
    } else {
        var post_data= {
            'mt_phone_country': mt_phone_country,
            'mt_phone': mt_phone
        };

        $('#my-ticket').fadeTo('slow', 0.2, function() { 
            $.post(
            base_url+'index.php?controller=pjFrontEnd&action=pjSendTicket',
            post_data,
            function(data){
                alert(data['message']);
                $('#my-ticket').fadeTo('slow', 1);
            },
            'json'
            );
        });
    }
}

jQuery(document).ready(function($) {
    if (is_home == 1) {
        fixedHeader();
        menuHightlight();
        carouselSwipe();

        set_ticket_phone_country();
    }
});

function set_ticket_phone_country() {
    if (is_home == 1) {
        var country_detail = $('#mt_phone_country').val();    
        var country_detail_array = country_detail.split('_');

        $('#mt_phone').val(country_detail_array[1]);
    }
}

function load_booking() {
    var is_booking_loaded = $('#is_booking_loaded').val();
    if (is_booking_loaded == 0) {
        $('#load_booking_container').html('<script type="text/javascript" src="'+base_url+'index.php?controller=pjFrontEnd&action=pjActionLoad"></script>');
    }
}
