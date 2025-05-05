<?php
class User {
    public $id;
    public $username;
    public $email;
    public $role;
    public $created_at;
    
    public static function getById($id) {
        global $db;
        $result = $db->select("SELECT * FROM users WHERE id = ?", [$id]);
        
        if (count($result) === 0) {
            return null;
        }
        
        return self::createFromArray($result[0]);
    }
    
    public static function getByEmail($email) {
        global $db;
        $result = $db->select("SELECT * FROM users WHERE email = ?", [$email]);
        
        if (count($result) === 0) {
            return null;
        }
        
        return self::createFromArray($result[0]);
    }
    
    public static function create($username, $email, $password, $role = 'member') {
        global $db;
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $id = $db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return self::getById($id);
    }
    
    public function update($data) {
        global $db;
        
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $db->update('users', $data, 'id = ?', [$this->id]);
        
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    public static function login($email, $password) {
        $user = self::getByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        global $db;
        $result = $db->select("SELECT password FROM users WHERE id = ?", [$user->id]);
        
        if (password_verify($password, $result[0]['password'])) {
            $_SESSION['user_id'] = $user->id;
            return $user;
        }
        
        return false;
    }
    
    public static function logout() {
        unset($_SESSION['user_id']);
        session_destroy();
    }
    
    private static function createFromArray($data) {
        $user = new User();
        $user->id = $data['id'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->created_at = $data['created_at'];
        
        return $user;
    }
}