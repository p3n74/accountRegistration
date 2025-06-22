const express = require('express');
const router = express.Router();

// Notification endpoint for PHP to communicate with Node.js
router.post('/notify', (req, res) => {
  try {
    const { eventId, action, data } = req.body;
    const io = req.app.get('io');

    if (!io) {
      return res.status(500).json({ error: 'WebSocket server not initialized' });
    }

    console.log(`📨 Received notification: ${action} for event ${eventId}`);

    // Handle different types of notifications
    switch (action) {
      case 'participant-added':
        io.broadcastParticipantAdded(eventId, data);
        break;
      case 'participant-removed':
        io.broadcastParticipantRemoved(eventId, data.participantId);
        break;
      case 'status-changed':
        io.broadcastStatusChanged(eventId, data.participantId, data.newStatus, data.participant);
        break;
      case 'participant-count-updated':
        io.broadcastParticipantCountUpdate(eventId, data.newCount);
        break;
      default:
        console.log(`⚠️ Unknown action: ${action}`);
    }

    res.json({ success: true, message: 'Notification sent' });
  } catch (error) {
    console.error('Error handling notification:', error);
    res.status(500).json({ error: 'Failed to process notification' });
  }
});

// Get room statistics
router.get('/stats', (req, res) => {
  try {
    const io = req.app.get('io');
    if (!io || !io.getRoomStats) {
      return res.status(500).json({ error: 'WebSocket server not initialized' });
    }

    const stats = io.getRoomStats();
    res.json(stats);
  } catch (error) {
    console.error('Error getting stats:', error);
    res.status(500).json({ error: 'Failed to get statistics' });
  }
});

module.exports = router; 