<?php

class Event extends Model {
    protected $table = 'events';

    public function getEventById($eventId) {
        $sql = "SELECT * FROM {$this->table} WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEventsByCreator($creatorId) {
        $sql = "SELECT eventid, eventname, startdate, enddate, location FROM {$this->table} WHERE eventcreator = ? ORDER BY eventid DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $creatorId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createEvent($data) {
        $sql = "INSERT INTO {$this->table} (eventname, startdate, enddate, location, eventinfopath, eventbadgepath, eventcreator, eventkey, eventshortinfo, participantcount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssssissi", 
            $data['eventname'],
            $data['startdate'],
            $data['enddate'],
            $data['location'],
            $data['eventinfopath'],
            $data['eventbadgepath'],
            $data['eventcreator'],
            $data['eventkey'],
            $data['eventshortinfo'],
            $data['participantcount']
        );
        return $stmt->execute();
    }

    public function updateEvent($eventId, $data) {
        $sql = "UPDATE {$this->table} SET eventname = ?, startdate = ?, enddate = ?, location = ?, eventkey = ?, eventshortinfo = ?, eventbadgepath = ?, eventinfopath = ? WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssssssi", 
            $data['eventname'],
            $data['startdate'],
            $data['enddate'],
            $data['location'],
            $data['eventkey'],
            $data['eventshortinfo'],
            $data['eventbadgepath'],
            $data['eventinfopath'],
            $eventId
        );
        return $stmt->execute();
    }

    public function deleteEvent($eventId) {
        $sql = "DELETE FROM {$this->table} WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        return $stmt->execute();
    }

    public function getEventByName($eventName) {
        $sql = "SELECT * FROM {$this->table} WHERE eventname = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $eventName);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEventByKey($eventKey) {
        $sql = "SELECT * FROM {$this->table} WHERE eventkey = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $eventKey);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function incrementParticipantCount($eventId) {
        $sql = "UPDATE {$this->table} SET participantcount = participantcount + 1 WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        return $stmt->execute();
    }

    public function decrementParticipantCount($eventId) {
        $sql = "UPDATE {$this->table} SET participantcount = GREATEST(participantcount - 1, 0) WHERE eventid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $eventId);
        return $stmt->execute();
    }
} 