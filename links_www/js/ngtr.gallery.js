
//Add the description to the lightbox image
$('#blueimp-gallery').on('slide', function (index, slide) {

   var description = $(this).data('gallery').list[slide].getAttribute('data-description');
       $(this).find('.description').prop('innerHTML', description);

});

$('#blueimp-gallery').on('close', function () {

    var cols = document.getElementsByClassName('thumb');
    for(i=0; i<cols.length; i++) {
      cols[i].style.visibility = 'visible';
    }

});

$('#blueimp-gallery').on('open', function () {


    if(readCookie("hideImgOnLightboxOpen") == "on") {
    	var cols = document.getElementsByClassName('thumb');
    	for(i=0; i<cols.length; i++) {
    	  cols[i].style.visibility = 'hidden';
    	}
    }


});

//console.log(readCookie("hideImgOnLightboxOpen"));


function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}


function lightboxbigger() {

        var cols = document.getElementsByClassName('modal-body');
        for(i=0; i<cols.length; i++) {
console.log(cols[i].style.padding);
	    if (cols[i].style.padding == '0px 0px 56.25%') {
		    cols[i].style.padding = '0 0 90.25% 0';
            } else if (cols[i].style.padding == '0px 0px 90.25%') {
		    cols[i].style.padding = '0 0 56.25% 0';
	    } else {
		    cols[i].style.padding = '0 0 90.25% 0';
	    }
        }
}


//blueimp.Gallery(
//    document.getElementById('links'),
//    {
//        onslide: function (index, slide) {
//            var text = this.list[index].getAttribute('data-description'),
//                node = this.container.find('.description');
//            node.empty();
//            if (text) {
//                node[0].appendChild(document.createTextNode(text));
//            }
//        }
//    }
//);

//document.getElementById('links').onclick = function (event) {
//    event = event || window.event;
//    var target = event.target || event.srcElement,
//        link = target.src ? target.parentNode : target,
//        options = {index: link, event: event, onslide: function (index, slide) {
//            var text = this.list[index].getAttribute('data-description'),
//                node = this.container.find('.description');
//            node.empty();
//            if (text) {
//                node[0].appendChild(document.createTextNode(text));
//            }
//        } },
//        links = this.getElementsByTagName('a');
//    blueimp.Gallery(links, options);
//};


//document.getElementById('links').onclick = function (event) {
//    
//    //links = this.getElementsByTagName('a');
//
//    var cols = document.getElementsByClassName('thumb');
//    for(i=0; i<cols.length; i++) {
//      cols[i].style.visibility = 'hidden';
//    }
//    
//
//};



