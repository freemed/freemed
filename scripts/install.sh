#!/bin/bash
#  file: scripts/install.sh
#  desc: installer for the freemed project
#  code: jeff b (jeff@univrel.pr.uconn.edu)
#  lic : GPL, v2

VERSION=`scripts/cfg-value version`
HTTPDCONF="/etc/httpd/conf/httpd.conf"

echo "freemed project installer v$VERSION"
echo "code by jeff b (jeff@univrel.pr.uconn.edu)"
echo "(c) 1999 under the GPL, v2"
echo ""

echo -n " * Fixing permissions on directories ... "
chown root.nobody * -Rf
chmod 644         * -Rf
chmod a+x scripts/* -Rf
echo "done"

echo -n " * Rebuilding the language registry ... "
scripts/freemed-refresh-language-registry
echo "done"

echo " * Looking at httpd.conf file for Directory directive ..."
if [ -f /etc/httpd/conf/srm.conf ]; then
  # if it uses an srm.conf type file...
  echo "done"
  echo -n " * Using split httpd configuration files for mod ... "
  ( cd /etc/httpd/conf;                                          \
    mv -f srm.conf srm.conf.orig;                                \
    cat srm.conf.orig                                            \
     | sed -e "s/DirectoryIndex\ /DirectoryIndex\ index.php3\ /" \
      > srm.conf;                                                \
    mv -f srm.conf srm.conf.orig;                                \
    cat srm.conf.orig                                            \
     | sed -e "s/AddType\ application\/x-httpd-php3\ .php3/AddType\ application\/x-httpd-php3\ .php3\ .php\ .inc/"                              \
      > srm.conf;                                                \
    echo ""                            >> srm.conf;              \
    echo "Alias /freemed /usr/freemed" >> srm.conf;              \
    echo ""                            >> access.conf;           \
    echo "<Directory /usr/freemed>"    >> access.conf;           \
    echo "Options Indexes Includes FollowSymLinks" >> access.conf; \
    echo "AllowOverride None"          >> access.conf;           \
    echo "order allow,deny"            >> access.conf;           \
    echo "allow from all"              >> access.conf;           \
    echo "</Directory>"                >> access.conf;           \
  )
  echo "done"
else
  # standard httpd.conf only configuration
  echo "done"
  echo -n " * Using single httpd configuration file for mod ... "
  ( cd /etc/httpd/conf; \
    mv -f httpd.conf httpd.conf.orig;                            \
    cat httpd.conf.orig                                          \
     | sed -e "s/DirectoryIndex\ /DirectoryIndex\ index.php3\ /" \
      > httpd.conf;                                              \
    mv -f httpd.conf httpd.conf.orig;                            \
    cat srm.conf.orig                                            \
     | sed -e "s/AddType\ application\/x-httpd-php3\ .php3/AddType\ application\/x-httpd-php3\ .php3\ .php\ .inc/"                              \
      > httpd.conf;                                              \
    echo ""                            >> httpd.conf;            \
    echo "Alias /freemed /usr/freemed" >> httpd.conf;            \
    echo ""                            >> httpd.conf;            \
    echo "<Directory /usr/freemed>"    >> httpd.conf;            \
    echo "Options Indexes Includes FollowSymLinks" >> access.conf; \
    echo "AllowOverride None"          >> httpd.conf;            \
    echo "order allow,deny"            >> httpd.conf;            \
    echo "allow from all"              >> httpd.conf;            \
    echo "</Directory>"                >> httpd.conf;            \
  )
  echo "done"
fi
