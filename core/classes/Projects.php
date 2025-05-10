<?php
class Projects {
    public $id;
    public $name;
    public $description;
    public $image;
    public $status;
    public $created_at;
    
    public static function getAll() {
        global $db;
        $result = $db->select("SELECT * FROM projects ORDER BY created_at DESC");
        
        $projects = [];
        foreach ($result as $row) {
            $projects[] = self::createFromArray($row);
        }
        
        return $projects;
    }
    
    public static function getById($id) {
        global $db;
        $result = $db->select("SELECT * FROM projects WHERE id = ?", [$id]);
        
        if (count($result) === 0) {
            return null;
        }
        
        return self::createFromArray($result[0]);
    }
    
    public static function create($name, $description, $image, $status = 'active') {
        global $db;
        
        $id = $db->insert('projects', [
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return self::getById($id);
    }
    
    public function update($data) {
        global $db;
        
        $db->update('projects', $data, 'id = ?', [$this->id]);
        
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    public function delete() {
        global $db;
        $db->delete('projects', 'id = ?', [$this->id]);
    }
    
    private static function createFromArray($data) {
        $project = new Project();
        $project->id = $data['id'];
        $project->name = $data['name'];
        $project->description = $data['description'];
        $project->image = $data['image'];
        $project->status = $data['status'];
        $project->created_at = $data['created_at'];
        
        return $project;
    }
}