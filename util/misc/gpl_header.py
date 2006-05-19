#!/bin/env python

import os, sys

# Remove this many lines from the top of the file ${1}
num_of_lines_to_remove = 23


d = os.path.dirname(sys.argv[0])
header_file = d + "/gpl_header"

# Read the gpl file into a string
try:
  fd = open(header_file)
  gpl = fd.read();
  fd.close();
except IOError:
  print 'Unable to open: '+header_file
  sys.exit(0)



# If we've been passed a file and it exists
if (len(sys.argv) > 1 and os.path.isfile(sys.argv[1])):
  
  file = sys.argv[1]

  print 'Examining: '+file

  # Try opening the passed file
  try:
    fd = open(file)
  except IOError:
    print 'Unable to open: '+file
    sys.exit(0)

  # Turn the file into a list
  lines = fd.readlines()
  fd.close()
  i = 0

  # Remove the first line of the list for "num_of_lines_to_remove" many times
  while (i<num_of_lines_to_remove):
    del lines[0]
    i+= 1

  # Open the file again for writing
  try:
    fd = open(file,'w')
  except IOError:
    print 'Unable to open for writing: '+file
    sys.exit(0)

  # Stick the gpl at the top and write it
  str = gpl
  str += ''.join(lines) 
  fd.write(str) 

else:
  print 'File not found: '+ sys.argv[1]
