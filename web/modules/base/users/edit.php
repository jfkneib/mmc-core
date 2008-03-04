<?php
/**
 * (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
 * (c) 2007-2008 Mandriva, http://www.mandriva.com
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
 */

require("modules/base/includes/users.inc.php");
require("modules/base/includes/groups.inc.php");
require("localSidebar.php");
require("graph/navbar.inc.php");

/**
 * Resize a jpg file if it is greater than $maxwidth or $maxheight
 * 
 * @returns: the file name of the resized JPG file
 */
function resizeJpg($source, $maxwidth, $maxheight) {
    list($width, $height) = getimagesize($source);
    if (($width > $maxwidth) || ($height > $maxheight)) {
        if ($width > $height) {
            $newwidth = $maxwidth;
            $newheight = $newwidth * $height / $width;
        } else {
            $newheight = $maxheight;
            $newwidth = $newheight * $width / $height;
        }
        $image = imagecreatefromjpeg($source);
        $newimage = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresized($newimage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $ret = tempnam("/notexist", ".jpg");
        imagejpeg($newimage, $ret);
        imagedestroy($image);
        imagedestroy($newimage);
    } else {
        /* No resize needed */
        $ret = $source; 
    }
    return $ret;
}


global $result;
global $error;

//verify validity of information
if (isset($_POST["buser"])) {

$nlogin = $_POST["nlogin"];
$name = $_POST["name"];
$firstname = $_POST["firstname"];
$confpass = $_POST["confpass"];
$homedir = $_POST["homeDir"];
$loginShell = $_POST["loginShell"];

$detailArr["cn"][0]=$nlogin;
$detailArr["givenName"][0]=$firstname;
$detailArr["sn"][0]=$name;
$pass = $_POST["pass"];
$desactive = $_POST["isBaseDesactive"];

if ($pass != $confpass) {
    $error.= _("The confirmation password does not match the new password.")." <br/>";
    setFormError("pass");
}

if (!preg_match("/^[a-zA-Z0-9][A-Za-z0-9_.-]*$/", $nlogin)) {
    $error.= _("User's name invalid !")."<br/>";
    setFormError("login");
}

/* Check that the primary group name exists */
 $primary = $_POST["primary_autocomplete"];
 if (!strlen($primary)) {
   global $error;
    setFormError("primary_autocomplete");
    $error.= _("The primary group field can't be empty.")."<br />";
} else if (!existGroup($primary)) {
    global $error;
    setFormError("primary_autocomplete");
    $error.= sprintf(_("The group %s does not exist, and so can't be set as primary group."), $primary) . "<br />";
 }


//verify validity with plugin function
callPluginFunction("verifInfo",array($_POST));


    //if this user does not exist (not editing a user)
    if (!$error&&($_GET["action"]=="add")) {
        if (!exist_user($nlogin)) {
            if ($pass =='') {//if we not precise a password
                $error.= _("Password is empty.")."<br/>"; //refuse addition
                setFormError("pass");
            } else {  //if no problem
                $createHomeDir = isset($_POST["createHomeDir"]);
                $result = add_user($nlogin, $pass, $firstname, $name, $homedir, $createHomeDir, $_POST["primary_autocomplete"]);
                if (strlen($_POST['mail']) > 0) changeUserAttributes($nlogin, "mail", $_POST["mail"]);
		if (strlen($loginShell) > 0) changeUserAttributes($nlogin, "loginShell", $loginShell);
                $_GET["user"]=$nlogin;
                $newuser=true;
            }
        }
        else { //if user exist
            $error.= _("This user already exists.")."<br/>";
        }
    }
} elseif ($_POST["benable"]) {
    $ret = callPluginFunction("enableUser", $_GET["user"]);
    $result = _("User enabled.");
} elseif ($_POST["bdisable"]) {
    $ret = callPluginFunction("disableUser",$_GET["user"]);
    $result = _("User disabled.");
}


//if we edit a user
// /!\ if we create a user... add smb attr or OX attr
// it'll be consider as modification
if ($_GET["user"]) {

  if (!$error) {
      global $result;
      if ($_POST["deletephoto"]) {
        changeUserAttributes($_POST["nlogin"], "jpegPhoto", null);
      } else if ($_POST["buser"]) { //if we submit modification
         if ($_POST['isBaseDesactive']) { //desactive user
              changeUserAttributes($nlogin,'loginShell','/bin/false');
              $result .= _("User disabled.")."<br />";
         } else { //else if it desactive, reactive him
	     if (($_POST['loginShell'] == "/bin/false") || ($_POST['loginShell'] == "")) {
                 $newshell = "/bin/bash";
                 $result .= _("User enabled.")."<br />";
	     } else $newshell = $_POST['loginShell'];
	     changeUserAttributes($nlogin, 'loginShell', $newshell);
         }

         if ($_POST["homeDir"]) move_home($nlogin, $_POST["homeDir"]);

         // Change user attributes
	 changeUserTelephoneNumbers($nlogin, $_POST["telephoneNumber"]);
         changeUserAttributes($nlogin, "title", $_POST["title"]);
         changeUserAttributes($nlogin, "mobile", $_POST["mobile"]);
         changeUserAttributes($nlogin, "facsimileTelephoneNumber", $_POST["facsimileTelephoneNumber"]);
         changeUserAttributes($nlogin, "homePhone", $_POST["homePhone"]);
	 if (strlen($_POST["cn"]) > 0) changeUserAttributes($nlogin, "cn", $_POST["cn"]);
	 if ($newuser) {
	     if (strlen($_POST["mail"]) > 0) changeUserAttributes($nlogin, "mail", $_POST["mail"]);
	     if (strlen($_POST["displayName"]) > 0) changeUserAttributes($nlogin, "displayName", $_POST["displayName"]);
	 } else {
	     changeUserAttributes($nlogin, "mail", $_POST["mail"]);
             changeUserAttributes($nlogin, "displayName", $_POST["displayName"]);
	 }
	 /* Change photo */
	 if (!empty($_FILES["photofilename"]["name"])) {            

	   if (strtolower(substr($_FILES["photofilename"]["name"], -3)) == "jpg") {
               $pfile = $_FILES["photofilename"]["tmp_name"];
	       $size = getimagesize($pfile);
               if ($size["mime"] == "image/jpeg") {
		 $maxwidth = 320;
		 $maxheight = 320;
                 if (in_array("gd", get_loaded_extensions())) {
                     /* Resize file if GD extension is installed */
                     $pfile = resizeJpg($_FILES["photofilename"]["tmp_name"], $maxwidth, $maxheight);                         
                 }
                 list($width, $height) = getimagesize($pfile);
		 if (($width <= $maxwidth) && ($height <= $maxheight)) {
                     $obj = new Trans();
                     $obj->scalar = "";
                     $obj->xmlrpc_type = "base64";
                     $f = fopen($pfile, "r");
                     while (!feof($f)) $obj->scalar .= fread($f, 4096);  
                     fclose($f);
                     unlink($pfile);
                     changeUserAttributes($nlogin, "jpegPhoto", $obj, False);
                 } else $error .= sprintf(_("The photo is too big. The max size is %s x %s."), $maxwidth, $maxheight) . "<br/>";
	       } else $error .= _("The photo is not a JPG file.") . "<br/>";
	   } else $error .= _("The photo is not a JPG file.") . "<br/>";
	 }	 

         change_user_main_attr($_GET["user"], $nlogin, $firstname, $name);
         $result.=_("Attributes updated.")."<br />";

         if (!isset($_POST["groupsselected"])) $_POST["groupsselected"] = array();
         // Create/modify user in all enabled MMC modules
         callPluginFunction("changeUser",array($_POST));

          //if we change the password
         if (($_POST["pass"] == $_POST["confpass"]) && ($_POST["pass"] != "")) {
             callPluginFunction("changeUserPasswd", array(array($_GET["user"], prepare_string($_POST["pass"]))));

             //update result display
             $result.=_("Password updated.")."<br />";
         }

         /* Primary group management */
         $primaryGroup = getUserPrimaryGroup($_POST['nlogin']);
         if ($_POST["primary_autocomplete"] != $primaryGroup) {
             /* Update the primary group */
             callPluginFunction("changeUserPrimaryGroup", array($_POST['nlogin'], $_POST["primary_autocomplete"], $primaryGroup));
         }

         /* Secondary groups management */
         $old = getUserSecondaryGroups($_POST['nlogin']);
         $new = $_POST['groupsselected'];
         foreach (array_diff($old, $new) as $group) {
             del_member($group, $_POST['nlogin']);
	     callPluginFunction("delUserFromGroup", array($_POST['nlogin'], $group));
         }
         foreach (array_diff($new, $old) as $group) {
             add_member($group, $_POST['nlogin']);
	     callPluginFunction("addUserToGroup", array($_POST['nlogin'], $group));
         }

     }
  }
  $detailArr = getDetailedUser($_GET["user"]);

  $enabled = isEnabled($_GET["user"]);
}

if (strstr($_SERVER[HTTP_REFERER],'module=base&submod=users&action=add') && $_GET["user"])
    if (!isXMLRPCError()) {
        $result = sprintf(_("User %s has been successfully created."), $_GET["user"]);
    }

//display result message
if (isset($result)&&!isXMLRPCError()) {
    new NotifyWidgetSuccess($result);
}

//display error message
if (isset($error)) {
    new NotifyWidgetFailure($error);
}

if (isset($_SESSION["addusererror"])) {
    new NotifyWidgetWarning("The user has not been completely created because of the following error(s):" . "<br/><br/>" .  $_SESSION["addusererror"]);
    unset($_SESSION["addusererror"]);
}


//title differ with action
if ($_GET["action"]=="add") {
    $title = _("Add user");
    $activeItem = "add";
} else {
    $title = _("Edit user");
    $activeItem = "index";
}

$p = new PageGenerator($title);
$sidemenu->forceActiveItem($activeItem);
$p->setSideMenu($sidemenu);
$p->display();

?>

<div>
<form id="edit" enctype="multipart/form-data" method="post" onsubmit="selectAll(); return validateForm();">
<div class="formblock" style="background-color: #F4F4F4;">
<table cellspacing="0">
<?php

 // Fetch uid/gid if we create a user
 if ($_GET["action"] == "add") {
     $detailArr["uidNumber"][0] = maxUID() + 1;
     $detailArr["gidNumber"][0] = maxGID() + 1;
 }
?>
<?php

//display form

if ($_GET["action"]=="add") {
    $formElt = new InputTpl("nlogin",'/^[a-zA-Z0-9][A-Za-z0-9_.-]*$/');
} else {
    $formElt = new HiddenTpl("nlogin");
}

$test = new TrFormElement(_("Login"),$formElt);
$test->setCssError("login");
$test->display(array("value"=>$detailArr["uid"][0]));

$test = new TrFormElement(_("Password"),new PasswordTpl("pass"));
$test->setCssError("pass");
$test->display(null);

$test = new TrFormElement(_("Confirm password"),new PasswordTpl("confpass"));
$test->setCssError("pass");
$test->display(null);


$test = new TrFormElement(_("Photo"), new ImageTpl("jpegPhoto"));
$test->setCssError("Photo");
$test->display(array("value" => $detailArr["uid"][0], "action" => $_GET["action"]));

$test = new TrFormElement(_("Name"),new InputTpl("name"));
$test->display(array("value"=>$detailArr["sn"][0]));

$test = new TrFormElement(_("First name"),new InputTpl("firstname"));
$test->display(array("value"=>$detailArr["givenName"][0]));

$test = new TrFormElement(_("Title"),new InputTpl("title"));
$test->display(array("value"=>$detailArr["title"][0]));

$email = new InputTpl("mail",'/^([A-Za-z0-9._%-]+@[A-Za-z0-9.-]+){0,1}$/');
$test = new TrFormElement(_("Mail address"), $email);
$test->setCssError("mail");
$test->display(array("value"=>$detailArr["mail"][0]));

print "</table>";
$phoneregexp = "/^[a-zA-Z0-9(-/ ]*$/";
if (!isset($detailArr['telephoneNumber'])) $detailArr['telephoneNumber'] = array('');
$tn = new MultipleInputTpl("telephoneNumber",_("Telephone number"));
$tn->setRegexp($phoneregexp);
$phone = new FormElement(_("Telephone Number"), $tn);
$phone->display($detailArr['telephoneNumber']);
print '<table cellspacing="0">';

$test = new TrFormElement(_("Mobile number"), new InputTpl("mobile", $phoneregexp));
$test->setCssError("mobile");
$test->display(array("value"=>$detailArr["mobile"][0]));

$test = new TrFormElement(_("Fax number"), new InputTpl("facsimileTelephoneNumber", $phoneregexp));
$test->setCssError("facsimileTelephoneNumber");
$test->display(array("value"=>$detailArr["facsimileTelephoneNumber"][0]));

$test = new TrFormElement(_("Home phone number"), new InputTpl("homePhone", $phoneregexp));
$test->setCssError("homePnone");
$test->display(array("value"=>$detailArr["homePhone"][0]));



$checked="";
if ($detailArr["uid"][0]) {
if ($detailArr["loginShell"][0]=='/bin/false') {
            $checked = "checked";
        }
}
$param = array ("value" => $checked);

$test = new TrFormElement(_("User is disabled, if checked"), new CheckboxTpl("isBaseDesactive"),
        array("tooltip"=>_("A disabled user can't log in any UNIX services. <br/>
                            Her/his login shell command is replaced by /bin/false"))
        );
$test->setCssError("isBaseDesactive");
$test->display($param);

?>
</table>
<div id="expertMode" <?displayExpertCss();?>>
<table cellspacing="0">
<?php

$test = new TrFormElement(_("Home directory"),new InputTpl("homeDir"));
$test->display(array("value"=>$detailArr["homeDirectory"][0]));

if ($_GET["action"] == "add") {
    $test = new TrFormElement(_("Create home directory on filesystem"), new CheckboxTpl("createHomeDir"));
    $test->display(array("value" => "CHECKED"));
}

$test = new TrFormElement(_("Login shell"),new InputTpl("loginShell"));
$test->display(array("value" => $detailArr["loginShell"][0]));

$test = new TrFormElement(_("Common name"),new InputTpl("cn"),
			  array("tooltip" => _("This field is used by some LDAP clients (for example Thunderbird address book) to display user entries."))
			  );
$test->display(array("value"=>$detailArr["cn"][0]));

$test = new TrFormElement(_("Preferred name to be used"),new InputTpl("displayName"),
			  array("tooltip" => _("This field is used by SAMBA (and other LDAP clients) to display user name."))
			  );
$test->display(array("value"=>$detailArr["displayName"][0]));?>

<tr><td style="text-align: right;"><? print "UID : ".$detailArr["uidNumber"][0]; ?></td>
<td><? print "GID : ".$detailArr["gidNumber"][0];?></td></tr>

</table>
</div>


<?php

setVar('detailArr',$detailArr);

$existACL = existAclAttr("groups");

if (!$existACL) {
    $aclattrright = "rw";
    $isAclattrright = true;
} else {
    $aclattrright = getAclAttr("groups");
    $isAclattrright = ($aclattrright != '');
}

if ($aclattrright=="rw") {
    renderTPL("editGroups");
} else {
    if ($aclattrright=="ro") {
        renderTPL("roGroups");
    }  else {
        renderTPL("norightGroups");
    }
}


print '</div>';

//call plugin baseEdit form
callPluginFunction("baseEdit",array($detailArr,$_POST));
?>

<input name="buser" type="submit" class="btnPrimary" value="<?= _("Confirm"); ?>" />
<input name="breset" type="reset" class="btnSecondary" onclick="window.location.reload( false );" value="<?= _("Cancel"); ?>" />



</form>
</div>

<?php

//if we create a new user redir in edition
if ($newuser&&!isXMLRPCError()) {
    if (isset($error)) {
        /* We will use this variable to display a warning on the edit page */
        $_SESSION["addusererror"] = $error;
    }
    header("Location: " . urlStrRedirect("base/users/edit", array("user" => $nlogin)));
}


?>
