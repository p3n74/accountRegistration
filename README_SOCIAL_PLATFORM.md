# Real-Time Social Platform Implementation

## Overview

This implementation adds real-time social platform features to the existing event management system, including:

1. **User Search** - AJAX-powered search with debouncing
2. **Follow System** - Follow/unfollow users with real-time notifications
3. **Messaging System** - Real-time WebSocket-based instant messaging

## Architecture

### Backend Components

- **PHP REST API** (`app/controllers/ApiController.php`)
  - User search endpoint
  - Follow/unfollow endpoints
  - User profile endpoints with follow status

- **Database Models**
  - `Follow.php` - Manages follow relationships
  - `Message.php` - Handles messaging operations
  - Updated `User.php` - Includes search functionality

- **Node.js WebSocket Server** (`nodejs-server/`)
  - Real-time messaging handler
  - Online status tracking
  - Typing indicators
  - Follow notifications

### Database Schema

#### New Tables

1. **`follows`** - Follow relationships
   ```sql
   - id (INT, AUTO_INCREMENT, PRIMARY KEY)
   - follower_id (CHAR(36), FK to user_credentials.uid)
   - followed_id (CHAR(36), FK to user_credentials.uid)
   - created_at (TIMESTAMP)
   ```

2. **`messages`** - Chat messages
   ```sql
   - id (CHAR(36), PRIMARY KEY)
   - sender_id (CHAR(36), FK to user_credentials.uid)
   - recipient_id (CHAR(36), FK to user_credentials.uid)
   - message_text (TEXT)
   - created_at (TIMESTAMP)
   - read_at (TIMESTAMP, NULL)
   - is_read (TINYINT(1), DEFAULT 0)
   ```

3. **`user_sessions`** - WebSocket session tracking
   ```sql
   - id (INT, AUTO_INCREMENT, PRIMARY KEY)
   - uid (CHAR(36), FK to user_credentials.uid)
   - socket_id (VARCHAR(255))
   - connected_at (TIMESTAMP)
   - last_activity (TIMESTAMP)
   ```

#### Updated Tables

- **`user_credentials`** - Added social features
  - `username` (VARCHAR(50), UNIQUE, NULL)
  - `follower_count` (INT, DEFAULT 0)
  - `following_count` (INT, DEFAULT 0)

## API Endpoints

### User Search
```
GET /api/users/search?q={query}
```
- **Description**: Search users by username or full name
- **Parameters**: 
  - `q` (string, 2-50 chars): Search query
- **Response**: 
  ```json
  {
    "success": true,
    "users": [
      {
        "uid": "user-guid",
        "fname": "John",
        "lname": "Doe",
        "username": "johndoe",
        "profilepicture": "/path/to/pic.jpg",
        "follower_count": 15,
        "following_count": 23,
        "is_student": 0
      }
    ],
    "count": 1
  }
  ```

### Follow User
```
POST /api/users/{userId}/follow
```
- **Description**: Follow a user
- **Authentication**: Required (session-based)
- **Response**:
  ```json
  {
    "success": true,
    "message": "Successfully followed user",
    "follower_count": 16
  }
  ```

### Unfollow User
```
DELETE /api/users/{userId}/unfollow
```
- **Description**: Unfollow a user
- **Authentication**: Required (session-based)
- **Response**:
  ```json
  {
    "success": true,
    "message": "Successfully unfollowed user",
    "follower_count": 15
  }
  ```

### Get User Profile
```
GET /api/users/{userId}
```
- **Description**: Get user profile with follow status
- **Authentication**: Optional (affects response data)
- **Response**:
  ```json
  {
    "success": true,
    "user": {
      "uid": "user-guid",
      "fname": "John",
      "lname": "Doe",
      "username": "johndoe",
      "follower_count": 15,
      "following_count": 23,
      "is_following": true,
      "follows_you": false
    }
  }
  ```

## WebSocket Events

### Authentication
```javascript
socket.emit('authenticate', { token: 'user-token' });

// Response events:
socket.on('auth:success', (data) => {
  // data.user contains authenticated user info
});

socket.on('auth:error', (data) => {
  // data.message contains error message
});
```

### Messaging
```javascript
// Send message
socket.emit('message:send', {
  recipientId: 'recipient-user-id',
  text: 'Hello!',
  clientMessageId: 'temp-123'
});

// Listen for incoming messages
socket.on('message:received', (data) => {
  // data.message contains message details
});

// Listen for sent message confirmation
socket.on('message:sent', (data) => {
  // data.messageId, data.clientMessageId, data.message
});
```

