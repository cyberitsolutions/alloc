#!/usr/bin/env python

import ConfigParser, os, sys


# Read the style.ini file with all the config key/values
config = ConfigParser.ConfigParser()

# Current directory trickyness
d = os.path.dirname(sys.argv[0])+'/'


# Convert the style template into a string
fd = open(d+'../styles/style.tpl')
str_orig = fd.read();
fd.close();

# List of style_something.ini files in ../styles/ 
files = os.listdir(d+'../styles/')

# Loop through style_*.ini files
for item in files:

  if item.endswith('.ini'):

    # Parse each style_something.ini file
    config.read([d+'../styles/'+item])

    # Get a list of sections from the config file
    sections = config.sections()


    # Loop through each section
    for section in sections:
      filename_suffix = 0;
      dict = {}
      str = str_orig


      # Get all "keys:" from the ini file
      options = config.options(section)

      # Load up a dict 
      for option in options:
          dict[option] = config.get(section,option)
      
      # Do a search and replace for each key/value in the template string 
      for k, v in dict.items():
        str = str.replace('('+k.upper()+')', v)

      # Write it out to a file
      fd = open(d+'../css/'+section+'.css','w')
      fd.write(str) 
      fd.close()


