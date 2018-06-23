Running one of the scripts
---------------------------

Scripts in this folder are ment to be called on the command line by
either executing one of the shell wrappers `mediarepo-*` or by calling
`php -f <scriptname> -- <script options>`.
If you run the adddoc.php script make sure to run it with the permissions
of the user running your web server. It will copy files right into
your content directory of your MediaREPO installation. Don't do this
as root because you will most likely not be able to remove those documents
via the web gui. If this happens by accident, you will still be able
to fix it manually by setting the propper file permissions for the document
just created in your content directory. Just change the owner of the
document folder and its content to the user running the web server.

Do not allow regular users to run this scripts!
-----------------------------------------------

None of the scripts do any authentication. They all run with a MediaREPO
admin account! So anybody being allowed to run the scripts can modify
your REPO content.
