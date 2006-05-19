# Functions to help display messages 
# 

function e_red      { echo -en "\x1b[31;01m${1}\x1b[0m"; }
function e_green    { echo -en "\x1b[32;01m${1}\x1b[0m"; }
function e_yellow   { echo -en "\x1b[33;01m${1}\x1b[0m"; }
function e_blue     { echo -en "\x1b[34;01m${1}\x1b[0m"; }
function e_white_b  { echo -en "\x1b[1;37m${1}\x1b[0m"; }

function e_ok       { e_blue "  ["; e_green "  OK  "; e_blue "] "; echo -e ${1}; }           # Echo [  OK  ] $msg
function e_skip     { e_blue "  ["; e_yellow " SKIP "; e_blue "] "; echo -e ${1}; }          # Echo [ SKIP  ] $msg
function e_failed   { e_blue "  ["; e_red "FAILED"; e_blue "] "; echo -e ${1}; FAILED=1; }   # Echo [FAILED] $msg
function e_dead     { e_blue "  ["; e_red " DEAD "; e_blue "] "; echo -e ${1}; FAILED=1; }   # Echo [ DEAD ] $msg
function e          { e_blue "\n* "; e_white_b "${1}\n"; }
function e_n        { e_blue "\n* "; e_white_b "${1}"; }

function e_good     { e_green "\n* * * "; e_white_b "${1}"; e_green " * * *\n"; }
function e_bad      { e_red "\n* * * ";   e_white_b "${1}"; e_red " * * *\n"; }



# Execute command, catch errors.
#
function run {
  ERR="" # Can't make ERR a local var cause that operation sets the $? var
  ERR="`$1 2>&1`"
  if [ "${?}" -ne 0 ]; then
    e_failed "${1} -> ${ERR}"
    return 1 # This will set the $? variable
  else
    e_ok "${1}"
    [ -n "${ERR}" ] && echo -e "${ERR}"
    return 0
  fi
}


# Function to retrieve user input or plugin a default value for a VAR.
#
function get_user_var {
  # ${1} NAME_OF_VAR
  # ${2} Please enter this var, text...
  # ${3} Default value
  # ${4} If true, does a read -s = silent mode (for passwords)

  local NAME="${1}"
  [ -n "${4}" ] && local READ_SWITCH=" -s "
  
  
  # if variable hasn't already been set
  #if [ -z "${!NAME}" ]; then

    # Print default
    [ -n "${3}" ] && local default=" "$(e_blue "[")${3}$(e_blue "]")
    e_n "${2}${default}: "

    # Read user input into variable 
    if [ -z "${DO_BATCH}" ]; then
      read ${READ_SWITCH} "${NAME}"
    fi

    # If no input then use default
    if [ -z "${!NAME}" ] && [ -n "${3}" ] ; then
      eval "${NAME}"="${3// /\ }"
    fi
  #fi
  #echo "Using: ${!NAME}"
}


# Get confirmation from user before executing command
#
function confirm_run {
  # $1 Are you sure you want to do this?
  # $2 rm -rf / (a command to run)
  # $3 no (default)

  if [ -z "${DO_BATCH}" ]; then
    get_user_var "USER_INPUT" "${1} ($2)" "${3}"
    local USER_INPUT="${USER_INPUT:0:1}"
  else
    local USER_INPUT="y"
  fi

  if [ "${USER_INPUT}" = "y" ] || [ "${USER_INPUT}" = "Y" ]; then
    run "${2}"
    return "${?}"
  else
    e_skip "Skipping command: ${2}"
    return 1 # This will set the $? variable
  fi

}

# Exit with an error message.
#
function die {
  e_dead "Exiting: ${1}"
  exit
}



