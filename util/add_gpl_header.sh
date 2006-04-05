#!/bin/bash
#
# Remove any number of lines from the top of a file and replace them with the contents of 
#
#
#


# Remove this many lines from the top of the file ${1}
num_of_lines_to_remove=1
header_file="./gpl_header"


# If the first arg passed is a file
if [ ! -z "${1}" ] && [ -f "${1}" ]; then

  # echo
  echo -n "Examining: ${1} ... "
 
  # Remove old temp.txt 
  [ -f "./temp.txt" ] && rm -f ./temp.txt;

  # cat header into temp file
  cat ${header_file} > ./temp.txt

  # Counter 
  i=0

  # Read in lines from a file 
  {
  while read line; do
    let i++
    if [ ${i} -gt ${num_of_lines_to_remove} ]; then
      echo "${line}" >> ./temp.txt  
    fi
  done;
  } < ${1}

  # Copy file over onto original
  cp -f ./temp.txt ${1} && echo "Yay."
fi
