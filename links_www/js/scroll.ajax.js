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
        if (element_in_scroll("#contenttable tr:last")) {
                $(window).unbind('scroll');
                $.ajax({
                    type: "POST",
                    url: "older.php",
		    dataType: 'json',
		    cache: false,
                    data: { partialurl:$('input[name=partialurl]').attr('value'), older:$('#older').data('val'),json: "true" },
                    success: function( msg ){
			//$('#older').data('val',msg.olderid);
			$(window).scroll(scrollFunction);
                    }
                }).done(function( msg ) {
                    //$("#contenttable tbody").append(msg.results).hide().fadeIn(999);
		    $(msg.results).hide().appendTo("#contenttable tbody").fadeIn(1000);
		    $('#older').data('val',msg.olderid);

                    if (msg.results.count != 0) {
                        $(window).scroll(function(e){
			    //Query the jQuery object for the values
			    //var msgindex = $msg.filter('#olderResult').text();
                            //scroll_element_ajax();
    			    //alert($('#older').data('val'));
                        })
                    }
                });
            };
    };
$(window).scroll(scrollFunction);
});


