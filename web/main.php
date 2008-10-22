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
?>
<?php

global $conf;

ob_start();

require("includes/session.inc.php");
require("includes/config.inc.php");
require_once("includes/i18n.inc.php");
require("modules/base/includes/edit.inc.php");
require("includes/acl.inc.php");

/**
 Lookup and load all MMC modules
 */
function autoInclude() {
    global $redirArray;
    global $redirAjaxArray;
    global $conf;

    includeInfoPackage(fetchModulesList($conf["global"]["rootfsmodules"]));
    includePublicFunc(fetchModulesList($conf["global"]["rootfsmodules"]));

    if (isset($_GET["module"])) {
        $__module = $_GET["module"];
    } else {
        $__module = "default";
    }

    if (isset($_GET["submod"])) {
        $__submod = $_GET["submod"];
    } else {
        $__submod = "default";
    }
    
    if (isset($_GET["action"])) {
        $__action = $_GET["action"];
    } else {
        $__action = "default";
    }

    /* Redirect user to a default page. */
    if (
        (!isset($redirArray[$__module][$__submod][$__action]))
        && (!isset($redirAjaxArray[$__module][$__submod][$__action]))
        ) {
        $__module = "base";
        $__submod = "main";
        $__action = "default";
    }

    if (!isNoHeader($__module, $__submod, $__action)) {
        require_once("graph/header.inc.php");
        /* Include specific module CSS if there is one */
        require("graph/dynamicCss.php");
    }

    /* ACL check */
    if (!hasCorrectAcl($__module,$__submod,$__action)) {
        $__module = "base";
        $__submod = "main";
        $__action = "default";
        global $acl_error;
        $acl_error = _("Error, you don't have correct rights !");
        echo "<script>\n";
        echo "window.location = '".getDefaultPage()."';";
        echo "</script>\n";
        return;
    }
    
    if (!empty($redirArray[$__module][$__submod][$__action])) {
        require($redirArray[$__module][$__submod][$__action]);
    } else {
        if (!empty($redirAjaxArray[$__module][$__submod][$__action])) {
            require($redirAjaxArray[$__module][$__submod][$__action]);
        }
        else {
            require("main_content.php");
        }
    }

    if (!isNoHeader($__module, $__submod, $__action)) {
        require_once("graph/footer.inc.php");
    }
}


global $maxperpage;
$root = $conf["global"]["root"];
$maxperpage = $conf["global"]["maxperpage"];

//include PageGenerator primitives
require("includes/PageGenerator.php");

autoInclude();

ob_end_flush();
?>
