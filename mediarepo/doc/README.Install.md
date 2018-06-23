MediaREPO Installation Instructions
==================================

NOTE FOR VERSION 4.0.0
======================

Since version 4.0.0 of MediaREPO installation has been simplified. 
ADOdb is no longer needed because the database access is done by
PDO.

IMPORTANT NOTE ABOUT TRANSLATIONS
=================================

As you can see MediaREPO provides a lot of languages but we are not professional 
translators and therefore rely on user contributions.

If your language is not present in the login panel:
- copy the language/English/ folder and rename it appropriately for your
  language
- open the file `languages/your_lang/lang.inc` and translate it
- open the help file `languages/your_lang/help.htm` and translate it too

If you see some wrong or not translated messages:
- open the file `languages/your_lang/lang.inc`
- search the wrong messages and translate them

if you have some "error getting text":
- search the string in the english file `languages/english/lang.inc`
- copy to your language file `languages/your_lang/lang.inc`
- translate it

If there is no help in your language:
- Copy the English help `english/help.htm` file to your language folder
- translate it

If you apply any changes to the language files please send them to the
MediaREPO developers <info@mediarepo.org>.

http://www.iana.org/assignments/language-subtag-registry has a list of
all language and country codes.

REQUIREMENTS
============

MediaREPO is a web-based application written in PHP. It uses MySQL,
sqlite3 or postgresql to manage the documents that were uploaded into
the application. Be aware that postgresql is not very well tested.

Make sure you have PHP 5.3 and MySQL 5 or higher installed. MediaREPO
will work with PHP running in CGI-mode as well as running as module under
apache. If you want to give your users the opportunity of uploading passport
photos you have to enable the gd-library (but the rest of MediaREPO will
work without gd, too).

Here is a detailed list of requirements:

1. A web server with at least php 5.3
2. A mysql database, unless you use sqlite
3. The php installation must have support for `pdo_mysql` or `pdo_sqlite`,
   `php_gd2`, `php_mbstring`
4. Various command line programms to convert files into text for indexing
   pdftotext, catdoc, xls2csv or scconvert, cat, id3 (optional, only needed
   for fulltext search)
5. ImageMagic (the convert program) is needed for creating preview images 
6. The Zend Framework (version 1) (optional, only needed for fulltext search)
7. The pear Log package
8. The pear HTTP_WebDAV_Server package (optional, only need for webdav)
9. SLIM RestApi
10. FeedWriter from https://github.com/mibe/FeedWriter

It is highly recommended to use the quickstart archive (mediarepo-quickstart-x.y.z.tar.gz)
because it includes all software packages for running MediaREPO, though you still need
a working web server with PHP.

QUICKSTART
===========

The fastes way to get MediaREPO running is by unpacking the archive
`mediarepo-quickstart-x.y.z.tar.gz` into your webservers document root.
It will create a new directory `mediarepo51x` containing everything you
need to run MediaREPO with sqlite3. Make sure that the subdіrectory
`mediarepo51x/data`
and the configuration file `mediarepo51/www/conf/settings.xml` is writeable
by your web server. All other directories must just be readable by your
web server. In the next step you need to adjust
the configuration file in `mediarepo51/www/conf/settings.xml`. If you
are not afraid of xml files, then open it in your favorite text editor
and search for `/home/wwww-data`. Replace that part in any path found
with your document root. Alternatively, you can open the installer
with a browser at http://your-domain/mediarepo51x/install/
It will first ask to unlock the installer by creating a file
`ENABLE_INSTALL_TOOL` in the diretory `mediarepo51/www/conf/`. Change all
paths by replacing `/home/wwww-data` with your document root. Once done,
save it, remove the file `ENABLE_INSTALL_TOOL` and point your browser to
http://your-domain/mediarepo51x/.

THE LONG STORY
================

MediaREPO has changed its installation process with version 3.0.0. This gives
you many more options in how to install MediaREPO. First of all, MediaREPO was
split into a core package (`mediarepo_Core-<version>.tar.gz`) and the web
application itself (`mediarepo-<version>.tar.gz`). The core is a pear package
which could be installed as one. It is responsible for all the database
operations. The web application contains the ui not knowing anything about
the database layout. Second, one MediaREPO installation can be used for
various customer instances by sharing a common source. Starting with
version 3.2.0 a full text search engine has been added. This requires
the zend framework and another pear package `mediarepo_Lucene-<version>.tar.gz`
which can be downloaded from the MediaREPO web page. Version 4.0.0 show
preview images of documents which requires `mediarepo_Preview-<version>.tar.gz`.
Finally, MediaREPO has
got a web based installation, which takes care of most of the installation
process.

Before you proceed you have to decide how to install MediaREPO:
1. with multiple instances
2. as a single instance

