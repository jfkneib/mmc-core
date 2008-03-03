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
<?

/**
 *  convert an aclString to an aclArray
 */
function createAclArray($aclString) {
    list($acl, $aclattr) = split('/', $aclString);

    /* get pages ACL */
    $arrayMod = split(':', $acl);
    foreach($arrayMod as $items) {
        if (substr_count($items, "#") == 2) {
            list($mod, $submod, $action) = split('#', $items);
            $resArray["acl"][$mod][$submod][$action]["right"] = "on";
        } else if (substr_count($items, "#") == 3) {
            list($mod, $submod, $action, $tab) = split('#', $items);
            $resArray["acltab"][$mod][$submod][$action][$tab]["right"] = "on";
        }
    }

    /* get attribute ACL */
    $arrayAttr=split(':',$aclattr);
    foreach($arrayAttr as $items) {
        list($attrName,$value) = split('=',$items);
        $resArray["aclattr"][$attrName]=$value;
    }
    
    return $resArray;
}

/**
 * convert an acl array to an acl String
 */
function createAclString($arrAcl, $arrAclTab, $arrAclAttr) {
    $res="";
    //fetch all modules in $arrAcl
    foreach ($arrAcl as $modKey => $valKey ){
        if ($arrAcl[$modKey]["right"]) {
            $res.=":$modKey";
        }
        //fetch all submodules in $valKey
        else foreach ($valKey as $submodKey => $submodvalKey ){
            if ($arrAcl[$modKey][$submodKey]["right"]) {
                $res.=":$modKey#$submodKey";
            }

            //fetch all action in
            else foreach ($submodvalKey as $actionKey => $actionvalKey) {
                if ($arrAcl[$modKey][$submodKey][$actionKey]["right"]) {
                    $res.=":$modKey#$submodKey#$actionKey";
                }
            }
        }
    }
    foreach($arrAclTab as $modKey => $valKey ){
        foreach ($valKey as $submodKey => $submodvalKey ){
            foreach ($submodvalKey as $actionKey => $actionvalKey) {
                foreach ($actionvalKey as $tabKey => $tabValue) {
                    if ($arrAclTab[$modKey][$submodKey][$actionKey][$tabKey]["right"]) {
                        $res.=":$modKey#$submodKey#$actionKey#$tabKey";
                    }                    
                }                
            }
        }
    }


    if ($res=='') { $res = ':'; }

    //partit attribut
    $resAttr='';
    foreach ($arrAclAttr as $attr => $value) {
        if (($value=="ro")or($value=="rw")) {
            $resAttr.=":$attr=$value";
        }
    }
    $combineRes = $res."/".$resAttr;

    return $combineRes;
}

function setFormError($name) {
  global $formErrorArray;
  $formErrorArray[$name]=1;
}

function isFormError($name) {
  global $formErrorArray;
  return $formErrorArray[$name];
}

function setExpertMode($value) {
  $_SESSION["expert_mode_var"]=$value;
}

function isExpertMode() {
  return $_SESSION["expert_mode_var"];
}

function displayExpertCss() {
  if ($_SESSION["expert_mode_var"]==1) {
      print ' style="display: inline;"';
    }
    else {
      print ' style="display: none;"';
    }
}
?>