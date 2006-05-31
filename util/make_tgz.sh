#!/bin/bash


DIR="${0%/*}/"

revno=$(bzr revno)

d="/home/${USER}/html"
f1="allocPSA-1.2.${revno}"
f2="allocPSA_doc-1.2.${revno}"
dir1="${d}/${f1}"
dir2="${d}/${f2}"

[ -d "${dir1}" ] && \rm -rf ${dir1}

bzr push
cd /cyber/devel/bzr/alloc/ && bzr export --format dir ${dir1}
cd /cyber/devel/bzr/alloc_doc/ && bzr export --format dir ${dir2}

cd ${dir2}

PATH=/usr/bin/:${PATH}

make help.html
mv help.html ${dir1}/help/
mv help.css ${dir1}/help/
mv images ${dir1}/help/

\rm -rf ${dir2}


cd ${d}

tar czvf ${f1}.tgz ${f1}

if [ -f "${f1}.tgz" ]; then
  echo "Created: ${f1}.tgz"
  rm -rf ${f1}
else 
  echo "Problem creating: ${f1}.tgz"
fi


