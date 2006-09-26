#!/bin/bash


DIR="${0%/*}/"

d="/home/${USER}/html"

version="$(cat ${DIR}alloc_version)"
f1="allocPSA-${version}"

dir1="${d}/${f1}"

[ -d "${dir1}" ] && \rm -rf ${dir1}

bzr push
cd /cyber/devel/bzr/alloc/ && bzr export --format dir ${dir1}


PATH=/usr/bin/:${PATH}

cd ${dir1}/util/
./make_doc.sh

\rm -rf ${dir2}


cd ${d}

tar czvf ${f1}.tgz ${f1}

if [ -f "${f1}.tgz" ]; then
  echo "Created: ${d}/${f1}.tgz"
  rm -rf ${f1}
else 
  echo "Problem creating: ${f1}.tgz"
fi


