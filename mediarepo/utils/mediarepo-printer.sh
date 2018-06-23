#!/bin/bash

# In der /etc/cups/cups-pdf.conf als Postprocessing script dieses
# Script eintragen.
# Dann in der /etc/apparmor.d/usr.sbin.cupsd am Ende vor der '}'
# /home/cvs/mediarepo/utils/mediarepo-printer.sh uxr,
# eintragen

/home/cvs/mediarepo/utils/remote-file-upload.py --config /home/${2}/.mediarepo-upload.conf --section "Printer" ${1}
