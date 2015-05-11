// SSL Alert
function showAlert() {

    $("#sslAlert").addClass("in");

    //var $alertDiv = $("#sslAlert");
    //if ($alertDiv.hasClass("in")) {
    //    $alertDiv.removeClass("in").css("display", "");
    //}
    //else {
    //    $("#sslAlert").addClass("in");
    //}

}

window.setTimeout(function () {

    if ( document.location.protocol != 'https:' ) {

        showAlert();

    }

}, 10000);

//$(".alert").alert()

// Fade the Menu Dropdown
$(function() {

        $('.dropdown-toggle').click(function() {

            $(this).next('.dropdown-menu').fadeToggle(500);

        });

});


//$(document).on('webkitTransitionEnd transitionend oTransitionEnd', ".fade", 
//    function (evnt) {
//        var $faded = $(evnt.target);
//        if ($faded.hasClass("in")) {
//            $faded.css("display", "");
//        }
//});



$('#blueimp-gallery')
    .on('open', function (event) {
        // Gallery open event handler


    })
    .on('opened', function (event) {
        // Gallery opened event handler

	//Lower the volume by default on html5 videos
        $("video").each(function(){ this.volume = 0.5; });

    })
    .on('slide', function (event, index, slide) {
        // Gallery slide event handler
    })
    .on('slideend', function (event, index, slide) {
        // Gallery slideend event handler
    })
    .on('slidecomplete', function (event, index, slide) {
        // Gallery slidecomplete event handler
    })
    .on('close', function (event) {
        // Gallery close event handler
    })
    .on('closed', function (event) {
        // Gallery closed event handler
    });

