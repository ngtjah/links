
// SSL Alert
function showAlert() {

    $("#sslAlert").addClass("in");

}

window.setTimeout(function () {

    if ( document.location.protocol != 'https:' ) {

        showAlert();

    }

}, 10000);


// Fade the Menu Dropdown
$(function() {

        $('.dropdown-toggle').click(function() {

            $(this).next('.dropdown-menu').fadeToggle(500);

        });

});

