<?php

class User {
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $discord_id;
    public $slack_id;
    public $github_username;
    public $school;
    public $ysws_projects;
    public $hcb_member;
    public $birthdate;
    public $class;
    public $phone;
    public $role;
    public $join_date;
    public $description;
    public $active_member;
    public $profile_image;
    public $bio;
    public $profile_public;
    public $discord_avatar;

    private $db;

    public function __construct($db = null) {
        $this->db = $db;
    }

    public static function getById($id) {
        global $db;
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) {
            $user = new self($db);
            foreach ($data as $key => $value) {
                if (property_exists($user, $key)) {
                    $user->$key = $value;
                }
            }
            return $user;
        }
        return null;
    }
}
