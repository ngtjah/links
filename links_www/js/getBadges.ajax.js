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

	    if (response[0]['links'] > 0)
	      {
	    var ajaxDisplay = document.getElementById('linksBadge');
	    ajaxDisplay.innerHTML = response[0]['links']; 
	      }
	    if (response[0]['thumbs'] > 0)
	      {
	    var ajaxDisplay = document.getElementById('thumbsBadge');
	    ajaxDisplay.innerHTML = response[0]['thumbs']; 
	      }
	    if (response[0]['vids'] > 0)
	      {
	    var ajaxDisplay = document.getElementById('vidsBadge');
	    ajaxDisplay.innerHTML = response[0]['vids']; 
	      }
	  }
      }
      xmlhttp.open("GET","newposts.php",true);
      xmlhttp.send();
    }
