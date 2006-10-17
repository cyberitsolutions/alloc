#!/bin/sh
#
# This script will trigger the php script which checks for repeating allocexpenses.
#
wget -q -O /dev/null CONFIG_VAR_ALLOC_WEB_URL_PREFIXfinance/checkRepeat.php
