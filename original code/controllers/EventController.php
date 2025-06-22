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
        
        // Prevent deletion if protected participants exist
        $participants = $eventModel->getEventParticipants($eventId);
        foreach($participants as $par){
            if(in_array($par['attendance_status'],[2,3,5])){
                $this->setFlash('error','Cannot delete event: participants have paid/attended or awaiting verification.');
                $this->redirect("/events/manage/{$eventId}");
            }
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
    
    public function uploadPhotos($eventId = null) {
        if (!$eventId) {
            $this->redirect('/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photos'])) {
            $this->setFlash('error', 'No photos uploaded');
            $this->redirect("/events/manage/{$eventId}");
        }

        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);

        if (!$event) {
            $this->setFlash('error', 'Event not found');
            $this->redirect('/dashboard');
        }

        // Authorize that the current user owns the event
        $uid = $this->getCurrentUserId();
        if ($event['eventcreator'] !== $uid) {
            $this->setFlash('error', 'You can only upload documentation to your own events');
            $this->redirect('/dashboard');
        }

        require_once '../app/core/FileStorage.php';
        $fs = new FileStorage();

        $uploaded = 0;
        foreach ($_FILES['photos']['tmp_name'] as $index => $tmpPath) {
            if ($_FILES['photos']['error'][$index] !== UPLOAD_ERR_OK) continue;

            $originalName = $_FILES['photos']['name'][$index];
            $resultPath = $fs->addEventPhoto($eventId, $tmpPath, $originalName);
            if ($resultPath) {
                $uploaded++;
            }
        }

        if ($uploaded === 0) {
            $this->setFlash('error', 'No photos were uploaded (size/type/limit issues?)');
        } else {
            $this->setFlash('success', "{$uploaded} photo(s) uploaded successfully");
        }
        $this->redirect("/events/manage/{$eventId}");
    }

    public function addParticipant($eventId = null) {
        // Expect JSON POST {uid: "..."}
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$eventId) {
            $eventId = $payload['event_id'] ?? null;
        }
        if (!$eventId) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);
        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            return;
        }
        // Authorize
        $currentUid = $this->getCurrentUserId();
        if ($event['eventcreator'] !== $currentUid) {
            echo json_encode(['success' => false, 'message' => 'Not allowed']);
            return;
        }
        $attendanceStatus = isset($payload['status']) ? intval($payload['status']) : 1;
        $pid = $eventModel->addParticipant($eventId, $payload['uid'] ?? null, $payload['email'] ?? null, $attendanceStatus);
        if($pid){
            echo json_encode(['success'=>true,'participant_id'=>$pid,'joined_at'=>date('Y-m-d H:i:s')]);
        }else{
            echo json_encode(['success'=>false]);
        }
        exit;
    }

    public function removeParticipant($eventId = null) {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$eventId) {
            $eventId = $payload['event_id'] ?? null;
        }
        if (!$eventId) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);
        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            return;
        }
        $currentUid = $this->getCurrentUserId();
        if ($event['eventcreator'] !== $currentUid) {
            echo json_encode(['success' => false, 'message' => 'Not allowed']);
            return;
        }
        $removed = $eventModel->removeParticipant($eventId, $payload['participant_id'] ?? null, $payload['email'] ?? null);
        echo json_encode(['success' => $removed]);
        exit;
    }

    public function updateParticipantStatus($eventId = null) {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$eventId) { $eventId = $payload['event_id'] ?? null; }
        if (!$eventId) { echo json_encode(['success' => false, 'message' => 'Invalid event']); return; }

        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);
        if (!$event) { echo json_encode(['success' => false, 'message' => 'Event not found']); return; }

        $currentUid = $this->getCurrentUserId();
        if ($event['eventcreator'] !== $currentUid) { echo json_encode(['success' => false, 'message' => 'Not allowed']); return; }

        $status = isset($payload['status']) ? intval($payload['status']) : null;
        if ($status === null) { echo json_encode(['success' => false, 'message' => 'Status required']); return; }
        $participantId = $payload['participant_id'] ?? null;
        $email = $payload['email'] ?? null;

        $ok = $eventModel->updateAttendanceStatus($eventId, $participantId, $email, $status);
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function participants($eventId = null){
        header('Content-Type: application/json; charset=utf-8');
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? intval($_GET['status']) : null;
        if(!$eventId){ echo json_encode(['success'=>false,'message'=>'Invalid event']); exit; }
        $eventModel = $this->model('Event');
        $event = $eventModel->getEventById($eventId);
        if(!$event){ echo json_encode(['success'=>false,'message'=>'Not found']); exit; }
        // owner check optional? allow anyone who can view manage page
        $participants = $eventModel->getEventParticipants($eventId,$status);
        echo json_encode(['success'=>true,'participants'=>$participants]);
        exit;
    }
} 