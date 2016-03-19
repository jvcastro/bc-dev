<div id="liveusers"  align="left" style="width:400px; position:relative; float:left"></div><div id="activecampaigns" align="right" style="width:400px; position:relative;left: 20px; float:left"></div>

<script>
http.open("GET", url + '?act=info', true);
http.onreadystatechange = function () {
	if (http.readyState == 4)
		{
		var resp = http.responseText;
		document.getElementById('displayport').innerHTML=resp;
		}
	};
http.send(null);
</script>