Alloc test plan
===============

Syntax::

    |   precondition
    >   do something
    <   expected result

#. Login test::

    |   go to login page
    >   enter correct username
    >   enter correct password
    <   login should succeed

#. Tasks tests::

    |   get to the tasks tab
    >   click 'New Task'
    >   enter a task name
    >   select a project
    >   write a description
    >   give it a tag
    >   set the manager
    >   fill in an estimate
    >   click save
    <   task should get created

    |   on the task you just created
    >   click on comments
    >   make a comment
    >   save the comment
    <   comment should be visible

    |   NOT AUTOMATIBLE
    >   click 'New Comment'
    >   write a new comment
    >   attach a pdf file
    >   save the comment
    >   download the attached file
    <   should be a pdf file that is downloaded not a php file

    >   reply to a comment
    >   save comment
    <   should appear in-line

    |   on the comments page of a task
    >   click 'Download' to download the comments
    <   should download the comments

    |   on the comments page of a task
    >   click 'Summary'
    <   should show a summary of comments
    >   click 'Full'
    <   should show the full comments

    |   on the reminders page
    >   click 'Add Reminder'
    >   click save
    <   reminder should be saved

    |   on the attachments page
    >   click browse to find a file
    >   choose a file
    >   click 'Upload Attachment'
    <   file should upload

    >   download the file you upload
    <   it should download in the same format

    >   delete the attachment
    <   file should be deleted

    |   go to history page
    <   should be some task history

    |   go to all page
    <   all 'Main', 'Comments', 'Reminders', 'Attachments', and 'History' should be showed

    |   go to tasks page
    >   select some tasks
    >   modify selected tasks
    >   repeat above until all modify options are tested

    >   click show filter
    >   change filter parameters and click 'Filter' until all tested
    <   should work as expected

    >   click 'PDF'
    <   should show a pdf

#. Home page tests::

    |   go to 'Home' tab
    >   click on the spanner for the tasks section
    >   change multiple settings and filter
    >   repeat above changing different settings
    <   should be no obvious problems
    <   sorting should work

    |   go to 'Home' tab
    >   click on the spanner in the calendar section
    >   change the calendar weeks and save
    <   should update the calendar accordingly
    >   now change the weeks
    <   should update the calendar accordingly

    >   clicking on links in the calendar should work

    >   now try adding time with the 'add time' tool <-- can't be automated
    <   should add time

    >   click on the 'Time Sheet Statistics'
    <   should display time sheet stats

    <   clicking on time sheets should show them

    <   adjusting the project list should work

#. Clients tests::

    |   go to the client page
    >   click show filter and make changes
    <   changes should update the list accordingly

    >   click new client
    >   add client name, info, etc.
    >   save
    >   add contact, phone num, etc.
    >   go back to the client page
    <   new client with info should be there

    |   got to the project page
    >   click show filter and make changes
    <   changes should update the list accordingly

    >   click new project
    >   fill in info
    >   click save
    >   go to the project page again
    <   new project should be visible

#. Time tests::

    |   go to time page
    >   go into some timesheets
    <   they should display as proper timesheets

    >   click 'Show Filter'
    >   change filter parameters and click 'Filter' until all tested
    <   should work as expected

    >   click 'New Time Sheet'
    <   should take you to the create new time sheet page

    | FIXME! We need to add more time sheet tests.

#. Sales tests::

    |   go to the Sales page
    >   go into a sales
    <   it should display properly

    >   click 'Show Filter'
    >   change filter parameters and click 'Filter' until all tested
    <   should work as expected

    >   click 'Products'
    <   should display a list of products

    |   manager user
    >   click 'New Product'
    >   fill in the name and price
    >   click save
    <   new product should be made
    >   click 'New Sale'
    >   create a new sale
    <   should work

    >   repeat above on Sales page
    <   should be no errors

#. People tests::

    |   go to People page
    <   a list of people should be shown
    >   click 'Show Filter'
    >   adjust filter parameters and click filter
    <   should display the changes

    >   click on 'Person Graphs'
    <   graphs of what people have done should show

    >   click 'Skill Matrix'
    <   a list of skills should be shown

    |   manager account
    >   click new person
    >   fill in the details
    >   click save
    <   should add a new person

#. Wiki tests::

    |   go to the Wiki page
    <   choosing files and folders should work
    <   creating new files and folders should work

    |   go to the Tools page
    >   click on all the links
    <   they should work

#. ★ tests::

    |   go to the ★
    >   if there are stared items they should show

    |   go to <username>
    >   adjust user info
    <   should change ok
    >   add/remove/edit 'Areas of Expertise'
    >   add/remove/edit 'Absence Forms'
    >   adjust 'Preferences'
    <   all should work

#. Help tests::

    |   go to Help page
    <   the help link should work

#. Search tests::

    >   do a search
    <   should work

#. Logout test::

    >   click logout
    <   should logout
