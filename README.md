# FreeMED Electronic Medical Record and Practice Management System

## Installation

This package has been updated to require PHP 8.3. Reference implementation has been on Debian.

Instructions are available in the wiki under [Installation](https://github.com/freemed/freemed/wiki/Installation).

## Legalese

```
FreeMED Electronic Medical Record and Practice Management System
Copyright (C) 1999-2024 FreeMED Software Foundation

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
```

## Errata

* index.php has to be added in /etc/apache2/?????.conf under the list of valid index files.
* These are to be installed in /usr/share/freemed, and a virtual directory alias should be made in the Apache2 configuration files, as well as giving read perms to Apache.
* Code is annotated with an author if the primary author is other than ```Jeff Buchbinder <jeff@freemedsoftware.org>```
* MySQL version 5.0.20+ is required.
* Since FreeMED involves a fairly hefty codebase, php optimizing tricks, such as the bware_cache module or the Zend Optimizer should be used to minimize load times. [APC](http://apc.communityconnect.com) is also available, as well as the venerable (but largely unmaintained) [Turck MMcache](http://turck-mmcache.sourceforge.net/).

## Troubleshooting

```
Q: I get an SQL connection error. What's wrong?
A: Try using the commandline clients for your SQL server. (For MySQL, this is
   simply "mysql -u (user) -p") If this works and you still can't log in with
   FreeMED, you may not have created the FreeMED database properly. You can
   log into the administrative console and add the database with the command
   "CREATE DATABASE freemed;". If this still does not allow you to connect,
   check your username, password, host, and table settings in lib/settings.php
   in your FreeMED installation. If you haven't set these properly, FreeMED
   will be unable to communicate with the SQL backend. If this still does
   not work, there is a chance that the machine or account that FreeMED is
   using does not have proper access rights on the SQL server. Please consult
   your SQL server documentation if this is the case.
```

If you have further issues, please open a ticket on the [Github Issue Tracker](https://github.com/freemed/freemed/issues)

