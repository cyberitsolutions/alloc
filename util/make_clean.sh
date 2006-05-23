#!/bin/sh
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
#
#  Script to setup database, website permissions, cronjobs and generate alloc.inc
#



# Directory of this file
DIR="${0%/*}/"

# Source functions
. ${DIR}functions.sh


e "Removing Executables"

if [ "0" != "$(id -u)" ]; then
  die "Please run this script as user root."
fi

# Make the alloc.inc file
e "Removing alloc.inc"
run "rm -f ${DIR}alloc.inc"

if [ -f "${DIR}alloc.inc" ]; then 
  e_failed "Could not remove alloc.inc"; 
fi


e "Removing cron_allocBackup.sh"
run "rm -f ${DIR}cron_allocBackup.sh"

if [ -f "${DIR}cron_allocBackup.sh" ]; then 
  e_failed "Could not remove cron_allocBackup.sh"; 
fi


e "Removing cron_checkRepeatExpenses.sh"
run "rm -f ${DIR}cron_checkRepeatExpenses.sh"

if [ -f "${DIR}cron_checkRepeatExpenses.sh" ]; then 
  e_failed "Could not remove cron_checkRepeatExpenses.sh"; 
fi


e "Removing cron_sendEmail.sh"
run "rm -f ${DIR}cron_sendEmail.sh"

if [ -f "${DIR}cron_sendEmail.sh" ]; then 
  e_failed "Could not remove cron_sendEmail.sh"; 
fi


e "Removing cron_sendReminders.sh"
run "rm -f ${DIR}cron_sendReminders.sh"

if [ -f "${DIR}cron_sendReminders.sh" ]; then 
  e_failed "Could not remove cron_sendReminders.sh"; 
fi


e "Removing patch.sh"
run "rm -f ${DIR}patch.sh"

if [ -f "${DIR}patch.sh" ]; then 
  e_failed "Could not remove patch.sh"; 
fi


e "Finished clean."
