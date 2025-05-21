<?php
require_once __DIR__.'/../init.php';

class ContactForm {
    private $db;
    private $errors = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function processSubmission($postData) {
        $this->validate($postData);
        
        if(empty($this->errors)) {
            return $this->insertContact($postData);
        }
        return false;
    }

    private function validate($data) {
        $requiredFields = ['name', 'email', 'message'];
        
        foreach($requiredFields as $field) {
            if(empty($data[$field])) {
                $this->errors[$field] = "This field is required";
            }
        }

        if(!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "Invalid email format";
        }
    }

    private function insertContact($data) {
        try {
            $sql = "INSERT INTO contacts (name, email, message) 
                    VALUES (:name, :email, :message)";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':message' => $data['message']
            ]);
            
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            $this->errors['database'] = "Submission error: " . $e->getMessage();
            error_log("Contact Form Error: " . $e->getMessage());
            return false;
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}
