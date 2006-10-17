#!/bin/sh
#
# This script will trigger allocs emailed reminder system.
#
wget -q -O /dev/null CONFIG_VAR_ALLOC_WEB_URL_PREFIXnotification/sendReminders.php

