# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2010 Mandriva, http://www.mandriva.com
#
# $Id$
#
# This file is part of Mandriva Management Console (MMC).
#
# MMC is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# MMC is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MMC.  If not, see <http://www.gnu.org/licenses/>.

"""
MMC agent password policy plugin

This plugin allows to manage LDAP password policy related attributes.
See: http://tools.ietf.org/html/draft-behera-ldap-password-policy

The device object class will be used as the structural class holding the
pwdPolicy auxiliary object class (as done in OpenLDAP ppolicy smoke test).
"""

import copy
import ldap
import logging
import time
import calendar
from ldap import modlist
from mmc.plugins.base import ldapUserGroupControl
from mmc.support.config import PluginConfig
from mmc.core.audit import AuditFactory as AF
from mmc.plugins.ppolicy.audit import AT, AA, PLUGIN_NAME


VERSION = "2.3.2"
APIVERSION = "0:0:0"
REVISION = int("$Rev$".split(':')[1].strip(' $'))
       
def getVersion(): return VERSION
def getApiVersion(): return APIVERSION
def getRevision(): return REVISION


def activate():
    ldapObj = ldapUserGroupControl()
    logger = logging.getLogger()

    config = PPolicyConfig("ppolicy")
    if config.disabled:
        logger.warning("Plugin ppolicy: disabled by configuration.")
        return False
    
    ppolicySchema = ['pwdPolicy', 'device']

    for objectClass in ppolicySchema:
        schema = ldapObj.getSchema(objectClass)
        if not len(schema):
            logger.error("LDAP Password Policy schema is not included in LDAP directory: %s objectClass is not available" % objectClass);
            return False

    # Register default password policy into the LDAP if it does not exist
    PPolicy().installPPolicy()
    
    return True


class PPolicyConfig(PluginConfig):

    """
    Class for objects that read the ppolicy plugin configuration file.
    """

    def readConf(self):
        """
        Read the configuration file using the ConfigParser API.
        """
        PluginConfig.readConf(self)
        # Read LDAP Password Policy configuration
        self.ppolicyAttributes = {}
        self.ppolicydn = self.get('ppolicy', 'ppolicyDN')
        self.ppolicydefault = self.get('ppolicy', 'ppolicyDefault')
        self.ppolicydefaultdn = "cn=" + self.ppolicydefault + "," + self.ppolicydn
        for attribute in self.items('ppolicyattributes'):
            if attribute[1] == 'True':
                self.ppolicyAttributes[attribute[0]] = True
            elif attribute[1] == 'False':
                self.ppolicyAttributes[attribute[0]] = False
            else:
                self.ppolicyAttributes[attribute[0]] = attribute[1]
        
        #if self.has_section("user"):
        #    if self.has_option('user', 'attribute'):
        #        self.userAttributes = self.get('user', 'attribute').split('|')


