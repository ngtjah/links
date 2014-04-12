
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