Both have its pros and cons, but
1. setting up a single instance is easier if you have no shell access to
   the web server
2. the installation script is only tested for single instances

Installation for multiple instances shares the same source by many
instances but requires to create links which is not in any case possible
on your web server.

0. Some preparation
-------------------

A common source of problems in the past have been the additional software
packages needed by MediaREPO. Those are the PEAR packages `Log` and
`HTTP_WebDAV_Server` as well as the `Zend_Framework`.
If you have full access to the server running a Linux distribution it is
recommended to install those with your package manager if they are provided
by your Linux distribution. If you cannot install it this way then choose
a directory (preferable not below your web document root), unpack the
software into it and extend the php include path with your newly created
directory. Extending the php include can be either done by modifying
php.ini or adding a line like

> php_value include_path '/home/mypath:.:/usr/share/php'

to your apache configuration or setting the `extraPath` configuration
variable of MediaREPO.

For historical reasons the path to the mediarepo_Core and mediarepo_Lucene package
can still be set
in the configuration, which is not recommend anymore. Just leave those
parameters empty.

On Linux/Unix your web server should be run with the environment variable
LANG set to your system default. If LANG=C, then the original filename
of an uploaded document will not be preserved if the filename contains
non ascii characters.

Turn off magic_quotes_gpc in your php.ini, if you are using a php version
below 5.4.

1. Using the installation tool
------------------------------

Unpack mediarepo-<version>.tar.gz below the document root of
your web server.
Install `mediarepo_Preview-<version>.tar.gz` and
`mediarepo_Core-<version>.tar.gz` either as a regular pear package or
set up a file system structure like pear did somewhere on you server.
For the full text search engine support, you will also
need to install `mediarepo_Lucene-<version>.tar.gz`.

For the following instructions we will assume a structure like above
and mediarepo-<version> being accessible through
http://localhost/mediarepo/

* Point you web browser towards http://hostname/mediarepo/install/

* Follow the instructions on the page and create a file `ENABLE_INSTALL_TOOL`
  in the conf directory.

* Create a data directory with the thre sub directories staging, cache
  and lucene.
  Make sure the data directory is either *not* below your document root
	or is protected with a .htaccess file against web access. The data directory
  needs to be writable by the web server.

* Clicking on 'Start installation' will show a form with all necessary
  settings for a basic installation.

* After saving your settings succesfully you are ready to log in as admin and
  continue customizing your installation with the 'Admin Tools'

2. Detailed installation instructions (single instance)
-------------------------------------------------------

You need a working web server with MySQL/PHP5 support and the files
`MediaREPO-<version>.tar.gz`, `mediarepo_Preview-<version>.tar.gz` and
`mediarepo_Core-<version>.tgz`. For the 
full text search engine support, you will also need to unpack
`mediarepo_Lucene-<version>.tgz`.

* Unpack all the files in a public web server folder. If you're working on
  a host machine your provider will tell you where to upload the files.
  If possible, do not unpack the pear packages `mediarepo_Core-<version>.tgz`,
	`mediarepo_Preview-<version>.tgz` and
  `mediarepo_Lucene-<version>.tgz` below the document root of your web server.
	Choose a temporary folder, as the files will be moved in a second.

  Create a directory e.g. `pear` in the same directory where you unpacked
  mediarepo and create a sub directory MediaREPO. Move the content except for the
  `tests` directory of all MediaREPO pear
  packages into that directory. Please note that `pear/MediaREPO` may not 
  (and for security reasons should not) be below your document root.
  
  You will end up with a directory structure like the following

  > mediarepo-<version>
  > pear
  >   MediaREPO
  >     Core.php
  >     Core
  >     Lucene.php
  >     Lucene
  >     Preview
  >     Preview.php

  Since they are pear packages they can also be installed with

	> pear install mediarepo_Core-<version>.tgz
	> pear install mediarepo_Lucene-<version>.tgz
	> pear install mediarepo_Preview-<version>.tgz

* The PEAR package Log is also needed. It can be downloaded from
  http://pear.php.net/package/Log. Either install it as a pear package
	or place it under your new directory 'pear'

  > pear
	>   Log
	>   Log.php

* The package HTTP_WebDAV_Server is also needed. It can be downloaded from
  http://pear.php.net/package/HTTP_WebDAV_Server. Either install it as a
	pear package or place it under your new directory 'pear'

  > pear
  >   HTTP
	>     WebDAV
	>       Server
	>       Server.php

  If you run PHP in CGI mode, you also need to place a .htaccess file
	in the webdav directory with the following content.

	RewriteEngine on
	RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization},last]

* Create a data folder somewhere on your web server including the subdirectories
  staging, cache and lucene and make sure they are writable by your web server,
  but not accessible through the web.

