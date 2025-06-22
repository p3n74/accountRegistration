const db = require('../config/database');

const connectedUsers = new Map();
const eventRooms = new Map();

function websocketHandler(io) {
  db.testConnection();

  io.on('connection', (socket) => {
    console.log(`👤 User connected: ${socket.id}`);

    socket.on('join-event-room', async (data) => {
      try {
        const { eventId, userId, userRole } = data;
        
        if (!eventId) {
          socket.emit('error', { message: 'Event ID is required' });
          return;
        }

        const event = await db.getEventDetails(eventId);
        if (!event) {
          socket.emit('error', { message: 'Event not found' });
          return;
        }

        socket.join(`event-${eventId}`);
        
        connectedUsers.set(socket.id, {
          userId,
          eventId,
          userRole,
          joinedAt: new Date()
        });

        if (!eventRooms.has(eventId)) {
          eventRooms.set(eventId, new Set());
        }
        eventRooms.get(eventId).add(socket.id);

        console.log(`📍 User ${socket.id} joined event room: ${eventId}`);
        
        const participants = await db.getEventParticipants(eventId);
        socket.emit('participants-data', {
          eventId,
          participants,
          participantCount: participants.length
        });

      } catch (error) {
        console.error('Error joining event room:', error);
        socket.emit('error', { message: 'Failed to join event room' });
      }
    });

    socket.on('disconnect', () => {
      console.log(`👋 User disconnected: ${socket.id}`);
      
      const userInfo = connectedUsers.get(socket.id);
      if (userInfo && userInfo.eventId) {
        if (eventRooms.has(userInfo.eventId)) {
          eventRooms.get(userInfo.eventId).delete(socket.id);
          if (eventRooms.get(userInfo.eventId).size === 0) {
            eventRooms.delete(userInfo.eventId);
          }
        }
      }
      
      connectedUsers.delete(socket.id);
    });
  });

  io.broadcastParticipantAdded = (eventId, participantData) => {
    io.to(`event-${eventId}`).emit('participant-added', {
      eventId,
      participant: participantData,
      timestamp: new Date()
    });
    console.log(`📢 Broadcasted participant added to event ${eventId}`);
  };

  io.broadcastParticipantRemoved = (eventId, participantId) => {
    io.to(`event-${eventId}`).emit('participant-removed', {
      eventId,
      participantId,
      timestamp: new Date()
    });
    console.log(`📢 Broadcasted participant removed from event ${eventId}`);
  };

  io.broadcastStatusChanged = (eventId, participantId, newStatus) => {
    io.to(`event-${eventId}`).emit('participant-status-changed', {
      eventId,
      participantId,
      newStatus,
      timestamp: new Date()
    });
    console.log(`📢 Broadcasted status change for participant ${participantId}`);
  };

  io.getRoomStats = () => {
    return {
      totalConnections: connectedUsers.size,
      activeRooms: eventRooms.size
    };
  };
}

module.exports = websocketHandler; 