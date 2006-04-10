#!/bin/env python

import ConfigParser, os, sys

# Read the style.ini file with all the config key/values
config = ConfigParser.ConfigParser()

d = os.path.dirname(sys.argv[0])+'/'
config.read([d+'../style.ini'])

# Get a list of sections from the config file
sections = config.sections()

# Convert the style template into a string
fd = open(d+'../style.css')
str_orig = fd.read();
fd.close();

# Get list of default options from the style_classic section
default_options = config.options('style_classic')

# Build up a defaults dictionary
dict_default = {}
for option in default_options:
  dict_default[option] = config.get('style_classic',option)


# Loop through each section
for section in sections:
  dict = {}
  str = str_orig

  # Try overriding the defaults stored in dict with the section value
  for option in default_options:
    try:
      dict[option] = config.get(section,option)
    except ConfigParser.NoOptionError:
      dict[option] = dict_default[option]
  
  # Do a search and replace for each key/value in the template string 
  for k, v in dict.items():
    str = str.replace('('+k.upper()+')', v)
  
  # Write it out to a file
  fd = open(d+'../stylesheets/'+section+'.css','w')
  fd.write(str) 
  fd.close()