### Typing Indicators
```javascript
// Start typing
socket.emit('typing:start', { recipientId: 'user-id' });

// Stop typing
socket.emit('typing:stop', { recipientId: 'user-id' });

// Listen for typing events
socket.on('typing:start', (data) => {
  // data.userId, data.user
});

socket.on('typing:stop', (data) => {
  // data.userId
});
```

### Online Status
```javascript
// Listen for user status changes
socket.on('user:online', (data) => {
  // data.userId
});

socket.on('user:offline', (data) => {
  // data.userId
});
```

## Frontend Integration

### HTML Demo Page
The `public/social.html` file provides a complete demo of all features:

1. **Authentication** - Connect using user token
2. **User Search** - Real-time search with 300ms debounce
3. **Follow System** - Follow/unfollow buttons in search results
4. **Messaging** - Real-time chat interface with typing indicators

### JavaScript Classes

The demo includes a `SocialPlatform` class that handles:
- WebSocket connection management
- API calls for search and follow operations
- Real-time message handling
- UI updates for typing indicators and online status

### Usage Example
```javascript
// Initialize
const socialPlatform = new SocialPlatform();

// Search users
await socialPlatform.searchUsers('john');

// Follow user
await socialPlatform.followUser('user-guid');

// Start chat
socialPlatform.startChat('user-guid', 'John Doe');
```

## Setup Instructions

1. **Install Dependencies**
   ```bash
   npm install redis ioredis  # Already done
   ```

2. **Setup Database Tables**
   ```bash
   php scripts/setup_social_tables.php
   ```

3. **Start Node.js Server**
   ```bash
   npm start  # or node nodejs-server/server.js
   ```

4. **Access Demo**
   Open `http://localhost:8000/social.html` in browser

## Security Features

- **Authentication**: All API endpoints require valid session
- **Input Validation**: Query length limits, message length validation
- **SQL Injection Protection**: Prepared statements throughout
- **XSS Prevention**: HTML entity encoding for message content
- **Rate Limiting**: WebSocket authentication required before messaging

## Performance Optimizations

- **Search Debouncing**: 300ms delay prevents excessive API calls
- **Database Indexing**: Optimized indexes on foreign keys and search fields
- **Follower Counts**: Maintained via database triggers for performance
- **Connection Pooling**: MySQL connection pool for Node.js server

## Real-Time Features

- **Instant Messaging**: Messages delivered immediately to online users
- **Typing Indicators**: Real-time typing status with auto-timeout
- **Online Status**: Live user presence tracking
- **Follow Notifications**: Real-time updates when someone follows you

## File Structure

```
app/
├── controllers/
│   └── ApiController.php          # REST API endpoints
├── models/
│   ├── Follow.php                 # Follow system model
│   ├── Message.php                # Messaging model
│   └── User.php                   # Updated with search
└── core/
    └── App.php                    # Updated routing

nodejs-server/
├── handlers/
│   └── messaging.js               # WebSocket message handler
├── config/
│   └── database.js                # Database configuration
└── server.js                      # Main server file

public/
└── social.html                    # Demo interface

storage/
└── events/
    └── follow_events.json         # Follow event tracking

scripts/
└── setup_social_tables.php       # Database setup script
```

## Testing

1. **Create Test Users**: Register multiple users in the system
2. **Get User Tokens**: Login to get authentication tokens
3. **Test Search**: Use the search functionality in the demo
4. **Test Following**: Follow/unfollow users
5. **Test Messaging**: Send real-time messages between users

## Future Enhancements

- **Redis Integration**: For production-scale pub/sub messaging
- **File Attachments**: Support for images in messages
- **Group Messaging**: Multi-user chat rooms
- **Push Notifications**: Browser notifications for offline users
- **Message Encryption**: End-to-end encryption for messages
- **User Blocking**: Block/unblock functionality
- **Message History**: Pagination for conversation history

## Troubleshooting

### Common Issues

1. **WebSocket Connection Failed**
   - Check if Node.js server is running on port 3000
   - Verify CORS settings in server configuration

2. **Authentication Errors**
   - Ensure user has valid session token
   - Check database connection in Node.js server

3. **API Endpoints Not Found**
   - Verify routing is properly configured in App.php
   - Check .htaccess configuration for API routes

4. **Database Errors**
   - Run setup script again: `php scripts/setup_social_tables.php`
   - Check database connection credentials

### Debug Mode

Enable debug logging in Node.js server by setting environment variables:
```bash
NODE_ENV=development npm start
```

This implementation provides a solid foundation for real-time social features while maintaining compatibility with the existing event management system. 