class PPolicy(ldapUserGroupControl):

    """
    Class for objects that manages the default LDAP password policy.
    """
 
    def __init__(self, conffile = None):
        ldapUserGroupControl.__init__(self, conffile)
        self.configPPolicy = PPolicyConfig("ppolicy", conffile)
                   
    def checkPPolicy(self):
        '''
        Check the presence of Password Policy
        
        @returns: True if it exists
        @rtype: bool
        '''
        try:
            self.l.search_s(self.configPPolicy.ppolicydefaultdn, ldap.SCOPE_BASE)
            return True
        except ldap.NO_SUCH_OBJECT:
            return False
        
    def installPPolicy(self):
        """
        Set the default password policies in LDAP if not available.
        """
        if not self.checkPPolicy():
            try:
                head, path = self.configPPolicy.ppolicydn.split(",", 1)
                ouName = head.split("=")[1]
                self.addOu(ouName, path)
                self.logger.info("Created OU " + self.configPPolicy.ppolicydn)
            except ldap.ALREADY_EXISTS:
                pass
                
            attrs = {}
            attrs['objectClass'] = ['pwdPolicy', 'device']
            attrs['cn'] = self.configPPolicy.ppolicydefault
            for k in self.configPPolicy.ppolicyAttributes :
                if type(self.configPPolicy.ppolicyAttributes[k]) == bool:
                    self.configPPolicy.ppolicyAttributes[k] = str(self.configPPolicy.ppolicyAttributes[k]).upper()
		if k == 'pwdCheckModule'.lower():
                    attrs['objectClass'].append('pwdPolicyChecker')
                attrs[k] = str(self.configPPolicy.ppolicyAttributes[k])
            attributes = modlist.addModlist(attrs)
            self.l.add_s(self.configPPolicy.ppolicydefaultdn, attributes)
            self.logger.info("Default password policy registered at: %s" % self.configPPolicy.ppolicydefaultdn)
        
    def getAttribute(self, nameattribute = None):
        """
        Get the given attribute value of the default password policies.

        @param nameattribute: LDAP attribute name
        @type nameattribute: str

        @returns: the attribute value
        @rtype: str
        """
        try:
            result = (self.l.search_s(self.configPPolicy.ppolicydefaultdn, ldap.SCOPE_BASE))[0][1]
            if nameattribute == None:
                return result
            elif nameattribute in result:
                return result[nameattribute]
            else:
                return None
        except KeyError:
            return None

    def setAttribute(self, nameattribute, value):
        """
        Set value to the given attribute.

        @param nameattribute: LDAP attribute name
        @type nameattribute: str

        @param value: LDAP attribute value
        @type value: str
        """
        r = AF().log(PLUGIN_NAME, AA.PPOLICY_MOD_ATTR, [(self.configPPolicy.ppolicydefaultdn, AT.PPOLICY), (nameattribute, AT.ATTRIBUTE)], value)
        if value != None:
            if type(value) == bool:
                value = str(value).upper()
            elif type(value) == int:
                value = str(value)
        try:
            self.l.modify_s(self.configPPolicy.ppolicydefaultdn, [(ldap.MOD_REPLACE,nameattribute,value)])
        except ldap.UNDEFINED_TYPE:
            logging.getLogger().error("Attribute %s isn't defined on ldap" % nameattribute)
        except ldap.INVALID_SYNTAX:
            logging.getLogger().error("Invalid Syntax from the attribute value of %s on ldap" % nameattribute)
        r.commit()
        
    def getDefaultAttributes (self):
        """
        Returns the list of LDAP password policies attributes.

        @returns: A list of attributes name
        @rtype: list
        """
        ret = []
        for k in self.configPPolicy.ppolicyAttributes:
            ret.append(k)
        return ret
    
    def setDefaultConfigAttributes (self):
        """
        Set all the password policies attributes to the value specified in the
        plugin configuration file.
        """
        for attribute in self.configPPolicy.ppolicyAttributes:
            self.setAttribute(attribute, self.configPPolicy.ppolicyAttributes[attribute])


