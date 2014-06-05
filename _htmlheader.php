<?php
/*~ _htmlheader.php - page HTML HEAD content
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend)                                             |
|   Version: 2.2.0                                                          |
|   Contact: contact author (also http://blaz.at/home)                      |
| ------------------------------------------------------------------------- |
|    Author: Blaž Kristan (blaz@kristan-sp.si)                              |
| Copyright (c) 2007-2014, Blaž Kristan. All Rights Reserved.               |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| ------------------------------------------------------------------------- |
| This file is part of N3O CMS (frontend).                                  |
|                                                                           |
| N3O CMS is free software: you can redistribute it and/or                  |
| modify it under the terms of the GNU Lesser General Public License as     |
| published by the Free Software Foundation, either version 3 of the        |
| License, or (at your option) any later version.                           |
|                                                                           |
| N3O CMS is distributed in the hope that it will be useful,                |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU Lesser General Public License for more details.                       |
'---------------------------------------------------------------------------'
*/

/**
 * Content template (headers included in HEAD)
 */
?>
<title><?php echo $TitleText; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo langCharSet($lang); ?>">
<meta http-equiv="content-language" content="<?php echo langCode($lang); ?>"> 
<meta name="revisit-after" content="10 days">
<meta name="generator" content="N3O CMS v<?php echo AppVer ?>">
<meta name="Author" content="<?php echo $PostMaster ?>">
<meta name="description" content="">
<meta name="keywords" content="">
<?php if ( $Mobile ) : ?>
<meta name="viewport" content="initial-scale=1, maximum-scale=1.0, minimum-scale=1, user-scalable=no, width=device-width">
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="format-detection" content="telephone=no" />
<meta name="format-detection" content="address=no" />
<?php else : ?>
<?php
// Twitter cards/Open Graph integration
// permalinks
$kat = ($TextPermalinks) ? ($IsIIS ? "$WebFile/" : ''). $KatText .'/' : '?kat='. $_GET['kat'];
if ( isset($_GET['ID']) && $_GET['ID'] > 0 ) {
	$Besedilo = $Teksti[0];
	// permalinks
	$bid = ($TextPermalinks) ? $Besedilo->Ime .'/' : '&amp;ID='. $Besedilo->ID;
	if ( $Besedilo->Slika != "" ) {
		$image = $WebURL .'/media/besedila/'. $Besedilo->Slika;
	} else if ( count($Galerija) > 0 ) {
		$image = $WebURL .'/media/'. $Galerija[0]->Datoteka;
	} else if ( preg_match("/<img[^>]*>/i", $Besedilo->Opis, $src) ) {
		// find 1st embeded image
			if ( preg_match("/src=\"(?!http)([^\"]*)\"/i", $src[0], $pic) ) { // find SRC= content
				$sPath = dirname("$StoreRoot/". $pic[1]); // filesystem path
				$rPath = dirname("$WebPath/". $pic[1]); // web relative path
				$sName = basename("$WebPath/". $pic[1]); // filename
			}
			$image = $WebURL .'/'. $pic[1];
	} else {
		$image = '';
	}
	$type        = 'article';
	$url         = $WebURL .'/'. $kat . $bid;
	$title       = $Besedilo->Naslov;
	$description = str_replace("\"", "&quot;", left(preg_replace("/<([^>]*)>/i", "", $Besedilo->Povzetek),200));
	$creator     = $Besedilo->TwitterName;
	unset($Besedilo);
} else {
	echo '<meta name="twitter:card" content="summary" />';
	$type        = 'website';
	$image       = '';
	$url         = $WebURL .'/'. $kat;
	$title       = $Kat->Naziv;
	$description = str_replace("\"", "&quot;", left(preg_replace("/<([^>]*)>/i", "", $Kat->Povzetek),200));
}
//echo '<meta name="twitter:card" content="'. ($image!='' ? 'photo' : 'summary') .'" />';
echo '<meta name="twitter:card" content="summary" />';
if ( $image!='' ) echo '<meta name="twitter:image:src" content="'. $image .'" />';
echo '<meta name="twitter:url" content="'. $url .'" />';
echo '<meta name="twitter:title" content="'. $title .'" />';
echo '<meta name="twitter:description" content="'. $description .'" />';
if ( isset($creator) && $creator!='' ) echo '<meta name="twitter:creator" content="'. $creator .'" />';
if ( isset($TwitterName) ) echo '<meta name="twitter:site" content="'. $TwitterName .'" />';
echo "\n";
// open graph
echo '<meta property="og:site_name" content="'. multiLang('<Title>', $lang) .'" />';
echo '<meta property="og:type" content="'. $type .'" />';
if ( $image!='' ) echo '<meta property="og:image" content="'. $image .'" />';
echo '<meta property="og:url" content="'. $url .'" />';
echo '<meta property="og:title" content="'. $title .'" />';
echo '<meta property="og:description" content="'. $description .'" />';
echo "\n";
?>
<?php endif ?>
<link rel="alternate" type="application/rss+xml" href="<?php echo $WebPath; ?>/RSS.php" title="What's New"> 
<link rel="icon" type="image/x-icon" href="<?php echo $WebPath; ?>/favicon.ico">
<link rel="apple-touch-icon" href="<?php echo $WebPath; ?>/favicon.png" />

