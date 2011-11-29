#!/usr/bin/env python
"""Compiled the stylesheets in css/src/"""

import ConfigParser, os, sys


def main():
  """Rebuild CSS"""

  # Read the style.ini file with all the config key/values
  config = ConfigParser.ConfigParser()

  # Current directory trickyness
  d = os.path.dirname(sys.argv[0])+'/'


  # Convert the style template into a string
  fd = open(d+'../css/src/style.tpl')
  str_orig = fd.read()
  fd.close()

  # List of style_something.ini files in ../styles/ 
  files = os.listdir(d+'../css/src/')

  # Loop through style_*.ini files
  for item in files:

    if item.endswith('.ini'):

      # Parse each style_something.ini file
      config.read([d+'../css/src/'+item])

      # Get a list of sections from the config file
      sections = config.sections()


      # Loop through each section
      for section in sections:
        dictops = {}
        s = str_orig


        # Get all "keys:" from the ini file
        options = config.options(section)

        # Load up a dict 
        for option in options:
          dictops[option] = config.get(section, option)
        
        # Do a search and replace for each key/value in the template string 
        for k, v in dictops.items():
          s = s.replace('('+k.upper()+')', v)

        # Write it out to a file
        fd = open(d+'../css/'+section+'.css','w')
        fd.write(s) 
        fd.close()


main()
