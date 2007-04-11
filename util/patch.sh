#!/bin/bash
#
# 
#  Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
#  
#  This file is part of allocPSA <info@cyber.com.au>.
#  
#  allocPSA is free software; you can redistribute it and/or modify it under the
#  terms of the GNU General Public License as published by the Free Software
#  Foundation; either version 2 of the License, or (at your option) any later
#  version.
#  
#  allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
#  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
#  A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License along with
#  allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
#  St, Fifth Floor, Boston, MA 02110-1301 USA


# 
# To update database structure for db's which already have data in them.
#
# This script has been re-written so that livealloc can still use it
#

set -x


# The location of the alloc install, default to localhost for livealloc
if [ -n "${1}" ]; then
  alloc="${1}"
else
  alloc="http://localhost/alloc/"
fi


# Append a forward-slash if necessary
[ "${alloc:(-1):1}" != "/" ] && alloc="${alloc}/"

# This upgrade script will fetch bash commands to be run as ROOT
str=$(wget -q -O - ${alloc}installation/patch_1_2_256_to_1_3_497.php?return_commands=1)

# Run commands
eval "${str}"

# Perform regular db upgrades
wget -q -O /dev/null ${alloc}installation/patch.php?apply_patches=true


