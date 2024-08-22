<?php


use Validation\Validator;
use PHPUnit\Framework\TestCase;

class FormValidationTest extends TestCase {

	public function testRequiredField() {
		$validator = new Validator();
		$data = ['name' => ''];
		$rules = ['name' => 'required'];
		$this->assertFalse($validator->validate($data, $rules));
		$this->assertArrayHasKey('name',$validator->getErrors());
	}

	public function testMinMaxField() {
		$validator = new Validator();
		$data = ['age' => 19, 'name' => 'David'];
		$rules = [
			'age' => 'required|min:18|max:80',
			'name' => 'required|min:4',
		];
		$validator->validate($data, $rules);
		$this->assertArrayNotHasKey('name',$validator->getErrors());
	}

	public function testNotRequired() {
		$validator = new Validator();
		$data = ['name' => ''];
		$rules = ['name' => 'string|max:50'];
		$validator->validate($data, $rules);
		$this->assertArrayNotHasKey('name',$validator->getErrors());
	}
}