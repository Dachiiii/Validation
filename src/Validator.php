<?php

namespace Validation;

class Validator {
	private array $errors = [];
	public array $rules = [];

	/**
	* Validate the data based on the given rules.
	*
	* @param array $data 	The data to validate.
	* @param array $rules	The validation rules.
	* @return bool True if validation passes, otherwise false.
	*/

	public function validate(array $data, array $rules): bool {
		foreach ($rules as $field => $rule) {
			$value = $data[$field] ?? null;
			$this->applyRules($field, $value, $rule);
		}
		return empty($this->errors);
	}

	/**
	* Apply a set of rules to a field's value. 
	*
	* @param string $field The field name.
	* @param mixed $value The value to validate.
	* @param string|array $rule The validation rules. 
	*/

	private function applyRules(string $field, $value, $rule) {
		$rules = is_array($rule) ? $rule : explode('|', $rule);
		if (!in_array('required', $rules) && (empty($value) || is_null($value))) {
			return;
		} else {
			foreach ($rules as $rule) {
				$this->applyRule($field, $value, $rule);
			}
		}
	}

	/**
	* Apply a single validation rule.
	*
	* @param string $field The field name.
	* @param mixed $value The value to validate.
	* @param string $rule The rule to apply.
	*/

	private function applyRule(string $field, $value, string $rule) {
		if (strpos($rule,":") !== false) {
			list($ruleName, $param) = explode(":", $rule, 2);
		} else {
			$ruleName = $rule;
			$param = null;
		}
		$methodName = "validate".ucfirst($ruleName);
		if (method_exists($this,$methodName)) {
			if (!is_array($value))
				$value = htmlentities($value, ENT_QUOTES);
			$this->$methodName($field,$value,$param);
		} else {
			throw new \Exception("Validation Rule {$ruleName} not defined.");
		}
	}

	/**
	*  Add an error to the list of errors.
	* 
	* @param string $field The field name.
	* @param string $message The error message.
	**/
	private function addError(string $field, string $message){
		$this->errors[$field][] = $message;
	}

	/**
	* Get all validation errors
	* @return array The errors. 
	*/
	public function getErrors(): array {
		return $this->errors;
	}

	private function validateString(string $field, $value) {
		if (!is_string($value)) $this->addError($field,"The {$field} field must be a string.");
	}

	/**
	 *  Validate max length limit on both string and integer
	 *  @param string $field field name
	 * 	@param mixed $value value
	 *  @param $max max limit
	*/
	private function validateMax(string $field, $value, $max) {
		if (is_string($value) && !(is_numeric($value)) && strlen($value) > $max)
			$this->addError($field, "The {$field} field must not exceed {$max} characters.");
		elseif (is_numeric($value) && $value > $max)
			$this->addError($field, "The {$field} field must not exceed {$max}.");
	}

	/**
	 *  Validate min length limit on both string and integer
	 *  @param string $field field name
	 * 	@param mixed $value value
	 *  @param $min minimum limit
	*/

	private function validateMin(string $field, $value, $min) {
		if (is_string($value) && !(is_numeric($value)) && strlen($value) < $min)
			$this->addError($field, "The {$field} field must be minimum {$min} characters.");
		elseif (is_numeric($value) && (int)$value < $min)
			$this->addError($field, "The {$field} field must be minimum {$min}.");	
	}

	/**
	 * Checks if field is empty and gives error if value is required
	 * @param string $field field name
	 * @param $value field value
	 *
	*/
	public function validateRequired(string $field, $value) {
		if (empty($value)) $this->addError($field, "The {$field} field is required.");
	}

