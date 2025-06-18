# Event Management System - Modern Architecture

## Overview
This event management system now uses a **hybrid architecture** combining traditional database storage with modern file-based storage for scalability and future-proofing.

## Architecture Components

### 1. Database Layer (MySQL/MariaDB)
**Primary Tables:**
- `user_credentials` - Core user authentication and profile data
- `events` - Event metadata and basic information

**Deprecated Tables:**
- `event_participants` - Being phased out in favor of file storage

### 2. File Storage Layer
**Directory Structure:**
```
storage/
├── users/           # Individual user data files (uid.json)
├── events/          # Event details and settings (eventid.json)
└── participants/    # Event participation data (eventid.json)
```

**File Storage Benefits:**
- **Scalability**: No database row limits for participants
- **Performance**: Faster read/write for large participant lists
- **Flexibility**: Easy to add custom fields without schema changes
- **Backup**: Simple file-based backups and replication
- **Migration**: Easy to move to cloud storage (S3, etc.)

### 3. Hybrid Data Flow

#### User Registration/Login
1. **Database**: Stores credentials, basic profile
2. **File Storage**: Syncs extended user data automatically

#### Event Creation
1. **Database**: Stores core event metadata
2. **File Storage**: Stores detailed event configuration and settings

#### Event Participation
1. **File Storage**: Primary storage for participant lists
2. **Database**: Maintains participant count for quick queries

## Key Classes

### FileStorage
Central class managing all file operations:
```php
$fileStorage = new FileStorage();

// User operations
$fileStorage->saveUserData($uid, $userData);
$fileStorage->getUserData($uid);

// Event operations
$fileStorage->saveEventData($eventId, $eventData);
$fileStorage->getEventData($eventId);

// Participant operations
$fileStorage->addParticipantToEvent($eventId, $uid, $userData);
$fileStorage->getEventParticipants($eventId);
```

### Updated Models

#### User Model
- **Backward Compatible**: All existing methods work
- **Auto-Sync**: Database changes automatically sync to files
- **New Methods**: `addAttendedEvent()`, `getAttendedEvents()` use file storage

#### Event Model
- **Enhanced**: Participant management through file storage
- **Backward Compatible**: Existing event queries work
- **New Features**: Advanced participant tracking, event settings

## Migration Path

### Current State
- Database-first approach with JSON fields
- Limited scalability for large events
- Complex queries for participant data

### Target State
- File-first approach for variable data
- Database for core relational data
- Infinite scalability for participants

### Migration Script
Run the migration script to move existing data:
```bash
php scripts/migrate_to_file_storage.php
```

## Benefits of New Architecture

1. **Performance**: 
   - Faster participant list loading
   - Reduced database query complexity
   - Better caching possibilities

2. **Scalability**:
   - No limits on participant count
   - Easy horizontal scaling
   - Cloud storage ready

3. **Flexibility**:
   - Add custom participant fields without schema changes
   - Event-specific settings and configurations
   - Easy data export/import

4. **Maintainability**:
   - Clear separation of concerns
   - Easier debugging and monitoring
   - Simple backup strategies

## Development Guidelines

### Adding New Features

#### For User Data:
1. Add to database if it's core authentication data
2. Add to file storage if it's extended profile data
3. Update `User::syncUserToFile()` method

#### For Event Data:
1. Add to database if it's used in queries/joins
2. Add to file storage if it's configuration/settings
3. Update `Event::syncEventToFile()` method

#### For Participant Data:
1. Always use file storage for new participant fields
2. Use `FileStorage::addParticipantToEvent()` for registration
3. Update database participant count for quick access

### Best Practices

1. **Always sync**: Database changes should sync to files
2. **Graceful degradation**: Handle missing files gracefully
3. **Atomic operations**: Use transactions for critical operations
4. **Validation**: Validate data before saving to files
5. **Monitoring**: Log file operations for debugging

## Future Enhancements

1. **Cloud Storage**: Move to AWS S3 or similar
2. **Caching**: Add Redis for frequently accessed data
3. **Search**: Implement Elasticsearch for advanced search
4. **Analytics**: Add event analytics and reporting
5. **Real-time**: WebSocket integration for live updates

## Troubleshooting

### Common Issues

1. **File Permission Errors**:
   ```bash
   chmod -R 755 storage/
   chown -R www-data:www-data storage/
   ```

2. **Missing Files**:
   - Check if migration script was run
   - Verify file paths in FileStorage class
   - Check directory permissions

3. **Data Inconsistency**:
   - Run migration script again
   - Check sync methods in models
   - Verify file JSON structure

### Monitoring

Monitor these directories for health:
- `storage/users/` - User data files
- `storage/events/` - Event configuration files  
- `storage/participants/` - Participation data

## Security Considerations

1. **File Permissions**: Ensure proper permissions on storage directories
2. **Data Validation**: Validate all data before saving to files
3. **Access Control**: Implement proper access control for file operations
4. **Backup Security**: Encrypt backups of sensitive data files
5. **Audit Trail**: Log all file operations for security auditing 