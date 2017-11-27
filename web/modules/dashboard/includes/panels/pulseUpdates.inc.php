<?php
/*
 * (c) 2016 siveo, http://www.siveo.net/
 *
 * This file is part of Management Console (MMC).
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

require_once("modules/dashboard/includes/panel.class.php");
require_once("modules/xmppmaster/includes/xmlrpc.php");
require_once("modules/base/includes/computers.inc.php");
require_once("modules/update/includes/xmlrpc.inc.php");

$options = array(
    "class" => "PulseUpdates",
    "id" => "pulseUpdates",
    "title" => _T("Pulse Updates", "dashboard"),
    "enable" => true,
);

class PulseUpdates extends Panel {

    function display_content() {
        $updates = get_updates(array('filters'=>array('status'=>0),'hide_installed_update'=>true))[data];
        $update_count = count($updates);
        if ($updates === FALSE){

        // Update error occured
        printf('<center style="color:red;font-weight:bold">%s</center>', _T('An error occured while fetching updates'));
        } else {

            $view_updates_text = _T('View updates', 'update');

            print '<center>';

            if ($update_count == 0)
                printf('<p><strong>%s</strong></p>', _T('No updates available.', 'update'));
            else{
                printf('<p><strong>%d %s</strong></p>', $update_count, _T('updates available.', 'update'));

                print <<<EOS
                <a title="View updates" class="btnSecondary"
                    href="main.php?module=update&amp;submod=update&amp;action=index"
                    >$view_updates_text</a><br/><br/>
EOS;
            }
	}
    }
?>
