class ProjectsModule {
    public function __construct() {
        $this->registerRoutes();
    }

    private function registerRoutes() {
        Pages::getInstance()->add('Projects', '/projects', 'pages/projects.php', 'Projects Module');
    }

    public function getProjectData($id) {
        return DB::getInstance()->query('SELECT * FROM projects p 
            JOIN page_projects pp ON p.id = pp.project_id 
            WHERE p.id = ?', [$id])->first();
    }
}

// Initialize module
new ProjectsModule();
