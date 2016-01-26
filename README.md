# Automatic cPanel Web Hosting and Database Backup Scripts
by: [https://www.hostliketoast.com/](https://www.hostliketoast.com/)

There are two scripts available here:

Script Name: Perform FULL cPanel account Backup
Folder: **cpanel_fullbackup**
Description: Allows you to perform an automated (using cron?) backup of a cPanel web hosting account. 
Version: v2.0
Author: Host Like Toast
Source URI: https://www.hostliketoast.com/developer-channel/

Script Name: Perform CPanel DB Backup
Folder: **cpanel_dbbackup**
Description: Allows you to perform an automated (using cron?) backup of all databases for a given CPanel hosting account. 
Version: v2.1
Author: Host Like Toast
Author URI: https://www.hostliketoast.com/developer-channel/

## Video tutorial: 

[https://www.youtube.com/watch?v=9bMhgGljqJ8](https://www.youtube.com/watch?v=9bMhgGljqJ8)

## Automatic cPanel Full Backup Script ( v2.0 )

The free Automatic cPanel Web Hosting Backup Script lets you:

(v2012-10-01)
- create a full backup of your cPanel web hosting account
- schedule full backups of your cPanel web hosting account using the CRON feature of your web hosting
- specify the notification email after the backup is complete
- specify the method of backup copy: passiveftp, ftp, scp, local

### How does the cPanel Web Hosting Backup Script work?

#### The steps necessary to getting this script to work are as follows:

1. Download the package
2. Open the following file in your favourite text editor: config.php
3. Simply configure each option that is detailed in the file. There are full comments on how you should complete the file.
4. Save the configuration file.
5. Upload all the files from the package to a server or web space of your choice.
6. Either execute the file: **perform_cpanel_fullbackup.php** at the command-line, or call it using a browser.
7. Once you’re happy with the results, setup your CRON job and leave it to run.

## Automatic cPanel MySQL Backup Script

The free Automatic cPanel MySQL Backup Script lets you:

Automatic cPanel MySQL Backup Script (PHP) ( v2.0 )

(v2012-09-26)
- download a backup to your server of all your MySQL databases within a single cPanel hosting account
- FTP/SFTP copy those backup files to a remote FTP server

### How does the cPanel MySQL Database Backup Script work?

#### The steps necessary to getting this script to work are as follows:

1. Download the package
2. Open the following file in your favourite text editor: config.php
3. (Pro version only) Make sure DUMMY_MODE setting is ‘YES’ for doing testing.
4. Simply configure each option that is detailed in the file. There are full comments on how you should complete the file.
5. Save the configuration file.
6. Upload all the files from the package to a server or web space of your choice.
7. Either execute the file: **perform_cpanel_dbbackup.php** at the command-line, or call it using a browser.
8. Once you’re happy with the results, setup your CRON job and leave it to run. (Remember to change the Dummy mode back to ‘NO’)

# License

Copyright (c) 2012 Host Like Toast <helpdesk@hostliketoast.com>
 All rights reserved.
  
"Perform Full cPanel Account Backup" and "Perform CPanel DB Backup" is distributed under the GNU General Public License, Version 2,
June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
St, Fifth Floor, Boston, MA 02110, USA
  
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
