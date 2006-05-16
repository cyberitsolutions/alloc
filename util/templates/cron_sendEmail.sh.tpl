#!/bin/sh
#
# This script will trigger allocs daily task email summaries.
#

# path to cron and log files
PREFIX="CONFIG_VAR_ALLOC_LOG_DIR"

# wget the php script
wget -q -O ${PREFIX}sendEmail.log -P ${PREFIX} CONFIG_VAR_ALLOC_WEB_URL_PREFIXperson/sendEmail.php

