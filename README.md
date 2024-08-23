![PHP Version](https://img.shields.io/badge/php-8.3.6-purple)
![PHPUnit](https://img.shields.io/badge/PHPUnit-^11.3-yellow)

# PHP Input Validation Library

A Simple PHP input validation to ensure data integrity and security in web applications.
This library provides set of rules for validating various types of input, and also
prevents XSS, and File Upload Vulnerabilites by sanitizing and validation.

## Installation via composer
```
composer require dachiiii/validation
```

## Usage

to validate input data, several steps have to be taken, creating FormValidationClass, set rules, and validate.
For example, if you want to validate Add Product Form, you must create **regular class file** in your project,
and write rules in it, for example:

```php
<?php
    // your\namespace    if necessarily
    use Validation\Validator;

    class ProductFormValidation extends Validator {
        protected array $categories = ['Books','Clothing','Software','Other'];
        protected array $mimes = ['image/png','image/jpeg'];

        public function __construct() {  
            $this->initRules();
        }

        private function initRules() {
            $this->rules['productName'] = "string|required|max:40";
            $this->rules['productDescription'] = "string|min:80|max:3000";
            $this->rules['productCategory'] = "string|required|in:categories";
            $this->rules['productIsAvailable'] = "bool";
            $this->rules['productPrice'] = "integer|min:0";
            $this->rules['productImage'] = "file|mime:mimes";
        }
    }
```

it is recommended to put these files into the directory, so it may look something like this:

```
FormValidations/
├── AccountFormValidation.php
├── CommentFormValidation.php
└── ProductFormValidation.php
```

### File Validation

```php
$this->rules['file'] = "file";
```

This code validates if input is file, even though this is correct, always use **mime:types** with it,
because if it's validated without mime types, it means any kind of file will pass validation, and
website will become vulnerable to file upload vulnerabilites. Safest way to validate files is:

```php
protected array $valid_mime_types = ['video/mp4','image/png','image/gif','image/jpeg',...];
...
$this->rules['file'] = "file|mime:valid_mime_types";
```

## Final Validation

```php
$validator = new ProductFormValidation();
if ($validator->validate($to_validate, $validator->rules)) {
    // Validation Pass
} else {
    // Validation Failed
    $errors = $validator->getErrors();
}
```
