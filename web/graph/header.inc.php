<?php
/**
 * (c) 2004-2006 Linbox / Free&ALter Soft, http://linbox.com
 *
 * $Id$
 *
 * This file is part of LMC.
 *
 * LMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * LMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>
<?php
/* $Id$ */

$css = $conf["global"]["root"]."graph";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Linbox Management Console</title>
        <link href="jsframework/themes/default.css" rel="stylesheet" media="screen" type="text/css" />
	<link href="<?php echo $css; ?>/master.css" rel="stylesheet" media="screen" type="text/css" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="imagetoolbar" content="false" />
	<meta name="Description" content="" />
	<meta name="Keywords" content="" />
        <script src="jsframework/lib/prototype.js" type="text/javascript"></script>

        <script src="jsframework/src/scriptaculous.js" type="text/javascript"></script>
        <script src="jsframework/common.js" type="text/javascript"></script>

<?php
unset($css);
?>
<script language="javascript">

var myglobalHandlers = {
    onCreate : function() {
        document.getElementById('loadimg').src = "<?php echo $root; ?>img/common/loader_p.gif"
    },
    onComplete: function() {
            if(Ajax.activeRequestCount== 0) {
                document.getElementById('loadimg').src = "<?php echo $root; ?>img/common/loader.gif"
            }
        }
    };

    Ajax.Responders.register(myglobalHandlers);



// pf="prefix" and ck="checked" (0|1)
function checkAll (pf,ck) {
cbox=document.getElementsByTagName('INPUT');
  for (i=0; i<cbox.length; i++){
    if (cbox[i].type=='checkbox'){
      if (cbox[i].name.indexOf(pf) > -1) {
        if (ck == "1") { cbox[i].checked = true; } else { cbox[i].checked = null; }
      }
    }
  }
}



function getStyleObject(objectId) {
    // cross-browser function to get an object's style object given its id
    if(document.getElementById && document.getElementById(objectId)) {
	// W3C DOM
	return document.getElementById(objectId).style;
    } else if (document.all && document.all(objectId)) {
	// MSIE 4 DOM
	return document.all(objectId).style;
    } else if (document.layers && document.layers[objectId]) {
	// NN 4 DOM.. note: this won't find nested layers
	return document.layers[objectId];
    } else {
	return false;
    }
} // getStyleObject

function changeObjectDisplay(objectId, newVisibility) {
    // get a reference to the cross-browser style object and make sure the object exists
    var styleObject = getStyleObject(objectId);
    if(styleObject) {
	styleObject.display = newVisibility;
	return true;
    } else {
	// we couldn't find the object, so we can't change its visibility
	return false;
    }
} // changeObjectVisibility


function toggleVisibility(layer_ref)
{
        var state = getStyleObject(layer_ref).display;

	if (state == 'none')
	{
		state = 'inline';
	} else
	{
		state = 'none';
	}
changeObjectDisplay(layer_ref, state)
}

    function showPopup(evt,url) {
        $('popup').style.width='300px';
        if (!evt) evt = window.event;
        new Ajax.Updater('__popup_container',url,{onComplete: displayPopup(evt), evalScripts:true})
    }

    function displayPopup (evt) {
        obj = document.getElementById('popup')
        obj.style.left = parseInt(evt.clientX)+document.documentElement.scrollLeft-300+"px"
        obj.style.top = (parseInt(evt.clientY)+document.documentElement.scrollTop)+"px"
        getStyleObject('popup').display='inline';
    }

    function showPopupUp(evt,url) {
        $('popup').style.width='300px';
        if (!evt) evt = window.event;
        new Ajax.Updater('__popup_container',url,{onComplete: displayPopupUp(evt), evalScripts:true})

    }

    function displayPopupUp (evt) {
        obj = document.getElementById('popup')
        obj.style.left = parseInt(evt.clientX)+document.documentElement.scrollLeft+"px"
        obj.style.top = (parseInt(evt.clientY)+document.documentElement.scrollTop-350)+"px"
        //new Effect.Appear('popup')
        getStyleObject('popup').display='inline';
    }

    function showPopupCenter(url) {
        new Ajax.Updater('__popup_container',url,{onComplete: displayPopupCenter(), evalScripts:true})

    }

    function displayPopupCenter () {
        obj = document.getElementById('popup')
	var width = $('popup').style.width;
	var widthreal = width.substr( 0, width.length - 2 );
        obj.style.left = ((screen.width-widthreal)/2)+"px";
        obj.style.top = 200+"px";
        getStyleObject('popup').display='inline';
    }
</script>
