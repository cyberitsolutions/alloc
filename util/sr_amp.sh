#!/bin/bash

for i in `grep -rsi -E "]\.[[:space:]]?\"&"  * | sed -e 's/:.*//'`; do 

  echo ${i}

  str1="${i} ${str}"
  
done;


 #| sed -e 's/:.*//'`
for i in `grep -rsi -E "\{url_alloc_[[:alnum:]]+\}&" * | sed -e 's/:.*//'` ; do 

  echo ${i}

  str="${i} ${str}"
  
done;

#vim $str
