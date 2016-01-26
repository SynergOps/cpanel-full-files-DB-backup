<?php
/**
 * Copyright (c) 2012 Host Like Toast <helpdesk@hostliketoast.com>
 * All rights reserved.
 * 
 * "Worpit_Cpanel_Base" is distributed under the GNU General Public License, Version 2,
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
 * Worpit_Cpanel_Base Version 2.0 (2012/10/01)
 */

class Worpit_Cpanel_Base {
	
	const CONFIG_FILE = 'config.php';
	
	protected $m_aConfigKeys;
	protected $m_oApiResult;
	protected $m_oXmlApi;
	protected $m_sLog;
	
	public function __construct() {
		
		$this->m_aConfig = array();
		$this->m_aConfigKeys = array();
		$this->clearLog();
		
		$this->writeLog( 'Process ID: '.getmypid() );
		
	}//__construct
	
	protected function readConfig() {
	
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

	protected function isConfigValid() {
		return true;
	}
	
	protected function clearLog() {
		$this->m_sLog = "";
	}
	
	protected function writeLog( $insLogData = '' ) {
		$this->m_sLog .= "$insLogData\n";
	}
	
	protected function printLog( $infHtml = null ) {
	
		$fPrintLogWithHtml = true;
		if ( is_null($infHtml) && PHP_SAPI == 'cli' ) {
			$fPrintLogWithHtml = false;
		}
		
		if ( $fPrintLogWithHtml ) {
			$aLines = preg_split( "/((\r?\n)|(\r\n?))/", $this->m_sLog) ;
			foreach($aLines as $line) {
				echo "<p>$line</p>";
			}
		}
		else {
			echo $this->m_sLog;
		}
	}
}