#!/bin/env python
#
# Usage: agpl_header.py filename1 filename2 filenameN..
#
#

import os, sys

# Remove this many lines from the top of the file ${1}
num_of_lines_to_remove = 23


d = os.path.dirname(sys.argv[0])
header_file = d + "/agpl_header"

# Read the gpl file into a string
try:
  fd = open(header_file)
  gpl = fd.read();
  fd.close();
except IOError:
  print 'Unable to open: '+header_file
  sys.exit(0)


# Loop through all command line arguments, ie each file
for arg in sys.argv:
  # If we've been passed a file and it exists
  if os.path.isfile(arg):
    
    file = arg

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
    print 'File not found: '+ arg
