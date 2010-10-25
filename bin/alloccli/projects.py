from alloc import alloc

class projects(alloc):

  one_line_help = "Print a list of projects."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append((''  ,'csv            ','Return the results in CSV format.'))
  ops.append(('p:','project=ID|NAME','A project ID, or a fuzzy match for a project name.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= one_line_help
  help_text+= "\n\n%s\n\nIf called without arguments this program will display all of your projects."


  def run(self):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    # Initialize some variables
    #self.quiet = o['quiet']
    self.csv = o['csv']
    personID = self.get_my_personID()
    projectID = ""

    # Get a projectID either passed via command line, or figured out from a project name
    filter = {}
    if self.is_num(o['project']):
      filter["projectID"] = o['project']
    elif o['project']:
      filter["projectID"] = self.search_for_project(o['project'],personID)

    filter["personID"] = personID
    filter["projectStatus"] = "Current"

    projects = {}
    projects = self.get_list("project",filter)

    self.print_table(projects, ["projectID","Project ID","projectName","Project Name"], sort="Project Name")
      


