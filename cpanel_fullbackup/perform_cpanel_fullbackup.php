<?php
/**
 * Copyright (c) 2012 Host Like Toast <helpdesk@hostliketoast.com>
 * All rights reserved.
 * 
 * "Perform Full cPanel Account Backup" is distributed under the GNU General Public License, Version 2,
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

/**
 * 
 * Script Name: Perform FULL cPanel account Backup
 * Description: Allows you to perform an automated (using cron?) backup of a cPanel web hosting account. 
 * Version: v2.0
 * Author: Host Like Toast
 * Author URI: http://www.hostliketoast.com/
 * Notes: To configure, please read and edit accompanying config.php file
 * 
 * CHANGELOG
 *  [2012-09-30] v2.0:	New Release built on new class structure.
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set("error_log" , dirname(__FILE__)."/cpanel_full_backup.error_log.txt");

// Maximum script execution time in seconds, default is 600 (10 minutes).
// If you have really large databases, you may need to increase this value substantially.
set_time_limit( 600 );

include_once( dirname(__FILE__).'/xmlapi-php/xmlapi.php' );
include_once( dirname(__FILE__).'/worpit_cpanel_base.php' );

class Worpit_Cpanel_Full_Backup extends Worpit_Cpanel_Base {
	
	public function __construct() {
		
		parent::__construct();
		
		$this->m_aConfigKeys = array(
				
				'CPANEL_SERVER_ADDRESS',
				'CPANEL_PORT_NUM',
				'CPANEL_ADMIN_USERNAME',
				'CPANEL_ADMIN_PASSWORD',
				
				'COPY_METHOD',
				'FTP_NOTIFY_EMAIL',
				
				'FTP_SERVER_ADDRESS',
				'FTP_SERVER_PORT',
				'FTP_USERNAME',
				'FTP_PASSWORD',
				'FTP_PATH_TO_COPY',
		);

	}//__construct
	
	public function runBackup() {
		
		$sTimeStamp = date( "Y-m-d-H-i-s" );
		
		$this->writeLog( "** START @ $sTimeStamp **" );
		
		// 1. Read the config
		if ( !$this->readConfig() ) {
			$this->writeLog( "No valid configuration read. Quitting." );
			return;
		}
		
		// 2. Prepare FTP copy arguments
		$aFtpArgs = array(
				$this->m_aConfig['COPY_METHOD'],
				$this->m_aConfig['FTP_SERVER_ADDRESS'],
				$this->m_aConfig['FTP_USERNAME'],
				$this->m_aConfig['FTP_PASSWORD'],
				$this->m_aConfig['FTP_NOTIFY_EMAIL'],
				$this->m_aConfig['FTP_SERVER_PORT'],
				$this->m_aConfig['FTP_PATH_TO_COPY'],
		);
		
		// 3. Run the backup
		$this->runCpanelBackup( $this->m_aConfig['CPANEL_ADMIN_USERNAME'], $aFtpArgs );
		
		$this->writeLog( "** FINISH **\n" );
		$this->printLog();
	}

	/**
	 * 
	 * @param $insUsername
	 * @param $inaFtpArgs
	 * @return boolean
	 */
	protected function runCpanelBackup( $insUsername = '', $inaFtpArgs = null ) {
		
		$fSuccess = false;
		
		$this->m_oXmlApi = new xmlapi( $this->m_aConfig['CPANEL_SERVER_ADDRESS'] );
		$this->m_oXmlApi->password_auth( $this->m_aConfig['CPANEL_ADMIN_USERNAME'], $this->m_aConfig['CPANEL_ADMIN_PASSWORD'] );
		$this->m_oXmlApi->set_port( $this->m_aConfig['CPANEL_PORT_NUM'] );
		
		//This order counts
		if ( empty($inaFtpArgs) ) {
			$aFtpArgs = array(
					$this->m_aConfig['COPY_METHOD'],
					$this->m_aConfig['FTP_SERVER_ADDRESS'],
					$this->m_aConfig['FTP_USERNAME'],
					$this->m_aConfig['FTP_PASSWORD'],
					$this->m_aConfig['FTP_NOTIFY_EMAIL'],
					$this->m_aConfig['FTP_SERVER_PORT'],
					$this->m_aConfig['FTP_PATH_TO_COPY'],
			);
		}
		else {
			$aFtpArgs = $inaFtpArgs;
		}
		
		// Runs the xmlapi query that performs the full backup and FTP
		$this->m_oApiResult = $this->m_oXmlApi->api1_query( $insUsername, 'Fileman', 'fullbackup', $aFtpArgs );
		
		if ( is_null($this->m_oApiResult) || isset( $this->m_oApiResult['error'] ) ) {
			$this->writeLog( "Backup failed with error: ".$this->m_oApiResult['error'] );
		}
		elseif ( $this->m_oApiResult->event->result == 1 ) {
			$this->writeLog( "Success - Backup has STARTED. Please wait while the backup is performed and the FTP attempted (if this option was set)." );
			$this->writeLog( "This can take a while depending on how much data is being backed-up and to where you're copying it to.
			If the FTP transfer fails for whatever reason, the backup file may be left at the source server." );
			$fSuccess = true;
		}
		
		return $fSuccess;
		
	}//runCpanelBackup
	
}//Worpit_Cpanel_Full_Backup

$oBackupJob = new Worpit_Cpanel_Full_Backup();
$oBackupJob->runBackup();
