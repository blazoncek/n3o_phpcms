/* custom.js - custom JS functions (requires jQuery)
.---------------------------------------------------------------------------.
|  Software: N3O CMS                                                        |
|   Version: 2.2.0                                                          |
|   Contact: contact author (also www.kristan-sp.si/blazk)                  |
| ------------------------------------------------------------------------- |
|    Author: Blaž Kristan (blaz@kristan-sp.si)                              |
| Copyright (c) 2000-2013, Blaž Kristan. All Rights Reserved.               |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/

///////////////////////////////////////
// initialize Google Maps object, attach it to DIV and overlay GPX track
//   - divid  (string) : input DIV ID
//   - gpxurl (string) : input URL to GPX track
function initialize_map(divid,gpxurl) {
	var map;
	var myLatLng = new google.maps.LatLng(0,0);
	var mapOptions = {
		zoom: 1,
		center: myLatLng,
		mapTypeId: google.maps.MapTypeId.TERRAIN
	};
	map = new google.maps.Map(document.getElementById(divid),mapOptions);

	// ensure jQuery is loaded before using
	$.ajax({
		type: "GET",
		url: gpxurl,
		dataType: "xml",
		success: function(xml) {
			var points = [];
			var bounds = new google.maps.LatLngBounds ();
			$(xml).find("trkpt").each(function() {
				var lat = $(this).attr("lat");
				var lon = $(this).attr("lon");
				var p = new google.maps.LatLng(lat, lon);
				points.push(p);
				bounds.extend(p);
			});
			var poly = new google.maps.Polyline({
				path: points,
				strokeColor: "#FF00AA",
				strokeOpacity: .7,
				strokeWeight: 4
			});
			poly.setMap(map);
			map.fitBounds(bounds);
		}
	});
}
