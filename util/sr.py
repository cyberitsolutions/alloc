#!/bin/env python

import os, sys, optparse, re, string

# options to 
parser = optparse.OptionParser(usage="%prog -r 'hey' -s 'ho' file1 file2...fileN")
parser.add_option("-q", "--quiet",  action="store_false", dest="verbose", default=True, help="Don't print status messages to stdout")
parser.add_option("-r", "--regex",  action="store",       dest="regex",                 help="The perl compatible regex pattern")
parser.add_option("-s", "--string", action="store",       dest="string",                help="The replace string")
parser.add_option("-b", "--batch",  action="store_true",  dest="batch",   default=False,help="Do search and replace non-interactively")

(options, files) = parser.parse_args()

if not files or len(files)<1:
  print "No list of files found."
  parser.print_usage()
  sys.exit();
elif not options.regex:
  print "No regex found."
  parser.print_usage()
  sys.exit();
elif not options.string:
  print "No replacement string found."
  parser.print_usage()
  sys.exit();

for file in files:
  if not os.path.isfile(file):
    print "No file exists: "+file
    parser.print_usage()
    sys.exit();

# compile regexp
cregex=re.compile(options.regex)


  
for file in files:
  # open file for read  
  lines = open(file,'r').readlines()

  # initialize the replace flag
  replace_flag=0
  exit_after_write=0

  # intialize the list counter
  listindex = -1

  # search and replace in current file printing to the user changed lines
  for line in lines:
    # increment the list counter
    listindex = listindex + 1

    if cregex.search(line) and not exit_after_write:
      f=re.sub(options.regex, options.string, line)
      # print the current filename, the old string and the new string
      print '\n' + file + ':' +repr(listindex+1)
      print '- ' + line ,
      if line[-1:]!='\n': print '\n' ,
      print '+ ' + f ,
      if f[-1:]!='\n': print '\n' ,  

      # if substitution is step by step
      if not options.batch:
        question = raw_input('write(Y), skip (n), quit (q) ? ')
        question = string.lower(question)
      else:
        question = 'y'


      if question == 'q':
        exit_after_write = 1

      elif question == 'n':
        pass

      else:
        lines[listindex] = f
        replace_flag=1
    
   
  # if some text was replaced
  # overwrite the original file
  if replace_flag==1:
    # open the file for writting  
    write_file=open(file,'w') 

    # overwrite the file  
    for line in lines:
      write_file.write(line)

    # close the file
    write_file.close()   
      
  if exit_after_write == 1:
    sys.exit('\ninterrupted.\n')



