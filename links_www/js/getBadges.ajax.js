var myOnload = loadBadges();

var myInterval = setInterval(function(){loadBadges()},1000*60);

    function loadBadges()
    {
      // if (str=="")
      // {
      // document.getElementById("txtBadge").innerHTML="";
      //  return;
      // } 
      if (window.XMLHttpRequest)
	{  // code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}
      else
	{  // code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
      xmlhttp.onreadystatechange=function()
      {
	if (xmlhttp.readyState==4 && xmlhttp.status==200)
	  {

	    var response = JSON.parse(xmlhttp.responseText);
	    var ajaxDisplayLinks;
            var ajaxDisplayThumbs;
            var ajaxDisplayVids;
            var ajaxDisplayThumbsVids;


            if (ajaxDisplayLinks = document.getElementById('linksBadge')) {	      
	      if (response[0]['links'] > 0) {
	        ajaxDisplayLinks.innerHTML = response[0]['links']; 
	      } else {
		ajaxDisplayLinks.innerHTML = "";
              }
            }

            if (ajaxDisplayThumbs = document.getElementById('thumbsBadge')) {            
	      if (response[0]['thumbs'] > 0) {
	        ajaxDisplayThumbs.innerHTML = response[0]['thumbs']; 
	      } else {
                ajaxDisplayThumbs.innerHTML = "";
              }
            }

            if (ajaxDisplayVids = document.getElementById('vidsBadge')) {
              if (response[0]['vids'] > 0) {
	        ajaxDisplayVids.innerHTML = response[0]['vids']; 
	      } else {
		ajaxDisplayVids.innerHTML = "";
              }
            }

            if (ajaxDisplayThumbsVids = document.getElementById('thumbsvidsBadge')) {
	      if (response[0]['thumbs'] > 0 || response[0]['vids'] > 0) {
	        ajaxDisplayThumbsVids.innerHTML = Number(response[0]['thumbs']) + Number(response[0]['vids']);
	      } else {
		ajaxDisplayThumbsVids.innerHTML = "";
              }
            }
	  }
      }
      xmlhttp.open("GET","newposts.php",true);
      xmlhttp.send();
    }
