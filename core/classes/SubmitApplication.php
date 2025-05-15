<?php
class SubmitApp {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function handleSubmission($postData) {
        $required = ['first_name', 'last_name', 'email', 'school', 'grade', 'age'];
        foreach ($required as $field) {
            if (empty($postData[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        $appData = [
            'first_name' => htmlspecialchars($postData['first_name']),
            'last_name' => htmlspecialchars($postData['last_name']),
            'email' => filter_var($postData['email'], FILTER_SANITIZE_EMAIL),
            'school' => htmlspecialchars($postData['school']),
            'class' => htmlspecialchars($postData['grade']),
            'age' => intval($postData['age']),
            'phone' => htmlspecialchars($postData['phone'] ?? ''),
            'birthdate' => $postData['birthdate'],
            'superpowers' => $postData['superpowers']
        ];

        try {
            return $this->db->insert('applications', $appData);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
