<?php
/**
 * Data received from a UserDefinedForm submission
 *
 * @package userforms
 */

class SubmittedFormField extends DataObject {
	
	private static $db = array(
		"Name" => "Varchar",
		"Value" => "Text",
		"Title" => "Varchar(255)"
	);
	
	private static $has_one = array(
		"Parent" => "SubmittedForm"
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'fetchFieldValue' => 'Value'
	);
	
	/**
	 * Encryption key to use during encryption and decryption
	*/
	protected $key = "d0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282";
	
	// Decrypt Function
	public function mc_decrypt(){
	    $decrypt = explode('|', $this->Value.'|');
	    $decoded = base64_decode($decrypt[0]);
	    $iv = base64_decode($decrypt[1]);
	    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
	    $key = pack('H*', $this->key);
	    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
	    $mac = substr($decrypted, -64);
	    $decrypted = substr($decrypted, 0, -64);
	    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
	    if($calcmac!==$mac){ return false; }
	    $decrypted = unserialize($decrypted);
	    return $decrypted;
	}
	
	public function fetchFieldValue() {
		
		//If encryption is enabled on the form this field belongs to, return the decrypted value
		$val = ( $this->Parent()->Parent()->EnableFieldEncryption) ? $this->mc_decrypt() : $this->Value;
		return $val;

	}
	
	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canCreate($member = null) {
		return $this->Parent()->canCreate();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canView($member = null) {
		return $this->Parent()->canView();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canEdit($member = null) {
		return $this->Parent()->canEdit();
	}

	/**
	 * @param Member
	 *
	 * @return boolean
	 */
	public function canDelete($member = null) {
		return $this->Parent()->canDelete();
	}

	/**
	 * Generate a formatted value for the reports and email notifications.
	 * Converts new lines (which are stored in the database text field) as
	 * <brs> so they will output as newlines in the reports
	 *
	 * @return string
	 */
	public function getFormattedValue() {
		return nl2br($this->dbObject('Value')->ATT());
	}
	
	/**
	 * Return the value of this submitted form field suitable for inclusion
	 * into the CSV
	 *
	 * @return Text
	 */
	public function getExportValue() {
		return $this->Value;
	}

	/**
	 * Find equivalent editable field for this submission.
	 *
	 * Note the field may have been modified or deleted from the original form
	 * so this may not always return the data you expect. If you need to save
	 * a particular state of editable form field at time of submission, copy 
	 * that value to the submission.
	 *
	 * @return EditableFormField
	 */
	public function getEditableField() {
		return $this->Parent()->Parent()->Fields()->filter(array(
			'Name' => $this->Name
		))->First();
	}
}
