allocPSA
========

allocPSA is the web-app that takes care of your projects, employees, time
sheets, invoicing and customers.

Support
=======

Please feel free to contact us at Cyber IT Solutions <info@cyber.com.au> or use
the forums at http://sourceforge.net/projects/allocpsa/ if you have any
questions.

Contact
=======

Email support@allocpsa.com for commercial and hosting enquiries.

License
=======

allocPSA is under the GNU Affero General Public License. Please see the LICENSE
file for more details.

Developers!
===========

We need help! This project receives very little development these days (early
2016), so all help is much appreciated. :)

I (cjbayliss) am working on a way to make it simple for devs to deploy and
test allocPSA. I'll update the document in the future with help in that regard.
If you have ideas, feel free to create an issue here on GitHub with suggestions.

Installation
============

Please note: If you are upgrading, please read the UPGRADING section below.

allocPSA is generally intended to run with PHP>=5 and MySQL>=4 on a Linux
server. It may also run on a Windows box, but possibly not with the full
functionality.

To install allocPSA:

1) Put the allocPSA source code in a directory called e.g. "alloc" in your
   httpd servers document root. E.g.: /var/www/html/alloc/

2) Make the patches and css, e.g.:

    ```bash
    $ make patches; make css
    ```

3) In a web browser, go to your servers hostname + directory where you put the
   alloc source code, such as: http://localhost/alloc/

4) Follow the instructions in the web browser to complete the installation.


Upgrading
=========

To determine which version you are currently running, look at the fine print at
the bottom of the login screen for allocPSA, or alternatively view the file:

```http://YOUR_ALLOC_INSTALLATION/util/alloc_version```


Generic Upgrade Instructions
----------------------------

1.  Backup your allocPSA database. DO IT NOW.

2.  Unpack the new allocPSA source code alongside your current installation.

3.  Copy the ```alloc_config.php``` file from your current installation of
    allocPSA into the directory that contains the new installation of allocPSA.

4.  Finally, update your allocPSA database by going to this address in your web
    browser: ```http://YOUR_NEW_ALLOC_INSTALLATION/installation/patch.php```

    Apply each patch separately, starting from the top and working your way
    down. If you get errors stop the process and use the support forums.



To upgrade from 1.2.256 to 1.3.508 - The Safe Way
-------------------------------------------------

1.  Completely replace the old allocPSA source code in the webserver
    document root, with the new source code.

2.  Visit an address in your webbrowser for the first part of the upgrade:

    ```http://YOUR_ALLOC_INSTALLATION/installation/patch_1_2_256_to_1_3_497.php```

    Follow the instructions carefully. Refresh the page every time you make a
    change, the todo list should get shorter everytime you've made a change and
    then refreshed the page.

3.  After you follow all the instructions there, you will be given a link to go
    to complete the database upgrade. Something like:

    ```http://YOUR_ALLOC_INSTALLATION/installation/patch.php```

    Ensure you only visit this link, once you have been told to by completing
    everything on step 2 - otherwise this step may fail.

    Once you've visited this link, click all the check boxes on the page
    (sorry), and click the button at the bottom to apply the ticked changes.
    My advice would be to start at the top and tick a few at a time, so that if
    there are any errors popping it will make it easier to determine which
    database patch is causing the problem.  

    Once they've all been applied and there are no more checkboxes to tick,
    refresh the page a couple times and you should get taken to the login
    screen. If you got errors at any stage then let us know about them.


4.  If you previously had cronjobs installed for the reminders, repeating
    transactions, or daily digest emails, you should remove them and install
    these new simpler ones (these should be a little more portable to windows).

    * Check every 10 minutes for any allocPSA Reminders to send

    ```bash
    */10 * * * * wget -q -O /dev/null http://YOUR_ALLOC_INSTALLATION/reminder/sendReminders.php
    ```

    * Send allocPSA Daily Digest emails once a day at 4:35am

    ```bash
    35 4 * * * wget -q -O /dev/null http://YOUR_ALLOC_INSTALLATION/person/sendEmail.php
    ```

    * Check for allocPSA Repeating Expenses once a day at 4:40am
    ```bash
    40 4 * * * wget -q -O /dev/null http://YOUR_ALLOC_INSTALLATION/finance/checkRepeat.php
    ```



To upgrade from 1.2.256 to 1.3.508 - The Quick Way (need root shell access)
---------------------------------------------------------------------------

1.  As the root user, run these commands:
    ```bash
    cd YOUR_ALLOC_INSTALLATION/util/
    chmod a+x ./patch.sh
    ./patch.sh http://YOUR_ALLOC_INSTALLATION/
    ```

    You should get a whole lot of output on the screen. 

2.  Run that last command again:

    ```bash
    ./patch.sh http://YOUR_ALLOC_INSTALLATION/
    ```

    You should get very little output on the screen. Make sure the second last line says: 

    ```php
    eval ''
    ```

3. Double check that all the database patches got applied, do something like:

    ```sql
    mysql -u root DATABASENAME -e "SELECT patchName FROM patchLog"
    ```

    There should be a list of 40 patch files.

4.  Lastly, don't forget to manually update the cronjobs as specified in step 4
    above.
