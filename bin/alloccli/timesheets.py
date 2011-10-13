from alloc import alloc

class timesheets(alloc):

  one_line_help = "Print a list of time sheets."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append((''  ,'csv            ','Return the results in CSV format.'))
  ops.append(('q' ,'quiet          ','Run with no output except errors.'))
  ops.append(('i' ,'items          ','Show time sheet\'s items.'))
  ops.append(('p:','project=ID|NAME','A project ID, or a fuzzy match for a project name.'))
  ops.append(('s:','status=STATUS  ','The time sheets\' status. Can accept multiple values, eg: "edit,manager,admin,invoiced,finished,rejected" or "all". Default: edit'))
  ops.append(('a:','account=TF     ','The time sheets\' TF name.'))
  ops.append(('c:','creator=NICK   ','The time sheets\' creator username.'))
  ops.append(('t:','time=ID        ','A time sheet ID.'))
  ops.append(('h:','hours=NUM      ','The time sheets must have this many hours recorded eg: "7" eg: ">7 AND <10 OR =4 AND !=8"'))
  ops.append(('d:','date=YYYY-MM-DD','If --items is specified, then match against the items\' date. Else match against the date of the time sheet\'s earliest item.'))
  ops.append(('o:','order=NAME     ','The order the Time Sheets or Items are displayed in. Default for time sheets: "From,Time ID" Default for items: "Date,Item ID"'))
  ops.append(('f:','fields=LIST    ','The commar separated list of fields you would like printed, eg: "all" eg: "Time ID,Item ID,Task ID,Comment"')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= one_line_help
  help_text+= '''\n\n%s

If run without arguments this program will display all of your editable time sheets.

Examples:
alloc timesheets --hours "2" --date 2010-01-01
alloc timesheets --hours ">2 AND <10 OR >20 AND <=100"
alloc timesheets --status finished --hours ">=7" --date "<=$(date -d '1 week ago' +%%Y-%%m-%%d)"

alloc timesheets --date "2010-10-10"
alloc timesheets --date "<=2010-10-10"
alloc timesheets --date ">=2010-10-10" --items'''

  def run(self, command_list):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    # Initialize some variables
    self.quiet = o['quiet']
    personID = self.get_my_personID()
    projectID = ""
    timeSheetID = ""
    order_ts = "From,Time ID"
    order_tsi = "Date,Item ID"
    status = "edit"

    # Get a projectID either passed via command line, or figured out from a project name
    if self.is_num(o['project']):
      projectID = o['project']
    elif o['project']:
      projectID = self.search_for_project(o['project'],personID)

    if self.is_num(o['time']):
      timeSheetID = o['time']

    if ',' in o['status']:
      status = o['status'].split(',')
    elif o['status'] == 'all':
      status = 'edit,manager,admin,invoiced,finished,rejected'.split(',')
    elif o['status']:
      status = o['status']

    if o['order']:
      order = o['order']
    elif o['items']:
      order = order_tsi
    else:
      order = order_ts

    ops = {}
    if timeSheetID:
      ops['timeSheetID'] = timeSheetID
    else:
      ops['status'] = status

      if 'account' in o and o['account']:
        tfargs = {}
        tfargs['method'] = 'get_tfID'
        tfargs['name'] = o['account']
        ops['tfID'] = self.make_request(tfargs)
      elif o['creator']:
        ops['personID'] = self.get_my_personID(o['creator'])
      else:
        ops['personID'] = personID

      if projectID:
        ops['projectID'] = projectID

    if o['hours']:
      ops['timeSheetItemHours'] = o['hours']

    if o['items']:
      timeSheets = self.get_list("timeSheet",ops)
      if timeSheets:
        tids = []
        for id,t in timeSheets.items():
          tids.append(id)
        if tids:
          ops = {"timeSheetID": tids}
          if o['date']:
            # >=
            ops['date'],ops['dateComparator'] = self.parse_date_comparator(o['date'])
          timeSheetItems = self.get_list("timeSheetItem",ops)
          self.print_table("timeSheetItem",timeSheetItems, o["fields"] or self.row_timeSheetItem, sort=order)

    else:
      if o['date']:
        # <=
        ops['dateFrom'],ops['dateFromComparator'] = self.parse_date_comparator(o['date'])
      timeSheets = self.get_list("timeSheet",ops)
      self.print_table("timeSheet",timeSheets, o["fields"] or self.row_timeSheet, sort=order)
  

