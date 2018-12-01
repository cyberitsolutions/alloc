[![Code Climate](https://codeclimate.com/github/cyberitsolutions/alloc/badges/gpa.svg)](https://codeclimate.com/github/cyberitsolutions/alloc) [![Issue Count](https://codeclimate.com/github/cyberitsolutions/alloc/badges/issue_count.svg)](https://codeclimate.com/github/cyberitsolutions/alloc)

# allocPSA
allocPSA is the web-app that takes care of your projects, employees, time
sheets, invoicing and customers.

## Support
Please feel free to contact us at Cyber IT Solutions
[info@cyber.com.au](mailto:info@cyber.com.au) or use the
[forums](http://sourceforge.net/p/allocpsa/discussion/) if you have any
questions.

## Contact
Email [support@allocpsa.com](mailto:support@allocpsa.com) for commercial and
hosting enquiries.

## License
allocPSA is under the GNU Affero General Public License. For more info please
see the LICENSE file or visit the [GNU Affero General Public
License](http://www.gnu.org/licenses/agpl-3.0.en.html) webpage.

## Developers!
We need help! This project receives very little development these days (early
2016), so all help is much appreciated. :)

I (cjbayliss) am working on a way to make it simple for devs to deploy and test
allocPSA. I'll update the document in the future with help in that regard. If
you have ideas, feel free to create an issue here on GitHub with suggestions.

# Installation
Please note: If you are upgrading, please read the _Upgrading_ section below.

allocPSA is generally intended to run with PHP>=5 and MySQL>=4 on a Linux
server. It may also run on a Windows box, but possibly not with the full
functionality.

You will need to install php, mysql, and php-mbstring. The php-gd package is
also recommended. On debian you will need to change the mysql (maridb) config
from:

```
character-set-server  = utf8mb4
collation-server      = utf8mb4_general_ci
```

to:

```
character-set-server  = utf8
#collation-server      = utf8mb4_general_ci
```

To install allocPSA:

1) Put the allocPSA source code in a directory called e.g. `alloc` in your
httpd servers document root. E.g.: `/var/www/html/alloc/`

2) Make the patches and css, e.g.:

```
$ make patches; make css
```

3) In a web browser, go to your servers hostname + directory where you put the
alloc source code, such as: `http://localhost/alloc/`

4) Follow the instructions in the web browser to complete the installation.

# Upgrading
To determine which version you are currently running, look at the fine print at
the bottom of the login screen for allocPSA, or alternatively view the file:
`http://YOUR_ALLOC_INSTALLATION/util/alloc_version`

## Generic Upgrade Instructions
- Backup your allocPSA database. _DO IT NOW_.
- Unpack the new allocPSA source code alongside your current installation.
- Copy the `alloc_config.php` file from your current installation of
  allocPSA into the directory that contains the new installation of allocPSA.

- Finally, update your allocPSA database by going to this address in your web
  browser: `http://YOUR_NEW_ALLOC_INSTALLATION/installation/patch.php`

  Apply each patch separately, starting from the top and working your way
  down. If you get errors stop the process and use the support forums.
