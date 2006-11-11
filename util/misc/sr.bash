#!/bin/bash

file=${1}

if [ "${file}" != "" ] && [ "${file}" != "." ] && [ "${file}" != ".." ]; then

  # Changes {hey} into {$hey} and {/optional} into {$/optional}
  sed -i -e 's/{\([^:}]*}\)/{$\1/g' ${file}

  # Change {$/optional} into {/}
  sed -i -e 's/{$\/optional}/{\/}/g' ${file}


  # Change {optional:do_something} into {if check_optional_do_something() }
  sed -i -e 's/{optional:\([^}]*\)}/{if check_optional_\1()}/g' ${file}


  # Change {:show_projects projectM.tpl} into {show_projects("projectM.tpl")}
  sed -i -e 's/{:\([^ }]*\)\(\s*\)\([^}]*\)}/{\1("\3")}/g' ${file}

  # Change {blah("")} into {blah()}
  sed -i -e 's/("")}/()}/g' ${file}

fi;



# str_replace

# {var}                         -->  {$var}                          -->  <?php echo stripslashes($TPL["var"]); ?>      
# {:show_projects}              -->  {show_projects()}               -->  <?php show_projects(); ?>
# {:show_projects project.tpl}  -->  {show_projects("project.tpl")}  -->  <?php show_projects("project.tpl"); ?>



##                                    {else                           -->  <?php } else
##                                    <?php } else}                   -->  <?php } else { ?>

##                                    <?php } elseif                  -->  <?php } elseif (
##                                    <?php } else if                 -->  <?php } elseif (
##                                    <?php } elseif (([^}]*)}        -->  <?php } elseif (\1) { ?>


# {optional:hey}                -->  {if check_optional_hey()}        -->  <?php if (check_optional_hey()) { ?>

##                                   {(if|while) ([^}]*)}             -->  <?php \1 (\2) { ?>                  
                                    

#                               -->  {while something}                -->  <?php while (blah) { ?>


# {/optional}                   -->  {/}                              -->  <?php } ?>
# {                             -->  {                                -->  <?php 
# }                             -->  }                                -->  ;?>



# Change {:hey} into <?php hey();?>
#sed -e 's/{:\(\([^ ][^}]\)*\)}/{\1()}/g'


