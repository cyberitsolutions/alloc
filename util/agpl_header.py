#!/bin/env python
"""Put the agpl header onto files."""

import os, sys

def main():
  """Usage: agpl_header.py filename1 filename2 filenameN."""

  # Remove this many lines from the top of the file ${1}
  numlines = 23


  d = os.path.dirname(sys.argv[0])
  header_file = d + "/agpl_header"

  # Read the gpl file into a string
  try:
    fd = open(header_file)
    gpl = fd.read()
    fd.close()
  except IOError:
    print 'Unable to open: '+header_file
    sys.exit(0)


  # Loop through all command line arguments, ie each file
  for arg in sys.argv:
    # If we've been passed a file and it exists
    if os.path.isfile(arg):
      
      f = arg

      print 'Examining: '+f

      # Try opening the passed file
      try:
        fd = open(f)
      except IOError:
        print 'Unable to open: '+f
        sys.exit(0)

      # Turn the file into a list
      lines = fd.readlines()
      fd.close()
      i = 0

      # Remove the first line of the list for "numlines" many times
      while (i<numlines):
        del lines[0]
        i += 1

      # Open the file again for writing
      try:
        fd = open(f,'w')
      except IOError:
        print 'Unable to open for writing: '+f
        sys.exit(0)

      # Stick the gpl at the top and write it
      s = gpl
      s += ''.join(lines) 
      fd.write(s) 

    else:
      print 'File not found: '+ arg

main()
