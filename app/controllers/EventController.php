<?php

class EventController extends Controller {
    
    public function __construct() {
        $this->requireAuth();
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventname = trim($_POST['eventname'] ?? '');
            $startdate = $_POST['startdate'] ?? '';
            $enddate = $_POST['enddate'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $eventshortinfo = trim($_POST['eventshortinfo'] ?? '');
            $eventinfo = $_POST['eventinfo'] ?? '';
            
            // Validation
            if (empty($eventname) || empty($startdate) || empty($enddate) || empty($location)) {
                $this->setFlash('error', 'Please fill in all required fields');
                $this->view('events/create', [
                    'eventname' => $eventname,
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'location' => $location,
                    'eventshortinfo' => $eventshortinfo,
                    'eventinfo' => $eventinfo
                ]);
                return;
            }
            
            if (strtotime($startdate) >= strtotime($enddate)) {
                $this->setFlash('error', 'End date must be after start date');
                $this->view('events/create', [
                    'eventname' => $eventname,
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'location' => $location,
                    'eventshortinfo' => $eventshortinfo,
                    'eventinfo' => $eventinfo
                ]);
                return;
            }
            
            $eventModel = $this->model('Event');
            
            // Check if event name already exists
            if ($eventModel->getEventByName($eventname)) {
                $this->setFlash('error', 'An event with this name already exists');
                $this->view('events/create', [
                    'eventname' => $eventname,
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'location' => $location,
                    'eventshortinfo' => $eventshortinfo,
                    'eventinfo' => $eventinfo
                ]);
                return;
            }
            
            // Generate unique event key
            $eventkey = $this->generateEventKey();
            
            // Handle file uploads
            $eventinfopath = $this->handleEventInfoUpload($eventinfo);
            $eventbadgepath = $this->handleBadgeUpload();
            
            $uid = $this->getCurrentUserId();
            
            $eventData = [
                'eventname' => $eventname,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'location' => $location,
                'eventinfopath' => $eventinfopath,
                'eventbadgepath' => $eventbadgepath,
                'eventcreator' => $uid,
                'eventkey' => $eventkey,
                'eventshortinfo' => $eventshortinfo,
                'participantcount' => 0
            ];
            
            if ($eventModel->createEvent($eventData)) {
                $this->setFlash('success', 'Event created successfully!');
                $this->redirect('/dashboard');
            } else {
                $this->setFlash('error', 'Failed to create event');
                $this->view('events/create', [
                    'eventname' => $eventname,
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'location' => $location,
                    'eventshortinfo' => $eventshortinfo,
                    'eventinfo' => $eventinfo
                ]);
            }
        } else {
            $this->view('events/create');
        }
    }
    
    public function edit($eventId = null) {
        if (!$eventId) {
            $this->redirect('/dashboard');
        }
        
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);
        
        if (!$event) {
            $this->setFlash('error', 'Event not found');
            $this->redirect('/dashboard');
        }
        
        $uid = $this->getCurrentUserId();
        if ($event['eventcreator'] != $uid) {
            $this->setFlash('error', 'You can only edit your own events');
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventname = trim($_POST['eventname'] ?? '');
            $startdate = $_POST['startdate'] ?? '';
            $enddate = $_POST['enddate'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $eventshortinfo = trim($_POST['eventshortinfo'] ?? '');
            $eventinfo = $_POST['eventinfo'] ?? '';
            
            // Validation
            if (empty($eventname) || empty($startdate) || empty($enddate) || empty($location)) {
                $this->setFlash('error', 'Please fill in all required fields');
                $this->view('events/edit', ['event' => $event]);
                return;
            }
            
            if (strtotime($startdate) >= strtotime($enddate)) {
                $this->setFlash('error', 'End date must be after start date');
                $this->view('events/edit', ['event' => $event]);
                return;
            }
            
            // Check if event name already exists (excluding current event)
            $existingEvent = $eventModel->getEventByName($eventname);
            if ($existingEvent && $existingEvent['eventid'] != $eventId) {
                $this->setFlash('error', 'An event with this name already exists');
                $this->view('events/edit', ['event' => $event]);
                return;
            }
            
            // Handle file uploads
            $eventinfopath = $this->handleEventInfoUpload($eventinfo, $event['eventinfopath']);
            $eventbadgepath = $this->handleBadgeUpload($event['eventbadgepath']);
            
            $eventData = [
                'eventname' => $eventname,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'location' => $location,
                'eventinfopath' => $eventinfopath,
                'eventbadgepath' => $eventbadgepath,
                'eventkey' => $event['eventkey'],
                'eventshortinfo' => $eventshortinfo
            ];
            
            if ($eventModel->updateEvent($eventId, $eventData)) {
                $this->setFlash('success', 'Event updated successfully!');
                $this->redirect('/dashboard');
            } else {
                $this->setFlash('error', 'Failed to update event');
                $this->view('events/edit', ['event' => $event]);
            }
        } else {
            $this->view('events/edit', ['event' => $event]);
        }
    }
    
