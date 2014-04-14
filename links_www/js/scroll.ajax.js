// Infinite Scroll

function element_in_scroll(elem)
{
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();
 
    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();
 
    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}


$(function(){
    var scrollFunction = function(e){
	var mycount = 1;
        if (element_in_scroll("#contenttable tr:last")) {
	        $('#loading').show();
                $(window).unbind('scroll');
                $.ajax({
                    type: "POST",
                    url: "older.php",
		    dataType: 'json',
		    cache: false,
                    data: { partialurl:$('input[name=partialurl]').attr('value'), older:$('#older').data('val'),json: "true" },
                    success: function( msg ){
			//$('#older').data('val',msg.olderid);

                    }
                }).done(function( msg ) {
                    //$("#contenttable tbody").append(msg.results).hide().fadeIn(999);

                    if (msg.results.length > 0) {

		        $('#loading').hide();
		        $(msg.results).hide().appendTo("#contenttable tbody").fadeIn(1000);
		        $('#older').data('val',msg.olderid);
			$(window).scroll(scrollFunction);


                    } else {

		        $('#loading').hide();
		        $('#nomoreresults').show();

		    }

		    mycount = mycount + 1;

                });
            };
    };

$(window).scroll(scrollFunction);

});


