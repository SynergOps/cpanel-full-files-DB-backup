<?php

// Don't allow direct access
if ( basename( $_SERVER['REQUEST_URI'] ) == 'config.php' ) {
	exit( 'config.php does nothing' );
}

// Set this to YES if you want to keep the database backup files locally (where you run this script). This is the default.
// Set this to NO if you want to delete the files after you FTP copy them some where else
define( 'KEEP_LOCAL_BACKUP_FILE',			'YES' );

// Set this to YES if you want to FTP copy your files to another server.
// Set this to NO if you don't want to FTP copy your files to another server. This is the default.
define( 'DO_REMOTE_FTP_COPY',				'NO' );

// CPANEL
define( 'CPANEL_SERVER_ADDRESS',			'abc.def.ghj.mnp' );		// IP address or domain name for the server with the cPanel account
define( 'CPANEL_PORT_NUM',					'2083' );					// The port number for the cPanel. If you have problems, try 2082
define( 'CPANEL_ADMIN_USERNAME',			'admin-username' );			// the admin username for your cPanel account
define( 'CPANEL_ADMIN_PASSWORD',			'veryStrongPassword' );		// the admin password for your cPanel account

// REMOTE FTP
define( 'FTP_SERVER_ADDRESS',				'npq.rst.uvw.xyz' );		// IP address or URL of the FTP server
define( 'FTP_SERVER_PORT',					'21' );						// FTP(S) Port. Default is 21.
define( 'FTP_USERNAME',						'ftp-username' );			// FTP Username
define( 'FTP_PASSWORD',						'ftp-password' );			// FTP Password
define( 'FTP_PATH_TO_COPY',					'/ftp/path/to/copy/' );		// FTP Path (where do you want to copy the files?)

// Set this to NO to use "active" FTP. This is the default.
// Set this to YES to use FTP in passive mode. Only necessary if you're having some FTP problems.
define( 'FTP_USE_PASSIVE',					'NO' );

// Set this to NO to use standard FTP.
// Set this to YES to use FTPS. Your target FTP server MUST also support this. Will attempt to fallback to normal FTP if connection fails.
define( 'FTP_USE_SSL',						'NO' );

