<?php

namespace Validation\Validate;
use Validation\Validator;

class FormValidationExample extends Validator {
	public array $rules = [];
	protected array $valid_names = ['Paul','Larry','Scott','Samuel'];
	protected array $valid_mime_types = ['image/jpeg','image/png'];

	public function __construct() {
		$this->initRules();
	}
	private function initRules() {
		$this->rules['name'] = "string|max:30|min:4|required|in:valid_names";
		$this->rules['password'] = 'string|max:32|min:8|required';
		$this->rules['age'] = 'integer|required|min:18|max:80';
		$this->rules['email'] = 'email|required';
		$this->rules['file'] = 'file|required|mime:valid_mime_types';
	}
	
}