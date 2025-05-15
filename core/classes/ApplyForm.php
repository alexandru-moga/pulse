<?php
require_once __DIR__.'/../init.php';

class ApplyForm {
    private $db;
    private $errors = [];

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function processSubmission(array $data) {
        $this->validate($data);
        
        if(empty($this->errors)) {
            return $this->saveApplication($data);
        }
        return false;
    }

    private function validate(array $data) {
        $requiredFields = [
            'email', 'first_name', 'last_name', 
            'school', 'class', 'birthdate', 'phone', 'superpowers'
        ];

        foreach($requiredFields as $field) {
            if(empty($data[$field])) {
                $this->errors[$field] = "This field is required";
            }
        }

        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "Invalid email format";
        }

        if(!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
            $this->errors['phone'] = "Invalid phone number format";
        }
    }

    private function saveApplication(array $data) {
        try {
            $applicationData = [
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'school' => $data['school'],
                'class' => $data['class'],
                'birthdate' => $data['birthdate'],
                'phone' => $data['phone'],
                'superpowers' => $data['superpowers'],
                'student_id' => $data['student_id'] ?? null,
                'discord_username' => $data['discord_username'] ?? null
            ];

            return $this->db->insert('applications', $applicationData);
        } catch(PDOException $e) {
            $this->errors['database'] = "Submission error: " . $e->getMessage();
            error_log("Application Error: " . $e->getMessage());
            return false;
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}
