const mysql = require('mysql2/promise');
const { v4: uuidv4 } = require('uuid');
const jwtService = require('../config/jwt');

class MessagingHandler {
  constructor(io, dbPool) {
    this.io = io;
    this.db = dbPool;
    this.connectedUsers = new Map(); // uid -> {socketId, socket, lastActivity}
    this.socketToUser = new Map(); // socketId -> uid
  }

  // Handle WebSocket connection
  handleConnection(socket) {
    console.log(`Socket connected: ${socket.id}`);

    // Handle authentication
    socket.on('authenticate', async (data) => {
      try {
        const { token } = data;
        
        if (!token) {
          socket.emit('auth:error', { message: 'JWT token required' });
          return;
        }

        // Verify JWT token
        const tokenResult = jwtService.verifyToken(token);
        if (!tokenResult.valid) {
          socket.emit('auth:error', { message: 'Invalid or expired token' });
          return;
        }

        // Validate user exists and is verified
        const user = await this.validateUser(tokenResult.payload.uid);
        if (!user) {
          socket.emit('auth:error', { message: 'User not found or not verified' });
          return;
        }

        // Store user session
        socket.userId = user.uid;
        this.connectedUsers.set(user.uid, {
          socketId: socket.id,
          socket: socket,
          lastActivity: new Date(),
          user: user
        });
        this.socketToUser.set(socket.id, user.uid);

        // Save session to database
        await this.saveUserSession(user.uid, socket.id);

        // Emit successful authentication
        socket.emit('auth:success', { 
          user: {
            uid: user.uid,
            fname: user.fname,
            lname: user.lname,
            username: user.username
          }
        });

        // Broadcast user online status to their contacts
        this.broadcastUserOnline(user.uid);

        console.log(`User authenticated: ${user.fname} ${user.lname} (${user.uid})`);
      } catch (error) {
        console.error('Authentication error:', error);
        socket.emit('auth:error', { message: 'Authentication failed' });
      }
    });

    // Handle sending messages
    socket.on('message:send', async (data) => {
      try {
        if (!socket.userId) {
          socket.emit('message:error', { message: 'Not authenticated' });
          return;
        }

        const { recipientId, text, clientMessageId } = data;

        // Validate input
        if (!recipientId || !text || !clientMessageId) {
          socket.emit('message:error', { 
            message: 'Missing required fields',
            clientMessageId 
          });
          return;
        }

        // Validate message length
        if (text.length > 1000) {
          socket.emit('message:error', { 
            message: 'Message too long (max 1000 characters)',
            clientMessageId 
          });
          return;
        }

        // Sanitize message content
        const sanitizedText = text.trim();
        if (!sanitizedText) {
          socket.emit('message:error', { 
            message: 'Message cannot be empty',
            clientMessageId 
          });
          return;
        }

        // Validate recipient exists
        const recipient = await this.getUserById(recipientId);
        if (!recipient) {
          socket.emit('message:error', { 
            message: 'Recipient not found',
            clientMessageId 
          });
          return;
        }

        // Save message to database
        const messageId = await this.saveMessage(socket.userId, recipientId, sanitizedText);
        const messageData = await this.getMessageById(messageId);

        // Send confirmation to sender
        socket.emit('message:sent', {
          messageId,
          clientMessageId,
          message: messageData,
          timestamp: new Date().toISOString()
        });

        // Check if recipient is online and send message
        const recipientSession = this.connectedUsers.get(recipientId);
        if (recipientSession) {
          recipientSession.socket.emit('message:received', {
            message: messageData,
            timestamp: new Date().toISOString()
          });
        }

        console.log(`Message sent: ${socket.userId} -> ${recipientId}`);
      } catch (error) {
        console.error('Message send error:', error);
        socket.emit('message:error', { 
          message: 'Failed to send message',
          clientMessageId: data.clientMessageId 
        });
      }
    });

    // Handle typing indicators
    socket.on('typing:start', (data) => {
      if (!socket.userId) return;
      
      const { recipientId } = data;
      const recipientSession = this.connectedUsers.get(recipientId);
      
      if (recipientSession) {
        recipientSession.socket.emit('typing:start', {
          userId: socket.userId,
          user: this.connectedUsers.get(socket.userId)?.user
        });
      }
    });

    socket.on('typing:stop', (data) => {
      if (!socket.userId) return;
      
      const { recipientId } = data;
      const recipientSession = this.connectedUsers.get(recipientId);
      
      if (recipientSession) {
        recipientSession.socket.emit('typing:stop', {
          userId: socket.userId
        });
      }
    });

    // Handle message read status
    socket.on('message:mark_read', async (data) => {
      try {
        if (!socket.userId) return;
        
        const { otherUserId } = data;
        await this.markMessagesAsRead(socket.userId, otherUserId);
        
        // Notify sender that messages were read
        const senderSession = this.connectedUsers.get(otherUserId);
        if (senderSession) {
          senderSession.socket.emit('messages:read', {
            userId: socket.userId
          });
        }
      } catch (error) {
        console.error('Mark read error:', error);
      }
    });

    // Handle disconnect
    socket.on('disconnect', async () => {
      try {
        const userId = this.socketToUser.get(socket.id);
        if (userId) {
          // Remove user session
          this.connectedUsers.delete(userId);
          this.socketToUser.delete(socket.id);
          
          // Remove from database
          await this.removeUserSession(socket.id);
          
          // Broadcast user offline status
          this.broadcastUserOffline(userId);
          
          console.log(`User disconnected: ${userId}`);
        }
      } catch (error) {
        console.error('Disconnect error:', error);
      }
    });
  }

