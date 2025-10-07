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
            'email' => 'Email',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'school' => 'School',
            'class' => 'Grade/Year',
            'birthdate' => 'Birthdate',
            'phone' => 'Phone',
            'description' => 'Coding Skills/Superpowers'
        ];

        foreach($requiredFields as $field => $label) {
            if(empty($data[$field])) {
                $this->errors[$field] = "$label is required";
            }
        }

        if(!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "Invalid email format";
        }

        if(!empty($data['phone']) && !preg_match('/^[\d\s\+\-\(\)]+$/', $data['phone'])) {
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
                'description' => $data['description'],
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
