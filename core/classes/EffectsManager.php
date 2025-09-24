<?php

class EffectsManager
{
    private $db;

    public function __construct($database = null)
    {
        global $db;
        $this->db = $database ?: $db;
    }

    /**
     * Get effects for a specific page
     */
    public function getPageEffects($pageName)
    {
        try {
            $stmt = $this->db->prepare("SELECT effects FROM pages WHERE name = ?");
            $stmt->execute([$pageName]);
            $result = $stmt->fetch();

            if ($result && !empty($result['effects'])) {
                $effects = json_decode($result['effects'], true);
                return is_array($effects) ? $effects : [];
            }

            return [];
        } catch (Exception $e) {
            error_log("EffectsManager error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Render effects for a page
     */
    public function renderPageEffects($pageName)
    {
        $effects = $this->getPageEffects($pageName);

        foreach ($effects as $effect) {
            $this->renderEffect($effect);
        }
    }

    /**
     * Render a specific effect
     */
    public function renderEffect($effectName)
    {
        $effectPath = ROOT_DIR . "/components/effects/{$effectName}.php";

        if (file_exists($effectPath)) {
            include $effectPath;
        } else {
            error_log("Effect file not found: {$effectPath}");
        }
    }

    /**
     * Check if an effect is enabled for a page
     */
    public function isEffectEnabled($pageName, $effectName)
    {
        $effects = $this->getPageEffects($pageName);
        return in_array($effectName, $effects);
    }

    /**
     * Get all available effects
     */
    public function getAvailableEffects()
    {
        $effectsDir = ROOT_DIR . "/components/effects/";
        $effects = [];

        if (is_dir($effectsDir)) {
            $files = scandir($effectsDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $effectName = pathinfo($file, PATHINFO_FILENAME);
                    $effects[] = $effectName;
                }
            }
        }

        return $effects;
    }

    /**
     * Update effects for a page
     */
    public function updatePageEffects($pageName, $effects)
    {
        try {
            $effectsJson = json_encode(array_values($effects));
            $stmt = $this->db->prepare("UPDATE pages SET effects = ? WHERE name = ?");
            $stmt->execute([$effectsJson, $pageName]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating page effects: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Migrate hardcoded effects to database
     */
    public function migrateHardcodedEffects()
    {
        $hardcodedEffects = [
            'index' => ['mouse', 'globe', 'grid'],
            'members' => ['mouse', 'grid'],
            'apply' => ['mouse', 'net', 'grid'],
            'contact' => ['mouse', 'grid', 'birds'],
        ];

        $results = [];

        foreach ($hardcodedEffects as $pageName => $effects) {
            if ($this->updatePageEffects($pageName, $effects)) {
                $results[] = "Page '{$pageName}' migrated with effects: " . (empty($effects) ? 'none' : implode(', ', $effects));
            } else {
                $results[] = "Failed to migrate page '{$pageName}'";
            }
        }

        return $results;
    }
}
