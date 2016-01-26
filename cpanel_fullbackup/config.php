<?php

// Don't allow direct access
if ( basename( $_SERVER['REQUEST_URI'] ) == 'config.php' ) {
	exit( 'config.php does nothing' );
}

// CPANEL ACCOUNT TO BACKUP
define( 'CPANEL_SERVER_ADDRESS',			'abc.def.ghj.mnp' );		// IP address or domain name for the server with the cPanel account
define( 'CPANEL_PORT_NUM',					'2083' );					// The port number for the cPanel. If you have problems, try 2082
define( 'CPANEL_ADMIN_USERNAME',			'admin-username' );			// the admin username for your cPanel account
define( 'CPANEL_ADMIN_PASSWORD',			'veryStrongPassword' );		// the admin password for your cPanel account

// You can choose between 4 options for copying the backup file using COPY_METHOD option
// 1) homedir - leave the backup in place on the server
// 2) passiveftp - FTP copy the file to an offsite FTP server
// 3) ftp - use this instead of passiveftp to FTP if passive doesn't work
// 4) scp - use SCP to copy the file to an offsite server
define( 'COPY_METHOD',						'passiveftp' );

//Want to be notified by email when the backup is finished. Put your email here.
define( 'FTP_NOTIFY_EMAIL',					'' );

// REMOTE FTP BACKUP DETAILS
define( 'FTP_SERVER_ADDRESS',				'npq.rst.uvw.xyz' );		// IP address or URL of the FTP server
define( 'FTP_SERVER_PORT',					'21' );						// FTP(S) Port. Default is 21.
define( 'FTP_USERNAME',						'ftp-username' );			// FTP Username
define( 'FTP_PASSWORD',						'ftp-password' );			// FTP Password
define( 'FTP_PATH_TO_COPY',					'/ftp/path/to/copy/' );		// FTP Path (where do you want to copy the files?)
