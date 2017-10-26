<?php
/**
 *
 * (c) 2015-2017 Siveo, http://http://www.siveo.net
 *
 * $Id$
 *
 * This file is part of Mandriva Management Console (MMC).
 *
 * MMC is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * MMC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MMC; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * File : logsimaging.php
 */

 /*
 this page show logs table
+-------------+------------------+------+-----+-------------------+----------------+
| Field       | Type             | Null | Key | Default           | Extra          |
+-------------+------------------+------+-----+-------------------+----------------+
| date        | timestamp        | NO   |     | CURRENT_TIMESTAMP |                |
| fromuser    | varchar(45)      | YES  |     | NULL              |                |
| touser      | varchar(45)      | YES  |     | NULL              |                |
| action      | varchar(45)      | YES  |     | NULL              |                |
| type        | varchar(6)       | NO   |     | noset             |                |
| module      | varchar(45)      | YES  |     |                   |                |
| text        | varchar(255)     | NO   |     | NULL              |                |
| sessionname | varchar(20)      | YES  |     |                   |                |
| how         | varchar(255)     | YES  |     | ""                |                |
| who         | varchar(45)      | YES  |     | ""                |                |
| why         | varchar(255)     | YES  |     | ""                |                |
| priority    | int(11)          | YES  |     | 0                 |                |
+-------------+------------------+------+-----+-------------------+----------------+

Module | Action | How | From user

Inventory | Inventory requested | New machine | Master
Inventory | Inventory reception | Planned | Machine
Inventory | Inventory requested | Deployment | User
Inventory | Inventory requested | Quick Action | User

Backup | Backup configuration | Manual | User
Backup | Full backup requested | Planned | BackupPC
Backup | Full backup requested | Manual | User
Backup | Incremental backup requested | Planned | BackupPC
Backup | Incremental backup requested | Manual | User
Backup | Reverse SSH start | Backup | ARS
Backup | Reverse SSH stop | Backup | ARS
Backup | Restore requested | Manual | User
Backup | Reverse SSH start | Restore | ARS
Backup | Reverse SSH stop | Restore | ARS

Deployment | Deployment planning | Manual | User
Deployment | Deployment planning | Convergence | User
Deployment | Deployment execution | Manual | User
Deployment | Deployment execution | Planned | User
Deployment | Deployment execution | Convergence | ARS ou Master
Deployment | WOL sent | Deployment | ARS

QuickAction | WOL sent | Manual | User
QuickAction | Inventory requested | Manual | User
QuickAction | Inventory reception | Manual | User
QuickAction | Shutdown sent | Manual | User
QuickAction | Reboot sent | Manual | User



Packaging | Package creation | Manual | User
Packaging | Package edition | Manual | User
Packaging | Package deletion | Manual | User
Packaging | Bundle creation | Manual | User
Packaging | Bundle edition | Manual | User
Packaging | Bundle deletion | Manual | User

Remote desktop | service| Manual | User
Remote desktop | Remote desktop control request | Manual | User
Remote desktop | Reverse SSH start | Remote desktop control request | ARS
Remote desktop | Reverse SSH stop | Remote desktop control request | ARS


From user (Acteur): Normalement utilisateur loggué à Pulse (pour MMC), Agent Machine, Master, ARS
Action: L'action
Module: Le module
Text: Détail
How: Le contexte: par exemple, lors d'un déploiement, planifié, etc.
Who: Nom du groupe ou de la machine
Why: Groupe ou machine


*/
?>

<?php
    require("graph/navbar.inc.php");
    require("localSidebar.inc.php");

    class DateTimeTplnew extends DateTimeTpl{

        function DateTimeTplnew($name, $label = null){
            $this->label = $label;
            parent::__construct($name);
        }

        function display($arrParam = array()) {
            print "<label for=\"".$this->name."\">".$this->label."</label>\n";
            parent::display($arrParam);
        }
    }

class SelectItemlabeltitle extends SelectItem {
    var $title;
    /**
     * constructor
     */
    function SelectItemlabeltitle($idElt, $label = null, $title = null, $jsFunc = null, $style = null) {
        $this->title = $title;
        $this->label = $label;
        parent::SelectItem($idElt, $jsFunc, $style);
    }

    function to_string($paramArray = null) {
        $ret = "";
        if ($this->label){
            $ret = "<label for=\"".$this->id."\">".$this->label."</label>\n";
        }

        $ret .= "<select";
        if ($this->title){
            $ret .= " title=\"" . $this->title . "\"";
        }
        if ($this->style) {
            $ret .= " class=\"" . $this->style . "\"";
        }
        if ($this->jsFunc) {
            $ret .= " onchange=\"" . $this->jsFunc . "(";
            if ($this->jsFuncParams) {
                $ret .= implode(", ", $this->jsFuncParams);
            }
            $ret .= "); return false;\"";
        }
        $ret .= isset($paramArray["required"]) ? ' rel="required"' : '';
        $ret .= " name=\"" . $this->name . "\" id=\"" . $this->id . "\">\n";
        $ret .= $this->content_to_string($paramArray);
        $ret .= "</select>";
        return $ret;
    }
}