  // Database helper methods
  async validateUser(userId) {
    try {
      const [rows] = await this.db.execute(
        'SELECT uid, fname, lname, username, email FROM user_credentials WHERE uid = ? AND emailverified = 1',
        [userId]
      );
      return rows[0] || null;
    } catch (error) {
      console.error('User validation error:', error);
      return null;
    }
  }

  async getUserById(userId) {
    try {
      const [rows] = await this.db.execute(
        'SELECT uid, fname, lname, username FROM user_credentials WHERE uid = ?',
        [userId]
      );
      return rows[0] || null;
    } catch (error) {
      console.error('Get user error:', error);
      return null;
    }
  }

  async saveMessage(senderId, recipientId, messageText) {
    const messageId = uuidv4();
    await this.db.execute(
      'INSERT INTO messages (id, sender_id, recipient_id, message_text) VALUES (?, ?, ?, ?)',
      [messageId, senderId, recipientId, messageText]
    );
    return messageId;
  }

  async getMessageById(messageId) {
    try {
      const [rows] = await this.db.execute(`
        SELECT m.*, 
               s.fname as sender_fname, s.lname as sender_lname, s.username as sender_username,
               r.fname as recipient_fname, r.lname as recipient_lname, r.username as recipient_username
        FROM messages m
        JOIN user_credentials s ON m.sender_id = s.uid
        JOIN user_credentials r ON m.recipient_id = r.uid
        WHERE m.id = ?
      `, [messageId]);
      return rows[0] || null;
    } catch (error) {
      console.error('Get message error:', error);
      return null;
    }
  }

  async saveUserSession(userId, socketId) {
    try {
      // Remove existing sessions for this user
      await this.db.execute('DELETE FROM user_sessions WHERE uid = ?', [userId]);
      
      // Add new session
      await this.db.execute(
        'INSERT INTO user_sessions (uid, socket_id) VALUES (?, ?)',
        [userId, socketId]
      );
    } catch (error) {
      console.error('Save session error:', error);
    }
  }

  async removeUserSession(socketId) {
    try {
      await this.db.execute('DELETE FROM user_sessions WHERE socket_id = ?', [socketId]);
    } catch (error) {
      console.error('Remove session error:', error);
    }
  }

  async markMessagesAsRead(userId, otherUserId) {
    try {
      await this.db.execute(
        'UPDATE messages SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE recipient_id = ? AND sender_id = ? AND is_read = 0',
        [userId, otherUserId]
      );
    } catch (error) {
      console.error('Mark read error:', error);
    }
  }

  // Broadcasting methods
  async broadcastUserOnline(userId) {
    // Get user's contacts (followers and following)
    try {
      const [contacts] = await this.db.execute(`
        SELECT DISTINCT 
          CASE 
            WHEN follower_id = ? THEN followed_id 
            ELSE follower_id 
          END as contact_id
        FROM follows 
        WHERE follower_id = ? OR followed_id = ?
      `, [userId, userId, userId]);

      contacts.forEach(contact => {
        const contactSession = this.connectedUsers.get(contact.contact_id);
        if (contactSession) {
          contactSession.socket.emit('user:online', { userId });
        }
      });
    } catch (error) {
      console.error('Broadcast online error:', error);
    }
  }

  async broadcastUserOffline(userId) {
    // Get user's contacts (followers and following)
    try {
      const [contacts] = await this.db.execute(`
        SELECT DISTINCT 
          CASE 
            WHEN follower_id = ? THEN followed_id 
            ELSE follower_id 
          END as contact_id
        FROM follows 
        WHERE follower_id = ? OR followed_id = ?
      `, [userId, userId, userId]);

      contacts.forEach(contact => {
        const contactSession = this.connectedUsers.get(contact.contact_id);
        if (contactSession) {
          contactSession.socket.emit('user:offline', { userId });
        }
      });
    } catch (error) {
      console.error('Broadcast offline error:', error);
    }
  }

  // Get online users
  getOnlineUsers() {
    return Array.from(this.connectedUsers.keys());
  }

  // Check if user is online
  isUserOnline(userId) {
    return this.connectedUsers.has(userId);
  }
}

module.exports = MessagingHandler; 