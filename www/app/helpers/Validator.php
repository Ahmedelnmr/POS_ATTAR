<?php
/**
 * Validator Helper
 * Simple validation for form data
 */

class Validator {
    private $errors = [];
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Require a field to be present and non-empty
     */
    public function required($field, $label = null) {
        $label = $label ?: $field;
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = "$label مطلوب";
        }
        return $this;
    }

    /**
     * Validate numeric field
     */
    public function numeric($field, $label = null) {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "$label يجب أن يكون رقم";
        }
        return $this;
    }

    /**
     * Validate minimum value
     */
    public function min($field, $min, $label = null) {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field] = "$label يجب أن يكون $min على الأقل";
        }
        return $this;
    }

    /**
     * Validate max length
     */
    public function maxLength($field, $max, $label = null) {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = "$label يجب أن لا يتجاوز $max حرف";
        }
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Get sanitized value
     */
    public static function sanitize($value) {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    /**
     * Get sanitized input array
     */
    public static function sanitizeAll($data) {
        $clean = [];
        foreach ($data as $key => $value) {
            $clean[$key] = self::sanitize($value);
        }
        return $clean;
    }
}
