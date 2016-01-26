<?php
/**
 * Copyright (c) 2011 Host Like Toast <helpdesk@hostliketoast.com>
 * All rights reserved.
 * 
 * "Perform CPanel DB Backup" is distributed under the GNU General Public License, Version 2,
 * June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/*
	Script Name: Perform CPanel DB Backup
	Description: Allows you to perform an automated (using cron?) backup of all databases for a given CPanel hosting account. 
	Version: v2.1
	Author: Host Like Toast
	Author URI: http://www.hostliketoast.com/
*/

/**
 *  CHANGELOG
 *  
 *  [2012-09-26] v2.1:	Added FTP_USE_PASSIVE option
 *  					Added FTP_USE_SSL option
 *  					Added FTP_SERVER_PORT option
 *  [2012-09-25] v2.0:	New Release that support FTP.
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set("error_log" , dirname(__FILE__)."/cpanel_mysql_backup.log.txt");

// Maximum script execution time in seconds, default is 600 (10 minutes).
// If you have really large databases, you may need to increase this value substantially.
set_time_limit( 600 );

class Worpit_Cpanel_MySql_Backup {
	
	const CONFIG_FILE = 'config.php';
	
	protected $m_aConfigKeys;
	protected $m_aDatabaseList;
	
	protected $m_oFtpConnection;
	
	public function __construct() {
		
		$this->m_aConfig = array();
		
		$this->m_aConfigKeys = array(
				
				'CPANEL_SERVER_ADDRESS',
				'CPANEL_PORT_NUM',
				'CPANEL_ADMIN_USERNAME',
				'CPANEL_ADMIN_PASSWORD',
				
				'FTP_SERVER_ADDRESS',
				'FTP_SERVER_PORT',
				'FTP_USERNAME',
				'FTP_PASSWORD',
				'FTP_PATH_TO_COPY',
				
				'KEEP_LOCAL_BACKUP_FILE',
				'DO_REMOTE_FTP_COPY',
				'FTP_USE_PASSIVE',
				'FTP_USE_SSL'
		);

		$this->writeLog( 'Process ID: '.getmypid() );

	}//__construct
	
	public function readConfig() {
	
		if ( !is_file( self::CONFIG_FILE ) ) {
			$this->writeLog(' There is no configuration file in expected location: '.self::CONFIG_FILE );
			return false;
		}
	
		$sConfigContent = file_get_contents( self::CONFIG_FILE );
	
		if ( $sConfigContent === false ) {
			$this->writeLog(' The config file is there, but I could not open it to read: '.self::CONFIG_FILE );
			return false;
		}
	
		foreach ( $this->m_aConfigKeys as $sKey ) {
			preg_match( "/".strtoupper( $sKey )."(\'|\\\")\s*,\s*(\'|\\\")(.+)\g{-2}/i", $sConfigContent, $aMatches );
			if ( !isset($aMatches[3]) ) {
				$this->m_aConfig[$sKey] = '';
			} else {
				$this->m_aConfig[$sKey] = $aMatches[3];
			}
		}

		return true;
	}//readConfig
	
	public function runBackup() {
		
		$sTimeStamp = date( "Y-m-d-H-i-s" );
		
		$this->writeLog( "** START @ $sTimeStamp **" );
		
		// 1. Read the config
		if ( !$this->readConfig() ) {
			$this->writeLog( "No valid configuration read. Quitting." );
			return;
		}
		
		// 2. Get a list of Databases
		$this->writeLog( "Attempting to retrieve a list of Databases." );
		$this->getAllCpanelDatabases();
		if ( empty($this->m_aDatabaseList) ) {
			$this->writeLog( "It appears you don't have any databases. Quitting." );
			return;
		}
		else {
			$this->writeLog( "Successfully obtained list of databases." );
		}
		
		// 3. Open up the FTP connection if needed.
		$fDoFtp = strtoupper($this->m_aConfig['DO_REMOTE_FTP_COPY']) == 'YES';
		if ( $fDoFtp ) {
			$this->m_aConfig['FTP_PATH_TO_COPY'] = rtrim( $this->m_aConfig['FTP_PATH_TO_COPY'], '/' ) . '/';
			$fDoFtp = $this->openFtpConnection();
			$fFtpSuccess = true; //flag used later to stop repeated FTP failures.
			
			//FTP Connection Failed and you intend to delete local copies, no point in continuing.
			if ( !$fDoFtp && ($this->m_aConfig['KEEP_LOCAL_BACKUP_FILE'] != 'YES') ) {
				$this->writeLog( "FTP connection failed AND you don't plan to keep your backups locally. No point in continuing. Quitting." );
				return;
			}
			
		}
		
		// 4. For each DB, download it and then copy it to FTP if requested. Then delete if requested.
		for ( $i = 0; $i < count( $this->m_aDatabaseList ); $i++ ) {
			
			$sDbName = $this->m_aDatabaseList[$i];
			$sDbFileName = $sTimeStamp.'_'.$sDbName.'.sql.gz';
			
			$this->writeLog( 'Being Processing Database: '.$sDbName );
			$fSuccess = $this->loginAndDownloadFile( $sDbName, $sDbFileName );
			
			if ( $fSuccess !== true ) {
				continue;
			}
			
			$this->writeLog( 'Downloaded Database Locally: '.$sDbName );
			
			//Only attempt to FTP if it's been set in the config, and the last copy was a success.
			if ( $fDoFtp && $fFtpSuccess ) {
				$this->writeLog( 'Starting Database FTP Copy: '.$sDbName );
				$fFtpSuccess = $this->ftpFileRemotely( $sDbFileName );
				
				//An FTP transfer failed indicating all future transfers will fail. Quiting processing.
				if ( !$fFtpSuccess ) {
					if ($this->m_aConfig['KEEP_LOCAL_BACKUP_FILE'] != 'YES') {
						$this->writeLog( "FTP copy failed AND you don't plan to keep your backups locally. Quitting." );
						break;
					}
					else {
						$this->writeLog( "FTP copy failed, so will not attempt any more FTP actions." );
					}
				}
			}
			
			if ( strtoupper($this->m_aConfig['KEEP_LOCAL_BACKUP_FILE']) == 'NO' ) {
				$this->writeLog( 'Delete Database File: '.$sDbFileName );
				unlink( dirname(__FILE__).'/'.$sDbFileName );
			}
			
		}//for
		
		if ( $fDoFtp ) {
			// close the FTP stream 
			ftp_close($this->m_oFtpConnection);
		}
		
		$this->writeLog( "** FINISH **\n" );
	}

	protected function getAllCpanelDatabases() {
		
		include_once( dirname(__FILE__).'/xmlapi-php/xmlapi.php' );
		
		$oXmlApi = new xmlapi( $this->m_aConfig['CPANEL_SERVER_ADDRESS'] );
		$oXmlApi->password_auth( $this->m_aConfig['CPANEL_ADMIN_USERNAME'], $this->m_aConfig['CPANEL_ADMIN_PASSWORD'] );
		$oXmlApi->set_port( $this->m_aConfig['CPANEL_PORT_NUM'] );
		
		$this->m_aDatabaseList = array();
		
		$oResult = $oXmlApi->api2_query( $this->m_aConfig['CPANEL_ADMIN_USERNAME'], 'MysqlFE', 'listdbs' );
		if ( !isset( $oResult->data[0] ) ) {
			$this->m_aDatabaseList[] = (string)($oResult->data->db);
		}
		else {
			foreach ( $oResult->data as $oDatabase ) {
				$this->m_aDatabaseList[] = (string)($oDatabase->db);
			}
		}
	}//getAllCpanelDatabases

	protected function loginAndDownloadFile( $insDbName, $insDbFileName ) {
		
		$sProtocol = ( $this->m_aConfig['CPANEL_PORT_NUM'] == '2083' )? 'https://' : 'http://';
		
		$sLoginUrl = $sProtocol.$this->m_aConfig['CPANEL_SERVER_ADDRESS'].':'.$this->m_aConfig['CPANEL_PORT_NUM'].'/getsqlbackup/'.$insDbName.'.sql.gz';
		
		$this->writeLog('Download URL: '.$sLoginUrl);
	
		$hOut = fopen( dirname(__FILE__).'/'.$insDbFileName, 'wb' );
		
		$oCurl = curl_init();
		curl_setopt( $oCurl, CURLOPT_HEADER,			false );
		curl_setopt( $oCurl, CURLOPT_NOBODY,			false );
		curl_setopt( $oCurl, CURLOPT_URL,				$sLoginUrl );
		curl_setopt( $oCurl, CURLOPT_SSL_VERIFYHOST,	0 );
		
		curl_setopt( $oCurl, CURLOPT_USERPWD,			$this->m_aConfig['CPANEL_ADMIN_USERNAME'].':'.$this->m_aConfig['CPANEL_ADMIN_PASSWORD'] ); 
		
		curl_setopt( $oCurl, CURLOPT_USERAGENT,			"Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
		curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER,	1 );
		curl_setopt( $oCurl, CURLOPT_SSL_VERIFYPEER,	0 );
		curl_setopt( $oCurl, CURLOPT_FOLLOWLOCATION,	1 );
		
		curl_setopt( $oCurl, CURLOPT_FILE,				$hOut );
		
		curl_exec( $oCurl );
		$sResult = curl_exec( $oCurl );
		fclose( $hOut );
		
		if ( $sResult == false ) {
			$sError = curl_error( $oCurl );
			curl_close( $oCurl );
			return $sError;
		}
		
		curl_close( $oCurl );
		
		return true;
	}

	protected function openFtpConnection() {
		
		$fSuccess = false;
		
		$sServerAddress = $this->m_aConfig['FTP_SERVER_ADDRESS'];
		$iServerPort = (int)( $this->m_aConfig['FTP_SERVER_PORT'] );

		// set up FTP connection
		if ( ($this->m_aConfig['FTP_USE_SSL'] == 'YES') ) {

			if ( function_exists('ftp_ssl_connect') ) {
				$this->m_oFtpConnection = ftp_ssl_connect( $sServerAddress, $iServerPort );
				
				if ( !$this->m_oFtpConnection ) {
					$this->writeLog( "Attempt to connect to ".$sServerAddress.":".$iServerPort." with SSL+FTP failed. Will fallback to normal FTP." );
				}
				else {
					$this->writeLog( "Attempt to connect to ".$sServerAddress.":".$iServerPort." with SSL+FTP Succeeded. Now logging in ..." );
				}
			}
			else {
				$this->writeLog( "This server doesn't support FTPS (FTP with SSL). Will fallback to normal FTP." );
			}
		}
		
		//Fallback
		if ( !$this->m_oFtpConnection ) {
			$this->m_oFtpConnection = ftp_connect( $sServerAddress, $iServerPort );
		}
	
		// login after a successful connection
		if ( $this->m_oFtpConnection ) {
			$fLoginResult = ftp_login( $this->m_oFtpConnection, $this->m_aConfig['FTP_USERNAME'], $this->m_aConfig['FTP_PASSWORD'] ); 
		}
		else {
			$this->writeLog( "Attempt to connect to ".$sServerAddress.":".$iServerPort." failed." );
		}
	
		// check connection
		if ( (!$this->m_oFtpConnection) || (!$fLoginResult) ) { 
			$this->writeLog( "FTP connection has failed!" );
		} else {
			$this->writeLog( "FTP connection was successful with ".$sServerAddress.":".$iServerPort );
			$fSuccess = true;
		}
		
		// Set to Passive connection if login was successful and this setting was set.
		if ( $fSuccess && ($this->m_aConfig['FTP_USE_PASSIVE'] == 'YES') ) {
			
			if ( ftp_pasv( $this->m_oFtpConnection, true ) ) {
				$this->writeLog( "FTP connection was set to PASSIVE mode." );
			}
			else {
				$this->writeLog( "Attempted to set FTP connection to PASSIVE mode but failed. Going to continue with copy anyway. If the script fails, review this as a possible source." );
			}
		}
		
		return $fSuccess;
		
	}//openFtpConnection
	
	protected function ftpFileRemotely( $insDbFileName ) {
	
		$fSuccess = false;
		
		$sDestinationFile = $this->m_aConfig['FTP_PATH_TO_COPY'] . $insDbFileName;
	
		// upload the file
		$fUpload = ftp_put( $this->m_oFtpConnection, $sDestinationFile, $insDbFileName, FTP_BINARY ); 
	
		// check upload status
		if (!$fUpload) { 
			$this->writeLog( "FTP upload has failed! Check the log file for errors. Try changing PASSIVE and SSL options in the config." );
			return false;
		} else {
			$this->writeLog( "Uploaded $insDbFileName to ".$this->m_aConfig['FTP_SERVER_ADDRESS']." as $sDestinationFile" );
			$fSuccess = true;
		}
	
		return $fSuccess;
	}
	
	protected function writeLog( $insLogData = '', $infWriteUser = false ) {
		echo "$insLogData\n";
	}
	
}//Worpit_Cpanel_MySql_Backup

$oBackupJob = new Worpit_Cpanel_MySql_Backup();
$oBackupJob->runBackup();