    public function delete($eventId = null) {
        if (!$eventId) {
            $this->redirect('/dashboard');
        }
        
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);
        
        if (!$event) {
            $this->setFlash('error', 'Event not found');
            $this->redirect('/dashboard');
        }
        
        $uid = $this->getCurrentUserId();
        if ($event['eventcreator'] != $uid) {
            $this->setFlash('error', 'You can only delete your own events');
            $this->redirect('/dashboard');
        }
        
        if ($eventModel->deleteEvent($eventId)) {
            // Delete associated files
            if (!empty($event['eventinfopath']) && file_exists($event['eventinfopath'])) {
                unlink($event['eventinfopath']);
            }
            if (!empty($event['eventbadgepath']) && file_exists($event['eventbadgepath'])) {
                unlink($event['eventbadgepath']);
            }
            
            $this->setFlash('success', 'Event deleted successfully!');
        } else {
            $this->setFlash('error', 'Failed to delete event');
        }
        
        $this->redirect('/dashboard');
    }
    
    public function manage($eventId = null) {
        if (!$eventId) {
            $this->redirect('/dashboard');
        }
        
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);
        
        if (!$event) {
            $this->setFlash('error', 'Event not found');
            $this->redirect('/dashboard');
        }
        
        $uid = $this->getCurrentUserId();
        if ($event['eventcreator'] != $uid) {
            $this->setFlash('error', 'You can only manage your own events');
            $this->redirect('/dashboard');
        }
        
        $this->view('events/manage', ['event' => $event]);
    }
    
    public function register($eventKey = null) {
        if (!$eventKey) {
            $this->redirect('/dashboard');
        }
        
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventByKey($eventKey);
        
        if (!$event) {
            $this->setFlash('error', 'Invalid event key');
            $this->redirect('/dashboard');
        }
        
        $uid = $this->getCurrentUserId();
        
        // Check if user is already registered
        $userModel = $this->model('User');
        $user = $userModel->getUserById($uid);
        $attendedEvents = json_decode($user['attendedevents'] ?? '[]', true);
        
        if (in_array($event['eventid'], $attendedEvents)) {
            $this->setFlash('error', 'You are already registered for this event');
            $this->redirect('/dashboard');
        }
        
        // Register user for event
        if ($userModel->addAttendedEvent($uid, $event['eventid'])) {
            $eventModel->incrementParticipantCount($event['eventid']);
            $this->setFlash('success', 'Successfully registered for ' . $event['eventname']);
        } else {
            $this->setFlash('error', 'Failed to register for event');
        }
        
        $this->redirect('/dashboard');
    }
    
    private function generateEventKey() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = '';
        for ($i = 0; $i < 6; $i++) {
            $key .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $key;
    }
    
    private function handleEventInfoUpload($eventinfo, $existingPath = null) {
        if (empty($eventinfo)) {
            return $existingPath;
        }
        
        $uploadDir = UPLOAD_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = 'eventinfo_' . time() . '.html';
        $filepath = $uploadDir . $filename;
        
        if (file_put_contents($filepath, $eventinfo)) {
            // Delete old file if it exists
            if ($existingPath && file_exists($existingPath)) {
                unlink($existingPath);
            }
            return $filepath;
        }
        
        return $existingPath;
    }
    
    private function handleBadgeUpload($existingPath = null) {
        if (!isset($_FILES['eventbadge']) || $_FILES['eventbadge']['error'] !== UPLOAD_ERR_OK) {
            return $existingPath;
        }
        
        $file = $_FILES['eventbadge'];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return $existingPath;
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return $existingPath;
        }
        
        $uploadDir = EVENT_BADGES_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'badge_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Delete old file if it exists
            if ($existingPath && file_exists($existingPath)) {
                unlink($existingPath);
            }
            return $filepath;
        }
        
        return $existingPath;
    }
} 