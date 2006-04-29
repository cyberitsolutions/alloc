#!/bin/bash


bzr push
cd /cyber/devel/bzr/alloc/ && bzr push ~/allocPSA
cd ~
tar czvf allocPSA.tgz allocPSA
\rm -rf allocPSA
mv allocPSA.tgz html/



