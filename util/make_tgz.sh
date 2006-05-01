#!/bin/bash

revno=$(bzr revno)
bzr push
cd /cyber/devel/bzr/alloc/ && bzr push ~/allocPSA
cd ~
tar czvf allocPSA.${revno}.tgz allocPSA
\rm -rf allocPSA
mv allocPSA.${revno}.tgz html/



