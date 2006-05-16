#!/bin/sh
#
# This script will trigger the php script which checks for repeating allocexpenses.
#

# path to cron and log files
PREFIX="CONFIG_VAR_ALLOC_LOG_DIR"

# wget the php script
wget -q -O ${PREFIX}checkRepeatingExpenses.log -P ${PREFIX} CONFIG_VAR_ALLOC_WEB_URL_PREFIXfinance/checkRepeat.php

