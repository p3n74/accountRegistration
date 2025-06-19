<?php

class FileStorage {
    private $storageRoot;

    public function __construct() {
        $this->storageRoot = dirname(dirname(__DIR__)) . '/storage/';
        $this->ensureDirectoriesExist();
    }

    private function ensureDirectoriesExist() {
        $directories = ['users', 'events', 'participants'];
        foreach ($directories as $dir) {
            $path = $this->storageRoot . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    public function saveUserData($uid, $data) {
        $filePath = $this->storageRoot . "users/{$uid}.json";
        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    public function getUserData($uid) {
        $filePath = $this->storageRoot . "users/{$uid}.json";
        if (!file_exists($filePath)) {
            return null;
        }
        $content = file_get_contents($filePath);
        return json_decode($content, true);
    }

    public function saveEventData($eventId, $data) {
        $filePath = $this->getEventDir($eventId) . "meta.json";
        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    public function getEventData($eventId) {
        // New preferred location inside the event folder
        $filePath = $this->getEventDir($eventId) . "meta.json";
        if (!file_exists($filePath)) {
            // Fallback to legacy flat-file path for backward compatibility
            $legacyPath = $this->storageRoot . "events/{$eventId}.json";
            if (!file_exists($legacyPath)) {
                return null;
            }
            $filePath = $legacyPath;
        }
        $content = file_get_contents($filePath);
        return json_decode($content, true);
    }

    public function saveEventParticipants($eventId, $participants) {
        $filePath = $this->getEventDir($eventId) . "participants.csv";
        if (empty($participants)) {
            // No participants left – remove the file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return true;
        }
        $rows = [];
        foreach ($participants as $uid => $data) {
            $rows[] = array_merge(['uid' => $uid], $data);
        }
        return $this->writeCsv($filePath, $rows);
    }

    public function getEventParticipants($eventId) {
        $filePath = $this->getEventDir($eventId) . "participants.csv";
        $rows = $this->readCsv($filePath);
        $participants = [];
        foreach ($rows as $row) {
            $uid = $row['uid'];
            unset($row['uid']);
            $participants[$uid] = $row;
        }
        return $participants;
    }

    public function addParticipantToEvent($eventId, $uid, $userData = []) {
        $participants = $this->getEventParticipants($eventId);
        $participants[$uid] = array_merge([
            'name'        => $userData['name'] ?? '',
            'email'       => $userData['email'] ?? '',
            'profile_picture' => $userData['profile_picture'] ?? '',
            'joined_at'   => date('Y-m-d H:i:s'),
            'status'      => 'registered'
        ], $userData);
        
        return $this->saveEventParticipants($eventId, $participants);
    }

    public function removeParticipantFromEvent($eventId, $uid) {
        $participants = $this->getEventParticipants($eventId);
        unset($participants[$uid]);
        return $this->saveEventParticipants($eventId, $participants);
    }

    public function isUserParticipant($eventId, $uid) {
        $participants = $this->getEventParticipants($eventId);
        return isset($participants[$uid]);
    }

    public function getUserEvents($uid) {
        $eventsRoot = $this->storageRoot . 'events/';
        $paths = glob($eventsRoot . '*/participants.csv');
        $userEvents = [];
        foreach ($paths as $csvPath) {
            $eventId = basename(dirname($csvPath));
            $participants = $this->getEventParticipants($eventId);
            if (isset($participants[$uid])) {
                $userEvents[] = [
                    'event_id' => $eventId,
                    'participation_data' => $participants[$uid]
                ];
            }
        }
        return $userEvents;
    }

    public function listAllUsers() {
        $usersDir = $this->storageRoot . 'users/';
        $users = [];
        
        if (!is_dir($usersDir)) {
            return $users;
        }

        $files = glob($usersDir . '*.json');
        foreach ($files as $file) {
            $uid = basename($file, '.json');
            $userData = $this->getUserData($uid);
            if ($userData) {
                $users[$uid] = $userData;
            }
        }
        
        return $users;
    }

    public function listAllEvents() {
        $eventsRoot = $this->storageRoot . 'events/';
        $events = [];

        if (!is_dir($eventsRoot)) {
            return $events;
        }

        // Legacy flat JSONs
        foreach (glob($eventsRoot . '*.json') as $jsonFile) {
            $eventId = basename($jsonFile, '.json');
            $eventData = $this->getEventData($eventId);
            if ($eventData) {
                $events[$eventId] = $eventData;
            }
        }

        // New folder-based events
        foreach (glob($eventsRoot . '*/meta.json') as $metaFile) {
            $eventId = basename(dirname($metaFile));
            $eventData = $this->getEventData($eventId);
            if ($eventData) {
                $events[$eventId] = $eventData;
            }
        }

        return $events;
    }

    public function deleteUserData($uid) {
        $filePath = $this->storageRoot . "users/{$uid}.json";
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }

    public function deleteEventData($eventId) {
        // Remove the entire event directory (including photos, csv, meta)
        $eventDir = $this->getEventDir($eventId);
        if (is_dir($eventDir)) {
            $this->rrmdir($eventDir);
        }
        // Legacy cleanup
        $legacyFile = $this->storageRoot . "events/{$eventId}.json";
        if (file_exists($legacyFile)) {
            unlink($legacyFile);
        }
        $legacyParticipants = $this->storageRoot . "participants/{$eventId}.json";
        if (file_exists($legacyParticipants)) {
            unlink($legacyParticipants);
        }
        return true;
    }

    private function getEventDir($eventId) {
        $dir = $this->storageRoot . "events/{$eventId}/";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function getEventPhotosDir($eventId) {
        $dir = $this->getEventDir($eventId) . 'photos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /* ---------------------------------- CSV Helpers ---------------------------------- */
    private function writeCsv(string $file, array $rows): bool {
        if (empty($rows)) return false;
        $fp = fopen($file, 'w');
        if (!$fp) return false;
        flock($fp, LOCK_EX);
        // header from keys of first row
        fputcsv($fp, array_keys($rows[0]), ',', '"', '\\');
        foreach ($rows as $row) {
            fputcsv($fp, $row, ',', '"', '\\');
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    private function readCsv(string $file): array {
        if (!file_exists($file)) return [];
        $rows = [];
        if (($fp = fopen($file, 'r')) !== false) {
            $header = fgetcsv($fp, 0, ',', '"', '\\');
            if ($header === false) { fclose($fp); return []; }
            while (($data = fgetcsv($fp, 0, ',', '"', '\\')) !== false) {
                $rows[] = array_combine($header, $data);
            }
            fclose($fp);
        }
        return $rows;
    }

    // --------------------------- Photo Management ---------------------------
    public function addEventPhoto(string $eventId, string $tmpPath, string $originalName): ?string {
        $maxSize = 10 * 1024 * 1024; // 10 MB
        if (filesize($tmpPath) > $maxSize) {
            return null; // file too large
        }
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return null; // invalid type
        }
        $photosDir = $this->getEventPhotosDir($eventId);
        $existing = glob($photosDir . '*');
        if (count($existing) >= 5) {
            return null; // limit reached
        }
        $filename = uniqid('photo_', true) . '.' . $ext;
        $dest = $photosDir . $filename;
        if (move_uploaded_file($tmpPath, $dest)) {
            return $dest;
        }
        return null;
    }

    public function listEventPhotos(string $eventId): array {
        $photosDir = $this->getEventPhotosDir($eventId);
        $files = glob($photosDir . '*');
        return array_map('basename', $files);
    }

    // Recursively remove a directory (helper for deleteEventData)
    private function rrmdir($dir) {
        if (!is_dir($dir)) return;
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object == "." || $object == "..") continue;
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
} 