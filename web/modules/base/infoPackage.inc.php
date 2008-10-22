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

require_once("modules/base/includes/computers.inc.php");

/**
 * module declaration
 */
$mod = new Module("base");
$mod->setVersion("2.3.1");
$mod->setRevision('$Rev$');
$mod->setAPIVersion("6:0:2");
$mod->setDescription(_("User and group"));
$mod->setPriority(1);

/**
 * define main submod
 */

$submod = new SubModule("main");
$submod->setVisibility(False);

$page = new Page("default",_("Home page"));
$page->setFile("main_content.php");
$page->setOptions(array("visible"=>False));
$submod->addPage($page);
$mod->addSubmod($submod);

$page = new Page("favorites",_("Favorites page"));
$page->setFile("includes/favorites.php");
$page->setOptions(array("visible"=>False,"AJAX" =>True));
$submod->addPage($page);
$mod->addSubmod($submod);


$submod = new SubModule("status");
$submod->setVisibility(True);
$submod->setDescription(_("Status"));
$submod->setImg('img/navbar/load');
$submod->setDefaultPage("base/status/index");
$submod->setPriority(10000);

$page = new Page("index",_("Default status page"));
$page->setFile("modules/base/status/index.php");

$submod->addPage($page);

$mod->addSubmod($submod);

$submod = new ExpertSubModule("logview");
$submod->setVisibility(True);
$submod->setDescription(_("Log view"));
$submod->setImg('img/navbar/logview');
$submod->setDefaultPage("base/logview/index");
$submod->setPriority(10001);

$page = new Page("index",_("LDAP log"));
$page->setFile("modules/base/logview/index.php", array("expert" => True));

$submod->addPage($page);

$page = new Page("show");
$page->setFile("modules/base/logview/ajax_showlog.php",
               array("AJAX" =>True,"visible"=>False));
$submod->addPage($page);

$page = new Page("setsearch");
$page->setFile("modules/base/logview/ajax_setSearch.php",
               array("AJAX" =>True,"visible"=>False));
$submod->addPage($page);

$mod->addSubmod($submod);


/**
 * user submod definition
 */

$submod = new SubModule("users");
$submod->setDescription(_("Users"));
$submod->setImg('img/navbar/user');
$submod->setDefaultPage("base/users/index");
$submod->setPriority(10);

$page = new Page("index",_("User list"));
$submod->addPage($page);

$page = new Page("ajaxAutocompleteGroup");
$page->setFile("modules/base/users/ajaxAutocompleteGroup.php",
               array("AJAX" =>True,"visible"=>False));
$submod->addPage($page);

$page = new Page("ajaxFilter");
$page->setFile("modules/base/users/ajaxFilter.php",
               array("AJAX" =>True,"visible"=>False));
$submod->addPage($page);

$page = new Page("add",_("Add a user"));
$submod->addPage($page);

$page = new Page("edit",_("Edit a user"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("editacl",_("Edit ACL permissions on a user"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("delete",_("Delete a user"));
$page->setFile("modules/base/users/delete.php",
               array("noHeader"=>True,"visible"=>False));
$submod->addPage($page);

$page = new Page("backup",_("Backup user files"));
$page->setFile("modules/base/users/backup.php",
               array("noHeader"=>True,"visible"=>False));
$submod->addPage($page);
                              
$page = new Page("passwd",_("Change user password"));
if ($_SESSION["login"]=='root') {
    $page->setOptions(array("visible"=>False));
}
$submod->addPage($page);

$page = new Page("getPhoto", _("Get user photo"));
$page->setOptions(array("visible"=>False, "noHeader" =>True));
$submod->addPage($page);

$mod->addSubmod($submod);

/**
 * groups submod definition
 */

$submod = new SubModule("groups");
$submod->setDescription(_("Groups"));
$submod->setImg('img/navbar/group');
$submod->setDefaultPage("base/groups/index");
$submod->setPriority(20);


$page = new Page("index",_("Group list"));
$submod->addPage($page);


$page = new Page("add",_("Add a group"));
$submod->addPage($page);

$page = new Page("delete",_("Delete a group"));
$page->setFile("modules/base/groups/delete.php",
               array("noHeader"=>True,"visible"=>False));
$submod->addPage($page);

$page = new Page("ajaxFilter");
$page->setFile("modules/base/groups/ajaxFilter.php",
               array("AJAX"=>True,"visible"=>False));
$submod->addPage($page);

$page = new Page("members",_("Group members"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$page = new Page("edit",_("Edit a group"));
$page->setOptions(array("visible"=>False));
$submod->addPage($page);

$mod->addSubmod($submod);


/* Computer management module */

if (hasComputerManagerWorking()) {
    $submod = new SubModule("computers");
    $submod->setDescription(_("Computers"));
    $submod->setImg('img/navbar/computer');
    $submod->setDefaultPage("base/computers/index");
    $submod->setPriority(30);

    $page = new Page("index", _("Computer list"));
    $submod->addPage($page);

    $page = new Page("add", _("Add computer "));
    if (!canAddComputer()) {
        $page->setOptions(array("visible"=>False));
    }
    $submod->addPage($page);

    $page = new Page("edit", _("Edit computer "));
    $page->setOptions(array("visible"=>False));
    $submod->addPage($page);

    $page = new Page("delete",_("Delete a computer"));
    $page->setFile("modules/base/computers/delete.php",
                   array("noHeader"=>True,"visible"=>False));
    $submod->addPage($page);

    $page = new Page("ajaxComputersList", _("Ajax part of computers list"));
    $page->setFile("modules/base/computers/ajaxComputersList.php");
    $page->setOptions(array("visible"=>False, "AJAX" =>True));
    $submod->addPage($page);

    $mod->addSubmod($submod);
}


/**
 * ACL properties
 */

$mod->addACL("jpegPhoto",_("User photo"));
$mod->addACL("nlogin",_("User login"));
$mod->addACL("name", _("User name"));
$mod->addACL("groups",_("User groups"));
$mod->addACL("firstname",_("User firstname"));
$mod->addACL("homeDir",_("User home directory"));
$mod->addACL("loginShell",_("Login shell"));

$mod->addACL("title",_("User title"));
$mod->addACL("mail",_("Mail address"));
$mod->addACL("telephoneNumber",_("Telephone number"));
$mod->addACL("mobile",_("Mobile phone number"));
$mod->addACL("facsimileTelephoneNumber",_("Fax number"));
$mod->addACL("homePhone",_("Home phone number"));
$mod->addACL("cn",_("Common name"));
$mod->addACL("displayName",_("Preferred name to be used"));

$mod->addACL("pass",_("Password"));
$mod->addACL("confpass",_("Confirm your password"));
$mod->addACL("isBaseDesactive",_("Enable/Disable user account"));

$MMCApp =& MMCApp::getInstance();
$MMCApp->addModule($mod);

?>