<!--[if lte IE 8]><style>
.slide span {
    display: inline-block;
    height: 100%;
}
.calendar, .calendar .bubble, .calendar .month {
	behavior: url(<?php echo $WebPath; ?>/PIE.php);
}
</style><![endif]-->
<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<?php
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"". $WebPath ."/css.php?kat=".$_GET['kat'].(contains($_SERVER['QUERY_STRING'],'nomenu')?'&nomenu':'').(contains($_SERVER['QUERY_STRING'],'noextra')?'&noextra':'')."\">\n";
echo "<script language=\"javascript\" type=\"text/javascript\" src=\"". $WebPath ."/js/funcs.js\"></script>\n";
// jQuery
echo "<script type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js\"></script>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/smoothness/jquery-ui.css\">\n";
echo "<script type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js\"></script>\n";
// Twitter widgets
echo "<script type=\"text/javascript\" id=\"twitter-wjs\" src=\"//platform.twitter.com/widgets.js\"></script>\n";

// mobile dependant JS
if ( $Mobile || $Tablet ) {
	// PhotoSwipe used
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"". $WebPath ."/js/photoswipe/photoswipe.css\" media=\"screen\">\n";
	echo "<script type=\"text/javascript\" src=\"". $WebPath ."/js/photoswipe/lib/klass.min.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"". $WebPath ."/js/photoswipe/code.photoswipe.jquery-3.0.5.min.js\"></script>\n";
} else {
	// FancyBox used
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"". $WebPath ."/js/fancybox/jquery.fancybox-1.3.4.css\" media=\"screen\">\n";
	echo "<script type=\"text/javascript\" src=\"". $WebPath ."/js/fancybox/jquery.easing-1.3.pack.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"". $WebPath ."/js/fancybox/jquery.mousewheel-3.0.4.pack.js\"></script>\n";
	echo "<script type=\"text/javascript\" src=\"". $WebPath ."/js/fancybox/jquery.fancybox-1.3.4.pack.js\"></script>\n";
}
echo "<script type=\"text/javascript\" src=\"//maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false\"></script>\n";
echo "<script language=\"javascript\" type=\"text/javascript\" src=\"". $WebPath ."/js/custom.js\"></script>\n";
?>
<script type="text/javascript">
<?php if ( !($Mobile || $Tablet) ) : ?>
// fancybox title formatting callback
function formatTitle(title, currentArray, currentIndex, currentOpts) {
	var file, name, id, str;
	var url = document.location.protocol + '//' + document.location.hostname + '<?php echo $WebPath ?>';
	str = '<div id="fancybox-title-over">';
	if ( title && title.length ) {
		file = title.match(/\/[^\/]*\//i);
		name = title.substring(0,title.search(/\/[^\)]*\//i));
		id   = title.substring(title.lastIndexOf('/')+1);
		str += ( file && file[file.length-1].length > 4 ? '<span style="float:right;"><a href="' + url + '/?tmpl=Slika' + ( id.length ? '&mID=' + id : '&pID=' + ( file && file.length ? file[file.length-1].substring(1,file[file.length-1].length-1) : title ) ) + '&nomenu&noextra\"><img src="' + url + '/pic/info.png" alt="Info" border="0"></a></span>' : '' );
		str += ( name && name.length >= 4 ? '<b style="display:block;">' + name + '</b>' : '<span class="a9" style="display:block;">' + title + '</span>' );
		str += ( file && file[file.length-1].length > 4 ? '<span class="a9" style="display:block;">' + file[file.length-1].substring(1,file[file.length-1].length-1) + '</span>' : '' );
	}
	//if ( currentArray.length > 1 )
	//	str += '<span class="a8" style="float:right;">' + (currentIndex + 1) + '/' + currentArray.length + '</span>';
	str += '</div>';
	return str;
}
<?php endif ?>
$(document).ready(function(){
<?php if ( $Mobile || $Tablet ) : ?>
	setTimeout(function() { window.scrollTo(0, 1) }, 100);
	try {
		var myPhotoSwipe1 = $("#Gallery a").photoSwipe({
			imageScaleMethod: <?php echo ( !$Mobile? "\"fitNoUpscale\"": "\"fit\"" ); ?>,
			captionAndToolbarAutoHideDelay: 2500,
			slideshowDelay: 5000
		});
	} catch (err) {}
	try {
		var myPhotoSwipe2 = $("a[rel^='lightbox_'].fancybox").photoSwipe({
			imageScaleMethod: <?php echo ( !$Mobile? "\"fitNoUpscale\"": "\"fit\"" ); ?>,
			captionAndToolbarAutoHideDelay: 2500,
			slideshowDelay: 5000
		});
	} catch (err) {}
	try {
		var myPhotoSwipe3 = $("a[rel='lightbox'].fancybox").photoSwipe({
			imageScaleMethod: "fitNoUpscale",
			captionAndToolbarAutoHideDelay: 2500,
			slideshowDelay: 5000
		});
	} catch (err) {}
<?php else : ?>
	$("a.fancybox").fancybox({
		'padding'       : 7,
		'margin'        : 10,
		'overlayOpacity': 0.9,
		'overlayColor'  : '#cccccc',
		'titlePosition' : 'over',
		'titleFormat'   : formatTitle,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'speedIn'		: 300, 
		'speedOut'		: 200, 
		'overlayShow'	: true,
		'cyclic'        : true,
		'onComplete'    : function() {
			$("#fancybox-title").hide();
			$("#fancybox-wrap").hover(function() {
				$("#fancybox-title").show();
			}, function() {
				$("#fancybox-title").hide();
			});
		}
	});
<?php /*
	// attach zoom icon to images
	var badgeParents = $('.post .imgcenter,.post .imgleft,.post .imgright,.text .imgcenter,.text .imgleft,.text .imgright').parent('A');
	badgeParents.css('position', 'relative');
	badgeParents.append('<img src="<?php echo $WebPath; ?>/pic/magnifyingglass.png" alt="" class="magnifyingglass">');
	badgeParents.each(function() {
		var badge = $('.magnifyingglass', this);
		badge.css({
			padding: '16px 0px 0px 16px',
			width: '16px',
			height: '16px'
		});
		badge.parent().hover(
			function() { 
				badge.animate({
					padding: '0px 0px 0px 0px',
					width: '32px',
					height: '32px'
				}, 150, 'swing', function () {
				});
			},
			function() { 
				badge.animate({
					padding: '16px 0px 0px 16px',
					width: '16px',
					height: '16px'
				}, 150, 'swing', function () {
				});
			}
		)
	});
*/ ?>
<?php endif ?>
	// attach search autocomplete
	$("input[name=S]").autocomplete({
		source: "<?php echo $WebPath; ?>/search.php?format=JSON",
		minLength: 3,
		delay: 500,
		position: {collision: "fit flip"},
		select: function( event, ui ) {
			rub = !isNaN(parseFloat(ui.item.kat)) && isFinite(ui.item.kat) ? "?kat=" + ui.item.kat : "/" + ui.item.kat + "/";
			bes = !isNaN(parseFloat(ui.item.id)) && isFinite(ui.item.id) ? "&ID=" + ui.item.id : ui.item.id + "/";
			document.location = "<?php echo $WebPath . ($IsIIS ? '/index.php' : ''); ?>" + rub + bes;
		}
	});
});
</script>