	/**
	 * Validates Email Address
	 * @param string $field
	 * @param string $value
	*/
	private function validateEmail(string $field, string $value) {
		if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$this->addError($field, "The {$field} field must be a valid email address.");
		}
	}


	/**
	*
	* Validates integer value
	* @param string $field 	field name
	* @param mixed $value 	field value
	*/
	private function validateInteger(string $field, $value) {
		if (!filter_var($value, FILTER_VALIDATE_INT)) {
			$this->addError($field, "The {$field} field must be an integer.");
		}
	}

	/**
	 *	Validates if the input value is in the array		 
	 *  For example: if you expect value to the someone's name and that
	 *  name should match any of these names: ['John','David'], you can
	 *  use this method.
	 *	usage:
	 * 		protected array $names = ['John','David'];
	 * 		$this->rules['name'] = "..|in:names|.."
	 */

	private function validateIn(string $field, $value, $in) {
		$message = "The {$field} field is invalid.";
		if (!in_array($value,$this->$in))
			$this->addError($field, $message);
	}

	/**
	* Validates file. it only determines if any kind of file types has been uploaded.
	*
	* @param string $field 		 field name
	* @param array $file 		 file value
	*/
	private function validateFile(string $field, array $file) {
		$file['name'] = htmlentities($file['name'], ENT_QUOTES);
		if (!is_uploaded_file($file['tmp_name']) || !file_exists($file['tmp_name']))
			$this->addError($field, "Missing {$field} field.");
	}

	/**
	* Validates the mime type of file,
	* and if the content|extension of the file corresponds to that type
	*
	* @param string $field  field name
	* @param array $file 	file input
	* @param string $mime_types   allowed mime types
	*/
	private function validateMime(string $field, array $file, string $mime_types) {
		$mime = $file['type'];
		$message = "Invalid File Type";
		if (in_array($mime,$this->$mime_types)) {
			$expected_extension = $this->validateMimeWithExtension($mime);
			if (!str_ends_with($file['name'], $expected_extension)) {
				$this->addError($field, $message);
			}
		} else
			$this->addError($field, $message);
	}

	/**
	* Validate Boolean Field
	* @param strign $field field name
	* @param $value field value
	*/
	private function validateBool(string $field, $value) {
		$acceptable = [true,false,0,1,'0','1','on','off'];
		if (!in_array($value, $acceptable))
			$this->addError($field,"$field field is invalid.");
	}

	/**
	* Validates if file's mimetype matches file extension
	* THESE ARE BASIC FILE EXTENSIONS, YOU CAN ALWAYS ADD MORE.
	*
	* @param string $mime 	mimetype
	* @return returns extension corresponds to its mime type
	*/
	private function validateMimeWithExtension(string $mime) {
		switch ($mime) {
			case 'image/gif'			: $extension = '.gif';		break;
			case 'image/png'			: $extension = '.png';		break;
			case 'image/jpeg'			: $extension = '.jpg';		break;
			case 'image/apng'			: $extension = '.apng';		break;
			case 'image/webp'			: $extension = '.webp'; 	break;
			case 'image/jpe'			: $extension = '.jpe';		break;
			case 'image/svg+xml'		: $extension = '.svg';		break;
			case 'image/pdf'			: $extension = '.tpdf';		break;
			case 'image/svg+xml'		: $extension = '.svg';		break;
			case 'text/plain'			: $extension = '.txt';		break;
			case 'text/csv'				: $extension = '.csv';		break;
			case 'application/epub+zip' : $extension = '.epub'; 	break;
			case 'application/gzip'		: $extension = '.gz';		break;
			case 'text/html'			: $extension = '.htm';		break;
			case 'text/html'			: $extension = '.html';		break;
			case 'application/json'		: $extension = '.json';		break;
			case 'application/ld+json' 	: $extension = '.jsonld';	break;
			case 'application/pdf'		: $extension = '.pdf';		break;
			case 'audio/mpeg'			: $extension = '.mp3';		break;
			case 'video/mp4'			: $extension = '.mp4';		break;
			case 'video/mpeg'			: $extension = '.mpeg';		break;
			default 					: $extension = null;		break;
		}
		return $extension;
	}

}
