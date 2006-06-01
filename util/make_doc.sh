#!/bin/bash -x


DIR="${0%/*}/"

DIR_FULL=${PWD}/${DIR}
DIR_FULL="${DIR_FULL/\.\//}../"
    

dest="/home/${USER}/alloc_doc_export/"

[ -d "${dest}" ] && rm -rf ${dest}

cd /cyber/devel/bzr/alloc_doc/ && bzr export --format dir ${dest}

cd ${dest}

# So my python install doesn't clobber the local one.
PATH=/usr/bin/:${PATH}

make help.html
mv help.html ${DIR_FULL}/help/
mv help.css ${DIR_FULL}/help/
mv images ${DIR_FULL}/help/

\rm -rf ${dest}



