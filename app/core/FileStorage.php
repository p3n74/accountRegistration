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
        $filePath = $this->storageRoot . "events/{$eventId}.json";
        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    public function getEventData($eventId) {
        $filePath = $this->storageRoot . "events/{$eventId}.json";
        if (!file_exists($filePath)) {
            return null;
        }
        $content = file_get_contents($filePath);
        return json_decode($content, true);
    }

    public function saveEventParticipants($eventId, $participants) {
        $filePath = $this->storageRoot . "participants/{$eventId}.json";
        return file_put_contents($filePath, json_encode($participants, JSON_PRETTY_PRINT)) !== false;
    }

    public function getEventParticipants($eventId) {
        $filePath = $this->storageRoot . "participants/{$eventId}.json";
        if (!file_exists($filePath)) {
            return [];
        }
        $content = file_get_contents($filePath);
        return json_decode($content, true) ?: [];
    }

    public function addParticipantToEvent($eventId, $uid, $userData = []) {
        $participants = $this->getEventParticipants($eventId);
        $participants[$uid] = array_merge([
            'uid' => $uid,
            'joined_at' => date('Y-m-d H:i:s'),
            'status' => 'registered'
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
        $eventsDir = $this->storageRoot . 'participants/';
        $userEvents = [];
        
        if (!is_dir($eventsDir)) {
            return $userEvents;
        }

        $files = glob($eventsDir . '*.json');
        foreach ($files as $file) {
            $eventId = basename($file, '.json');
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
        $eventsDir = $this->storageRoot . 'events/';
        $events = [];
        
        if (!is_dir($eventsDir)) {
            return $events;
        }

        $files = glob($eventsDir . '*.json');
        foreach ($files as $file) {
            $eventId = basename($file, '.json');
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
        $eventFile = $this->storageRoot . "events/{$eventId}.json";
        $participantsFile = $this->storageRoot . "participants/{$eventId}.json";
        
        $success = true;
        if (file_exists($eventFile)) {
            $success = $success && unlink($eventFile);
        }
        if (file_exists($participantsFile)) {
            $success = $success && unlink($participantsFile);
        }
        
        return $success;
    }
} 