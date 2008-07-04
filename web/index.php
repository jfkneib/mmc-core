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

ob_start();
session_start();
require("includes/config.inc.php");

require("modules/base/includes/users.inc.php");
require("modules/base/includes/edit.inc.php");
require("modules/base/includes/groups.inc.php");
require("includes/PageGenerator.php");


$root = $conf["global"]["root"];

if (isset($_POST["bConnect"])) {
    $login = $_POST["username"];
    $pass = $_POST["password"];
    
    /* Session creation */
    $ip = ereg_replace('\.','',$_SERVER["REMOTE_ADDR"]);
    $sessionid = md5 (time() . $ip . mt_rand());
    
    session_destroy();
    session_id($sessionid);
    session_start();

    $_SESSION["ip_addr"] = $_SERVER["REMOTE_ADDR"];
    if (isset($conf[$_POST["server"]])) {
        $_SESSION["XMLRPC_agent"] = parse_url($conf[$_POST["server"]]["url"]);
        $_SESSION["agent"] = $_POST["server"];
    } else {
        $error = sprintf(_T("The server %s does not exist"), $_POST["server"]);
    }

    if (!isset($error) && auth_user($login, $pass, $error)) {
        $_SESSION["login"] = $login;
        $_SESSION["pass"] = $pass;
        /* Set session expiration time */
        $_SESSION["expire"] = time() + 90 * 60;

        $_SESSION['lang'] = $_POST['lang'];
        setcookie('lang', $_POST['lang'], time() + 3600 * 24 * 30);

        list($_SESSION["acl"], $_SESSION["acltab"], $_SESSION["aclattr"]) = createAclArray(getAcl($login));
        $_SESSION["supportModList"] = xmlCall("base.getModList",null);

        /* Register module version */
        $_SESSION["modListVersion"]['rev'] = xmlCall("getRevision",null);
        $_SESSION["modListVersion"]['ver'] = xmlCall("getVersion",null);

        /* Make the comnpany logo effect */
        $_SESSION["doeffect"] = True;

        /* Redirect to main page */
        header("Location: " . $root . "main.php");
        exit;
    } else {
        $_SESSION['lang'] = $_POST['lang'];
        require("includes/i18n.inc.php");
        if (!isXMLRPCError()) $error = _("Login failed");
    }
}

if ($_GET["error"]) $error = urldecode($_GET["error"]) . "<br/>" . $error;
if ($_GET["agentsessionexpired"]) {
    $error = _T("You have been logged out because the session between the MMC web interface and the MMC agent expired.");
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Mandriva Linux / Mandriva Management Console</title>
	<link href="<?php echo $root; ?>graph/login/index.css" rel="stylesheet" media="screen" type="text/css" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="imagetoolbar" content="false" />
	<meta name="Description" content="" />
	<meta name="Keywords" content="" />
        <script src="jsframework/lib/prototype.js" type="text/javascript"></script>
        <script src="jsframework/src/scriptaculous.js" type="text/javascript"></script>
</head>
<body onload="Form.focusFirstElement('loginForm')">

<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">
	<table width="467" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" valign="middle">

        <div id="header">
        <div id="headerLeft"><div id="headerRight">

<!-- Put header content here  -->

        <p class="lock"><?= $conf["logintitle"][$_SESSION["lang"]] ;?></p>

        </div></div></div>

        <div id="interface">
        <div id="content">

<?php


$n = new NotifyWidget();

if (isset($_SESSION['__notify'])) {
    foreach ($_SESSION['__notify'] as $err){ //add notify widget error
        $error = $error . $err.'<br/>';
    }
    $n->flush();
}

if (isset($error)) {
    echo "<div id=\"alert\">".$error."</div>\n";
}
?>

        <div id="login">

<!--Login content -->

        <img src="<?php echo $root; ?>img/login/logo_mandriva_small.png" alt="">

		<form action="<?php echo $root; ?>index.php" method="post" name="loginForm" id="loginForm" target="_self">

			<p><?= _("Login");?> :<br>
			<input name="username" type="text" class="textfield" id="username" size="18"
<?php
			echo "value=\"$login\"";
?>
			/>
			</p>

			<p><?= _("Password");?> :<br>
			<input name="password" type="password" class="textfield" id="password" size="18">
			</p>

                        <p> <?= _("Server");?> :<br>
			<?php

                        global $conf;


                        $servList = array();

                        foreach ($conf as $key => $value) {
                            if (strstr($key,"server_")) {
                                $descList[$key]=$conf[$key]["description"];
                                $labelList[$key]=$key;
                            }
                        }

                        $listbox = new SelectItem("server");
                        $listbox->setElements($descList);
                        $listbox->setElementsVal($labelList);
                        $listbox->setSelected($descList[0]);
                        $listbox->display();

                        ?>
                        <br/>
                        <?= _("Language");?>: <br />

                        <?php

                        $langList = list_system_locales(realpath("modules/base/locale/"));

                        $descList = array();
                        $urlList = array();

                        $langDesc = getArrLocale();

                        foreach ($langList as $value) {
                            if ($langDesc[$value]) {
                                $descList[]=$langDesc[$value];
                            } else {
                                $descList[]=$value;
                            }
                            $urlList[]=$value;
                        }

                        $listbox = new SelectItem("lang");
                        $listbox->setElements($descList);
                        $listbox->setElementsVal($urlList);
                        $listbox->setSelected($descList[0]);

                        if ($_SESSION['lang']) {
                            $listbox->setSelected($_SESSION['lang']);
                        }
                        $listbox->display();

                        ?>

			<input name="bConnect" type="submit" class="btnPrimary" value="<?= _("Connect");?>" /></p>

        </form>

        </div> <!-- login -->
        </div> <!-- content -->
        </div> <!-- interface -->
        <div id="footer">
        <div id="footerLeft"><div id="footerRight">
        </div></div></div>
  		</td>
      </tr>
    </table>
	</td>
  </tr>
<?

if (is_file("license.php")) {
    require("license.php");
}

?>
</table>
</div>

<?if (isset($error)) print '<script type="text/javascript">new Effect.Shake($("alert"));</script>';?>

</body>
</html>
<?
ob_end_flush();
?>
