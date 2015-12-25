Uppu4
=====
To deploy my filesharing app "Uppu4" on your local computer, you have to go through the following steps:

1. Clone git repository into the directory of your choosing.

2. In your bash go to the root directory of the project and run "composer install".

3. After all required libraries and dependencies are installed, type in the bash following command:

* mysql -h hostname -u user --password=password Uppu4 < Uppu4.sql

to set up required tables in your database.

Server requirements:

Your apache2 server has to allow usage of .htaccess files and also it has to have x-sendfile module to be installed.
Also you have to add following lines into site's *.conf file to make the project work correctly:

XSendFile On

XSendFilePath /path/to/your/project/Uppu4/public/upload/