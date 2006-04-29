#!/bin/bash



cd /cyber/devel/bzr/alloc

bzr push ~/allocPSA
cd ~
tar czvf allocPSA.tgz allocPSA
mv allocPSA.tgz html/


