How to use the automated test suite
===================================

.. WARNING:: This test suite is in no way secure. If you are using it on a
             production system, or a system that needs to remain secure, you
             are using AT YOU'RE OWN RISK!

First install python-selenium and Firefox. Most distros don't have the
matching Selenium version for the Firefox version. So it is advisable to
install python-selenium with pip, e.g.:

    $ sudo pip install python-selenium

Now edit the default.cfg to have the hostname of you're testing server, the
usernames, and the passwords.

Next run the 'test_alloc' script, like so:

    $ ./test_alloc

It will run through all the tests that the average user does.

You can test only one page, e.g.:

    $ ./test_alloc alloc.test_tasks