// ------------------------------------------------------------------------------------------------
    $p = new PageGenerator(_("Imaging Logs"));
    $p->setSideMenu($sidemenu);
    $p->display();
    $filterlogs = "Imaging";
    $headercolumn= "date@fromuser@who@text";
?>

<script type="text/javascript">

var filterlogs = <?php echo "'$filterlogs'";?>;

function encodeurl(){
    var critere = filterlogs + "|" + jQuery('#criterionssearch option:selected').val();
    uri = "modules/base/logview/ajax_Data_Logs.php"
    //QuickAction
    var param = {
        "start_date" : jQuery('#start_date').val(),
        "end_date"   : jQuery('#end_date').val(),
        "type" : "",
        "action" : "",
        "module" : critere,
        "user" : "",
        "how" : "",
        "who" : "",
        "why" : "",
        "headercolumn" : "<?php echo $headercolumn; ?>"
    }
    uri = uri +"?"+xwwwfurlenc(param)
    return uri
}

function xwwwfurlenc(srcjson){
    if(typeof srcjson !== "object")
      if(typeof console !== "undefined"){
        console.log("\"srcjson\" is not a JSON object");
        return null;
      }
    u = encodeURIComponent;
    var urljson = "";
    var keys = Object.keys(srcjson);
    for(var i=0; i <keys.length; i++){
        urljson += u(keys[i]) + "=" + u(srcjson[keys[i]]);
        if(i < (keys.length-1))urljson+="&";
    }
    return urljson;
}

jQuery(function(){
    jQuery("p").click(function(){
        searchlogs( encodeurl());
    //jQuery('#tablelog').DataTable().ajax.reload(null, false).draw();
    });
});
    function searchlogs(url){
        jQuery('#tablelog').DataTable()
                            .ajax.url(
                                url
                            )
                            .load();
    }


    jQuery(function(){
        searchlogs("modules/base/logview/ajax_Data_Logs.php?start_date=&end_date=&type=&action=&module=<?php echo $filterlogs; ?>%7CNone&user=&how=&who=&why=&headercolumn=<?php echo $headercolumn; ?>")
    } );
    </script>

<?php
/*
Imaging | Menu change | Manual | User
Imaging | Menu change | WOL | User
Imaging | Menu change | Multicast | User
Imaging | Post-imaging script creation | Manual | User
Imaging | Master creation | Manual | User
Imaging | Master edition | Manual | User
Imaging | Master deletion | Manual | User
Imaging | Master deployment | Manual | User
Imaging | Master deployment | Multicast | User
Imaging | Backup image creation | Manual | User
Imaging | Backup image creation | WOL | User
Imaging | Image deployment | Manual | User
Imaging | Image deployment | WOL | User
Imaging | Image deletion | Manual | User
*/

$typecritere  =        array(
                                        _T('Menu change','logs'),
                                        _T('Post-imaging Script Creation','logs'),
                                        _T('Master Creation','logs'),
                                        _T('Master Edition','logs'),
                                        _T('Master Deletion','logs'),
                                        _T('Master Deployment','logs'),
                                        _T('Master Deployment Multicast','logs'),
                                        _T('Backup Image creation','logs'),
                                        _T('Image Deployment','logs'),
                                        _T('WOL','logs'),
                                        _T('Image Deletion','logs'),
                                        _T('no criteria selected','logs'));

$typecritereval  =        array(
                                        'Menu',
                                        'Post-imaging',
                                        'creation | Master',
                                        'deletion | Master',
                                        'deployment | Master',
                                        'Multicast | Master',
                                        'Backup| Image | ',
                                        'Image | deployment',
                                        'WOL',
                                        'Image|deletion',
                                        'None');


$start_date =   new DateTimeTplnew('start_date', "Start Date");
$end_date   =   new DateTimeTplnew('end_date', "End Date");


$modules = new SelectItemlabeltitle("criterionssearch", "criterions", "critere search");
$modules->setElements($typecritere);
$modules->setSelected("None");
$modules->setElementsVal($typecritereval);

?>

<style>

.inline { display : inline; }

}

</style>
<?php

?>


<div style="overflow-x:auto;">
    <table border="1" cellspacing="0" cellpadding="5" class="listinfos">
        <thead>
            <tr>
                <th><?php echo $start_date->display(); ?></th>
                <th><?php echo $end_date->display(); ?></th>
                <th><?php echo $modules->display(); ?></th>
            </tr>
        </thead>
     </table>
</div>


<p class="btnPrimary">
  Filter logs
</p>

<br>

<table id="tablelog" width="100%" border="1" cellspacing="0" cellpadding="1" class="listinfos">
        <thead>
            <tr>
                <th style="width: 12%;">date</th>
                <th style="width: 8%;">user</th>
                <th style="width: 6%;">who</th>
         <!--
                <th style="width: 6%;">type</th>
                <th style="width: 6%;">action</th>
                <th style="width: 6%;">module</th>

                <th style="width: 6%;">how</th>

                <th style="width: 6%;">why</th>

                <th style="width: 6%;">priority</th>
                <th style="width: 6%;">touser</th>
                <th style="width: 6%;">sessionname</th>
        -->
                <th>text</th>
            </tr>
        </thead>

    </table>
