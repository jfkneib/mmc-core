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
require("modules/base/includes/groups.inc.php");

if (isset($_POST["bconfirm"])) {
    $group = $_POST["groupname"];
    callPluginFunction("delGroup", array($group), True);
    if (!isXMLRPCError()) {
        new NotifyWidgetSuccess(sprintf(_("Group %s deleted"), $group));
        header("Location: " . urlStrRedirect("base/groups/index"));
    }
} else {
    $group = urldecode($_GET["group"]);
}
?>

<h2><?= _("Delete group"); ?> <?php echo $group; ?></h2>

<p>
<?= _("You will delete group"); ?> <strong><?php echo $group; ?></strong>.
</p>
<p>
<?= _("This group will be deleted even though it is not empty"); ?>
</p>

<form action="main.php?module=base&submod=groups&action=delete" method="post">
<input type="hidden" name="groupname" value="<?php echo $group; ?>" />
<input type="submit" name="bconfirm" class="btnPrimary" value="<?= _("Delete group"); ?>" />
<input type="submit" name="bback" class="btnSecondary" value="<?= _("Cancel"); ?>" onclick="new Effect.Fade('popup'); return false;" />
</form>
