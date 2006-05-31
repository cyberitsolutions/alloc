#!/bin/bash

# Directory of this file
DIR="${0%/*}/"

v="$(bzr revno)"
v=$((${v}+1))

echo "1.2.${v}" > ${DIR}alloc_version


