#!/bin/env python

import ConfigParser, os, sys


# Read the style.ini file with all the config key/values
config = ConfigParser.ConfigParser()

# Current directory trickyness
d = os.path.dirname(sys.argv[0])+'/'


# Convert the style template into a string
fd = open(d+'../style.tpl')
str_orig = fd.read();
fd.close();

# List of style_something.ini files in ../styles/ 
files = os.listdir(d+'../styles/')


# Loop through files
for item in files:


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


    # Create matrix of stylesheets, themes vs font sizes 
    for weight in range(-3,7):
      filename_suffix += 1
      str2 = str;

      font_sizes = {}
      font_sizes['H1_FONT_SIZE']                = (16 + weight)
      font_sizes['H2_FONT_SIZE']                = (16 + weight)
      font_sizes['H3_FONT_SIZE']                = (15 + weight)
      font_sizes['TABLE_BOX_TH_FONT_SIZE']      = (13 + weight)
      font_sizes['TABLE_BOX_TH_A_FONT_SIZE']    = (13 + weight)
      font_sizes['TABLE_TOOLBAR_TH_FONT_SIZE']  = (17 + weight)
      font_sizes['TABLE_TOOLBAR_TD_FONT_SIZE']  = (13 + weight)
      font_sizes['DEFAULT_FONT_SIZE']           = (12 + weight)   

    
      for k, v in font_sizes.items():
        str2 = str2.replace('('+k.upper()+')', repr(v))

      
      # Write it out to a file
      fd = open(d+'../css/'+section+'_'+repr(filename_suffix)+'.css','w')
      fd.write(str2) 
      fd.close()