class UserPPolicy(ldapUserGroupControl):

    """
    Class for objects that manage user password policies attributes.
    """
    
    def __init__(self, uid, conffile = None):
        """
        Class constructor.

        @param uid: user id of the LDAP user entry to manage
        @type uid: str
        """
        ldapUserGroupControl.__init__(self, conffile)
        self.configPPolicy = PPolicyConfig("ppolicy", conffile)
        self.userUid = uid
        self.dn = 'uid=' + uid + ',' + self.baseUsersDN
    
    def getPPolicyAttribute(self, name = None):
        """
        Get value of the given LDAP attribute.

        @param name: LDAP attribute name
        @type name: str

        @returns: the attribute value
        @rtype: str
        """
        result = self.getDetailedUser(self.userUid)
        if name == None:
            ret = result
        elif name in result:
            ret = result[name]
        else:
            ret = None
        return ret
        
    def setPPolicyAttribute(self, nameattribute, value):
        """
        Set the value of the given LDAP attribute.

        @param nameattribute: LDAP attribute name
        @type nameattribute: str

        @param value: LDAP attribute value
        @type value: str
        """
        r = AF().log(PLUGIN_NAME, AA.PPOLICY_MOD_USER_ATTR, [(self.dn, AT.USER), (nameattribute, AT.ATTRIBUTE)], value)
        if value != None:
            if type(value) == bool:
                value = str(value).upper()
            elif type(value) == int:
                value = str(value)
        try:
            self.l.modify_s(self.dn, [(ldap.MOD_REPLACE,nameattribute,value)])
        except ldap.UNDEFINED_TYPE:
            logging.getLogger().error("Attribute %s isn't defined on ldap" % nameattribute)
        except ldap.INVALID_SYNTAX:
            logging.getLogger().error("Invalid Syntax from the attribute value of %s on ldap" % nameattribute)
        r.commit()

    def hasPPolicyObjectClass(self):
        """
        Returns true if the user owns the pwdPolicy objectClass.

        @returns: return True if the user owns the pwdPolicy objectClass.
        @rtype: boolean
        """
        return "pwdPolicy" in self.getPPolicyAttribute()["objectClass"]
        
    def addPPolicyObjectClass(self):
        """
        Add the pwdPolicy objectClass to the current user.

        The pwdAttribute is also set to the value 'userPassword'.
        """
        if not self.hasPPolicyObjectClass():
            r = AF().log(PLUGIN_NAME, AA.PPOLICY_ADD_USER_PPOLICY_ATTR, [(self.dn, AT.USER)])
            # Get current user entry
            s = self.l.search_s(self.dn, ldap.SCOPE_BASE)
            c, old = s[0]

            new = copy.deepcopy(old)

            if not "pwdPolicy" in new["objectClass"]:
                new["objectClass"].append("pwdPolicy")
                new["pwdAttribute"] = "userPassword"

            # Update LDAP
            modlist = ldap.modlist.modifyModlist(old, new)
            self.l.modify_s(self.dn, modlist)
            r.commit()

    def isAccountLocked(self):
        """
        Check if the user account is locked.
        
        @returns: -1 if the user account has been locked permanently, 0 if not, else the lock timestamp
        @rtype: int
        """
        self.getDetailedUser(self.userUid, operational = True)
        return

    def isAccountInGraceLogin(self):
        """
        @returns: -1 if the user account in not in grace login, else returns the number of remaining grace logins. 0 means the user can no more bind to the LDAP.
        @rtype int:
        """
        ret = -1
        user = self.getUserEntry(self.userUid, operational = True)
        if 'pwdGraceUseTime' in user:
            count = len(user['pwdGraceUseTime'])
            if 'pwdGraceAuthNLimit' in user:
                ppolicygracelimit = user['pwdGraceAuthNLimit']
            else:
                ppolicygracelimit = PPolicy().getAttribute('pwdGraceAuthNLimit')
            if ppolicygracelimit:
                ret = int(ppolicygracelimit[0]) - count
        return ret

    def isPasswordExpired(self):
        """
        @returns: True if the password is expired
        @rtype: bool
        """
        user = self.getUserEntry(self.userUid, operational = True)
        ret = False
        if 'pwdChangedTime' in user:
            pwdChangedTime = user['pwdChangedTime'][0]
            if 'pwdMaxAge' in user:
                pwdMaxAge = user['pwdMaxAge'][0]
            else:
                pwdMaxAge = PPolicy().getAttribute('pwdMaxAge')[0]
            last = calendar.timegm(time.strptime(pwdChangedTime[:-1], '%Y%m%d%H%M%S'))
            ret = (time.time() - last) > int(pwdMaxAge)
        return ret

# XML-RPC methods

# for default PPolicy management
def checkPPolicy():
    return PPolicy().checkPPolicy()
    
def installPPolicy():
    return PPolicy().installPPolicy()
    
def getPPolicyAttribute(nameAttribute):
    return PPolicy().getAttribute(nameAttribute)
    
def getAllPPolicyAttributes ():
    return PPolicy().getAttribute()
    
def setPPolicyAttribute (nameAttribute, value):
    if value == '': value = None
    PPolicy().setAttribute(nameAttribute, value)
    
def getDefaultPPolicyAttributes ():
    return PPolicy().getDefaultAttributes()
    
def setPPolicyDefaultConfigAttributes ():
    return PPolicy().setDefaultConfigAttributes()

# for user PPolicy management
def hasPPolicyObjectClass(uid):
    return UserPPolicy(uid).hasPPolicyObjectClass()
    
def addPPolicyObjectClass(uid):
    UserPPolicy(uid).addPPolicyObjectClass()    
    
def removePPolicyObjectClass(uid):
    UserPPolicy(uid).removeUserObjectClass(uid, 'pwdPolicy')
    
def getUserPPolicyAttribut(uid, nameAttribut):
    if nameAttribut == '': nameAttribut = None
    return UserPPolicy(uid).getPPolicyAttribute(nameAttribut)
    
def setUserPPolicyAttribut(uid, nameAttribut, value):
    if value == '': value = None
    UserPPolicy(uid).setPPolicyAttribute(nameAttribut, value)

def isAccountLocked(uid):
    return UserPPolicy(uid).isAccountLocked()

def isAccountInGraceLogin(uid):
    return UserPPolicy(uid).isAccountInGraceLogin()

def isPasswordExpired(uid):
    return UserPPolicy(uid).isPasswordExpired()


if __name__ == "__main__":
    #print ldapUserGroupControl().getDetailedUserIntAttr("user1")
    print isPasswordExpired("testpass")
