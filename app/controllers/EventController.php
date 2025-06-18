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
            
            $eventData = [
                'eventname' => $eventname,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'location' => $location,
                'eventshortinfo' => $eventshortinfo,
                'eventcreator' => $_SESSION['uid'],
                'eventkey' => $eventModel->generateEventKey()
            ];
            
            $eventId = $eventModel->createEvent($eventData);
            if ($eventId) {
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
            
            $eventData = [
                'eventname' => $eventname,
                'startdate' => $startdate,
                'enddate' => $enddate,
                'location' => $location,
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
} 