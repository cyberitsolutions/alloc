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
  ops.append(('s:','status=STATUS  ','The time sheets\' status eg: edit, manager, admin, invoiced, finished. Default: edit'))
  ops.append(('t:','time=ID        ','A time sheet ID.'))
  ops.append(('h:','hours=NUM      ','The time sheets must have this many hours recorded eg: "7" eg: ">7 AND <10 OR =4 AND !=8"'))
  ops.append(('d:','date=YYYY-MM-DD','The from date of the earliest time sheet item.'))
  ops.append(('o:','order=TS&TSI   ','The order the Time Sheets and Items are displayed in. Default: "ID&Date"'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= one_line_help
  help_text+= '''\n\n%s

If run without arguments this program will display all of your editable time sheets.

Examples:
alloc timesheets --hours "2" --date 2010-01-01
alloc timesheets --hours ">2 AND <10 OR >20 AND <=100"
alloc timesheets --status finished --hours ">=7" --date "$(date -d '10 week ago' +%%Y-%%m-%%d)"'''

  def run(self):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    # Initialize some variables
    self.quiet = o['quiet']
    self.csv = o['csv']
    personID = self.get_my_personID()
    projectID = ""
    timeSheetID = ""
    order1 = "ID"
    order2 = "Date"
    status = "edit"

    # Get a projectID either passed via command line, or figured out from a project name
    if self.is_num(o['project']):
      projectID = o['project']
    elif o['project']:
      projectID = self.search_for_project(o['project'],personID)

    if self.is_num(o['time']):
      timeSheetID = o['time']

    if o['status']:
      status = o['status']

    if o['order']:
      if '&' not in o['order']: o['order']+='&'
      order1,order2 = o['order'].split("&")

    if not order2: order2 = 'Date'

    ops = {}
    if timeSheetID:
      ops['timeSheetID'] = timeSheetID
    else:
      ops['personID'] = personID
      ops['status'] = status
      if projectID:
        ops['projectID'] = projectID

    if o['hours']:
      ops['timeSheetItemHours'] = o['hours']
    if o['date']:
      ops['dateFrom'] = o['date']

    timeSheets = self.get_list("timeSheet",ops)

    if timeSheets:
      if o['items']:
        for id,t in timeSheets.items():
          self.print_table(self.get_list("timeSheetItem",{"timeSheetID": id}), self.row_timeSheetItem, sort=order2)
      else:
        self.print_table(timeSheets, self.row_timeSheet, sort=order1)




