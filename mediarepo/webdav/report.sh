#!/bin/sh

#curl -u admin -X REPORT --data-binary "@report.xml" "http://localhost/bizerba/addressbook/addressbookserver.php/addressbooks/admin/users/admin/"

curl -u steinm -X REPORT --data-binary "@report.xml" "https://nextcloud.steinmann.cx/remote.php/dav/addressbooks/users/steinm/contacts/"
