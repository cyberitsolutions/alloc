#!/bin/sh
#
# This script will trigger the php script which checks for repeating allocexpenses.
# Alex Lance <alla@cyber.com.au>
#

# path to cron and log files
PREFIX=`dirname $0`"/../logs/"

# wget the php script
wget -q -O ${PREFIX}checkRepeatingExpenses_log.new -P ${PREFIX} http://alloc/finance/checkRepeat.php

