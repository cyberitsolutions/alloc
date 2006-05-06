#!/bin/bash

revno=$(bzr revno)
file="/home/${USER}/html/allocPSA-1.2.${revno}.tgz"

bzr push
cd /cyber/devel/bzr/alloc/ && bzr export --format tgz ${file}

if [ -f "${file}" ]; then
  echo "Created: ${file}"
else 
  echo "Problem creating: ${file}"
fi

