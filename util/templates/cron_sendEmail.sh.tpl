#!/bin/sh
#
# This script will trigger allocs daily task email summaries.
#
wget -q -O /dev/null CONFIG_VAR_ALLOC_WEB_URL_PREFIXperson/sendEmail.php