For security reason the data folder should not be inside the public folders
or should be protected by a .htaccess file. The folder containing the
configuration (settings.xml) must be protected by an .htaccess file like the
following.

	> <Files ~ "^settings\.xml">
	> Order allow,deny
	> Deny from all
	> </Files>


If you install MediaREPO for the first time continue with the database setup.

* Create a new database on your web server
  e.g. for mysql:
	create database mediarepo;
* Create a new user for the database with all permissions on the new database
  e.g. for mysql:
	grant all privileges on mediarepo.* to mediarepo@localhost identified by 'secret';
	(replace 'secret' with you own password)
* Optionally import `create_tables-innodb.sql` in the new database
  e.g. for mysql:
	> cat create_tables-innodb.sql | mysql -umediarepo -p mediarepo
  This step can also be done by the install tool.
* create a file `ENABLE_INSTALL_TOOL` in the conf directory and point
  your browser at http://hostname/mediarepo/install


NOTE: UPDATING FROM A PREVIOUS VERSION OR mediarepo

As MediaREPO is a smooth continuation of LetoREPO there is no difference
in updating from LetoREPO or MediaREPO

- make a backup archive of your installation folder
- make a backup archive of your data folder
- dump your current database
- extract the MediaREPO archive to your web server
- edit the conf/settings.xml file to match your previuos settings 
  (you can even replace the file with your own one eventualy adding by hand
  the missing new parameters)
- create a file `ENABLE_INSTALL_TOOL` in the conf directory and point
  your browser at http://hostname/mediarepo/install

The install tool will detect the version of your current MediaREPO installation
and run the required database updates.


3. Email Notification
---------------------

A notification system allows users to receive an email when a
document or folder is changed. This is an event-based mechanism that
notifies the user as soon as the change has been made and replaces the
cron mechanism originally developed. Any user that has read access to a
document or folder can subscribe to be notified of changes. Users that
have been assigned as reviewers or approvers for a document are
automatically added to the notification system for that document.

A new page has been created for users to assist with the management of
their notification subscriptions. This can be found in the "My Account"
section under "Notification List".


4. Nearly finished
------------------

Now point your browser to http://hostname/mediarepo/index.php
and login with "admin" both as username and password.
After having logged in you should first choose "My Account" and
change the Administrator's password and email-address.


CONFIGURING MULTIPLE INSTANCES
==============================

Since version 3.0.0, MediaREPO can be set up to run several parallel instances
sharing the same source but each instance has its own configuration. This is
quite useful if you intend to host MediaREPO for several customers. This
approach still allows to have diffenrent version of MediaREPO installed
and will not force you to upgrade a customer instance, because other
instances are upgraded. A customer instance consists of
1. a directory containing mostly links to the MediaREPO source and a
   configuration file
2. a directory containing the document content files
3. a database

1. Unpack the MediaREPO distribution
----------------------------------

Actually there is no need to set up the database at this point but it won't
hurt since you'll need one in the next step anyway. The sources of MediaREPO
can be anywhere you like. The do not have to be in you www-root. If you just
have access to your www-root directory, then put them there.

2. Setup the instance
---------------------

Unpack the files as described in the quick installation.

Create a directory in your www-root or use www-root for your instance. In the
second case, you will not be able to create a second instance, because each
instance needs its own directory.

Go into that directory create the following links (<mediarepo-source> is the
directory of your initial MediaREPO intallation).

> src -> <mediarepo-source>
> inc -> src/inc
> op -> src/op
> out -> src/out
> js -> src/js
> views -> src/views
> languages -> src/languages
> styles -> src/styles
> themes -> src/themes
> install -> src/install
> index.php -> src/index.php

> ln -s ../mediarepo-<version> src
> ln -s src/inc inc
> ln -s src/op op
> ln -s src/out out
> ln -s src/js js
> ln -s src/views views
> ln -s src/languages languages
> ln -s src/styles styles
> ln -s src/themes themes
> ln -s src/install install
> ln -s src/index.php index.php

Create a new directory named conf and run the installation tool.

Creating the links as above has the advantage that you can easily switch
to a new version and go back if it is necessary. You could even run various
instances of MediaREPO using different versions.

3. Create a database and data store for each instance
-----------------------------------------------------

Create a database and data store for each instance and adjust the database
settings in conf/settings.xml or run the installation tool.

Point your web browser towards the index.php file in your new instance.

LICENSING
=========

MediaREPO is licensed unter GPLv2

Jumploader is licensed as stated by the author on th web site
<http://jumploader.com/>

-- Taken from web site of jumploader  ---
You may use this software for free, however, you should not:

- Decompile binaries.
- Alter or replace class and/or resource files.
- Redistribute this software under different name or authority.

If you would like a customized version, I can do this for a fee. Don't hesitate to contact me with questions or comments.

Uwe Steinmann <info@mediarepo.org>
