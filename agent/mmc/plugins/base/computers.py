# -*- coding: utf-8; -*-
#
# (c) 2004-2007 Linbox / Free&ALter Soft, http://linbox.com
# (c) 2007-2008 Mandriva, http://www.mandriva.com/
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
# along with MMC; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

import logging
import ldap
from mmc.support.mmctools import Singleton


class ComputerI:

    def canAddComputer(self):
        """
        Does this module handle addition of computers 
        """
        pass

    def canAssociateComputer2Location(self):
        """
        Does this module handle association between computers and locations
        """
        pass
        
    def addComputer(self, ctx, params):
        """
        Add a new computer
        """
        pass

    def canDelComputer(self):
        """
        Does this module handle removal of computers
        """
        pass

    def delComputer(self, ctx, params):
        """
        Del a computer
        """
        pass


    def getComputer(self, ctx, params):
        """
        Get only one computer
        """
        pass
    
    def getMachineMac(self, ctx, params):
        """
        Get the computers mac adresses
        """
        pass
        
    def getMachineIp(self, ctx, params):
        """
        Get the computers ip addresses
        """
        pass
    
    def getComputerList(self, ctx, params):
        """
        Get computer list
        """
        pass
    
    def getComputerCount(self, ctx, params = None):
        """
        Get the number of computer
        """
        pass

    def getRestrictedComputersListLen(self, ctx, params):
        """
        Get a limited computer list size
        """
        pass
        
    def getRestrictedComputersList(self, ctx, params):
        """
        Get a limited computer list
        """
        pass

    def getComputersListHeaders(self, ctx):
        """
        Get the headers of the computer list
        """
        pass


class ComputerManager(Singleton):

    components = {}
    main = "none"

    def __init__(self):
        Singleton.__init__(self)
        self.logger = logging.getLogger()

    def select(self, name):
        self.logger.info("Selecting computer manager: %s" % name)
        self.main = name

    def getManagerName(self):
        return self.main

    def register(self, name, klass):
        self.logger.debug("Registering computer manager %s / %s" % (name, str(klass)))
        self.components[name] = klass
        
    def validate(self):
        ret = (self.main == "none") or (self.main in self.components)
        if not ret:
            self.logger.error("Selected computer manager '%s' not available" % self.main)
            self.logger.error("Please check that the corresponding plugin was successfully enabled")
        return ret

    def canAddComputer(self):
        klass = self.components[self.main]
        return klass().canAddComputer()

    def canDelComputer(self):
        klass = self.components[self.main]
        return klass().canDelComputer()

    def canAssociateComputer2Location(self):
        klass = self.components[self.main]
        instance = klass()
        if hasattr(instance, 'canAssociateComputer2Location'):
            return instance.canAssociateComputer2Location()
        return False
        
    def addComputer(self, ctx, params):
        for plugin in self.components:
            klass = self.components[plugin]
            instance = klass()
            if klass().canAddComputer():
                try:
                    instance.addComputer(ctx, params)
                except TypeError:
                    instance.addComputer(params)

    def delComputer(self, ctx, params):
        for plugin in self.components:
            klass = self.components[plugin]
            instance = klass()
            if klass().canDelComputer():
                try:
                    instance.delComputer(ctx, params)
                except TypeError:
                    instance.delComputer(params)

    def neededParamsAddComputer(self):
        try:
            klass = self.components[self.main]
            return klass().neededParamsAddComputer()
        except:
            return []

    def getComputer(self, ctx, filt = None):
        self.logger.debug("getComputer %s" % filt)
        klass = self.components[self.main]
        instance = klass()
        return instance.getComputer(ctx, filt)
        
    def getMachineMac(self, ctx, filt = None):
        klass = self.components[self.main]
        instance = klass()
        return instance.getMachineMac(ctx, filt)

    def getMachineIp(self, ctx, filt = None):
        klass = self.components[self.main]
        instance = klass()
        return instance.getMachineIp(ctx, filt)

    def getComputersNetwork(self, ctx, filt = None):
        klass = self.components[self.main]
        instance = klass()
        return instance.getComputersNetwork(ctx, filt)
        
    def getComputersList(self, ctx, filt = None):
        klass = self.components[self.main]
        instance = klass()
        return instance.getComputersList(ctx, filt)
        
    def getComputerCount(self, ctx, filt = None):
        klass = self.components[self.main]
        instance = klass()
        return instance.getComputerCount(ctx, filt)
        
    def getRestrictedComputersListLen(self, ctx, filt = None, advanced = True):
        klass = self.components[self.main]
        instance = klass()
        return instance.getRestrictedComputersListLen(ctx, filt)
    
    def getRestrictedComputersList(self, ctx, min = 0, max = -1, filt = None, advanced = True, justId = False, toH = False):
        min = int(min)
        max = int(max)
        klass = self.components[self.main]
        instance = klass()
        return instance.getRestrictedComputersList(ctx, min, max, filt, advanced, justId, toH)

    def getComputersListHeaders(self, ctx):
        klass = self.components[self.main]
        instance = klass()
        ret = instance.getComputersListHeaders(ctx)
        if ret == None:
            ret = [['cn', 'Computer Name'], ['displayName', 'Description']]
        return ret

