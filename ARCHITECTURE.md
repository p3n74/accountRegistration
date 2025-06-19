# Event Management System - Modern Architecture

## Overview
This event management system uses a **hybrid architecture** combining traditional database storage with modern file-based storage for scalability and future-proofing. The system includes advanced **student management integration** with university systems for automated account management.

## Architecture Components

### 1. Database Layer (MySQL/MariaDB)
**Primary Tables:**
- `user_credentials` - Core user authentication and profile data
  - Added `is_student` field for university integration
- `events` - Event metadata and basic information
- `existing_student_info` - University student directory integration

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

### 3. Student Management Integration
**University System Integration:**
- **Automated Student Detection**: During registration, email addresses are checked against `existing_student_info`
- **Name Field Locking**: Student accounts have university-managed name restrictions
- **Account Flagging**: Student accounts are automatically marked with `is_student = 1`
- **Profile Restrictions**: Students cannot modify name information through the UI

**Integration Flow:**
1. **Registration Check**: Email validated against university student database
2. **Auto-Population**: Student names pre-filled from university records
3. **Account Creation**: User account created with `is_student` flag
4. **Profile Locking**: Name fields become non-editable for students

### 4. Hybrid Data Flow

#### User Registration/Login
1. **Student Check**: Email validated against `existing_student_info` table
2. **Database**: Stores credentials, basic profile with student flag
3. **File Storage**: Syncs extended user data automatically
4. **Access Control**: Student accounts have restricted profile editing

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

### ExistingStudent Model
New model for university integration:
```php
$existingStudentModel = new ExistingStudent();

// Check if email exists in university system
$student = $existingStudentModel->getStudentByEmail($email);
$exists = $existingStudentModel->studentExists($email);
```

### Updated Models

#### User Model
- **Backward Compatible**: All existing methods work
- **Auto-Sync**: Database changes automatically sync to files
- **New Methods**: `addAttendedEvent()`, `getAttendedEvents()` use file storage
- **Student Support**: Enhanced `createUser()` with `is_student` field handling
- **Profile Management**: Updated `getUserById()` includes student status

#### AuthController
- **Student Detection**: `checkExistingStudent()` AJAX endpoint for real-time validation
- **Enhanced Registration**: Automatic student flagging and name pre-population
- **Data Integrity**: Server-side validation ensures student data consistency

#### DashboardController
- **Access Control**: Student profile restrictions in `profile()` method
- **UI Logic**: Conditional form handling based on student status

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
   - Real-time student validation via AJAX

2. **Scalability**:
   - No limits on participant count
   - Easy horizontal scaling
   - Cloud storage ready
   - University system integration scales automatically

3. **Flexibility**:
   - Add custom participant fields without schema changes
   - Event-specific settings and configurations
   - Easy data export/import
   - Seamless university data integration

4. **Maintainability**:
   - Clear separation of concerns
   - Easier debugging and monitoring
   - Simple backup strategies
   - University data sync automation

5. **Security & Compliance**:
   - University-managed student data integrity
   - Automated access control based on student status
   - Server-side validation prevents data tampering
   - Clear audit trail for profile modifications

## Development Guidelines

### Adding New Features

#### For User Data:
1. Add to database if it's core authentication data
2. Add to file storage if it's extended profile data
3. Update `User::syncUserToFile()` method
4. Consider student access restrictions for new fields

#### For Event Data:
1. Add to database if it's used in queries/joins
2. Add to file storage if it's configuration/settings
3. Update `Event::syncEventToFile()` method

#### For Participant Data:
1. Always use file storage for new participant fields
2. Use `FileStorage::addParticipantToEvent()` for registration
3. Update database participant count for quick access

#### For Student Integration:
1. Add university data to `existing_student_info` table
2. Use `ExistingStudent` model for university system queries
3. Implement access control logic in controllers
4. Add UI restrictions in views based on student status

### Best Practices

1. **Always sync**: Database changes should sync to files
2. **Graceful degradation**: Handle missing files gracefully
3. **Atomic operations**: Use transactions for critical operations
4. **Validation**: Validate data before saving to files
5. **Monitoring**: Log file operations for debugging
6. **Student Data Integrity**: 
   - Always check student status before allowing profile modifications
   - Use university data as single source of truth for student names
   - Implement both client-side and server-side restrictions
7. **AJAX Security**: Validate all AJAX endpoints and ensure proper authentication
8. **University Integration**:
   - Keep `existing_student_info` table synchronized with university systems
   - Handle edge cases where student data might be missing or outdated

## Future Enhancements

1. **Cloud Storage**: Move to AWS S3 or similar
2. **Caching**: Add Redis for frequently accessed data
3. **Search**: Implement Elasticsearch for advanced search
4. **Analytics**: Add event analytics and reporting
5. **Real-time**: WebSocket integration for live updates
6. **University Integration**:
   - Automated sync with university student information systems
   - LDAP/Active Directory integration for authentication
   - Bulk student data import/export tools
   - Advanced student status management (enrollment, graduation, etc.)
7. **Enhanced Student Features**:
   - Student ID validation and verification
   - Academic year and program tracking
   - Department-specific event filtering
   - Student organization management

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

4. **Student Data Issues**:
   - Verify `existing_student_info` table is populated
   - Check email format consistency between tables
   - Ensure `is_student` field exists in `user_credentials`
   - Run `add_is_student_field.sql` if needed

5. **AJAX Endpoint Errors**:
   - Verify `/auth/checkExistingStudent` route is accessible
   - Check browser network tab for CORS or 404 errors
   - Ensure proper JSON response format
   - Validate authentication for AJAX calls

6. **Profile Editing Issues**:
   - Check user's `is_student` flag in database
   - Verify controller logic for student restrictions
   - Ensure view properly handles student status
   - Test both client-side and server-side restrictions

### Monitoring

Monitor these directories for health:
- `storage/users/` - User data files
- `storage/events/` - Event configuration files  
- `storage/participants/` - Participation data

Monitor these database tables for student integration:
- `user_credentials.is_student` - Student flag consistency
- `existing_student_info` - University data freshness
- Authentication logs for student registration patterns

## Security Considerations

1. **File Permissions**: Ensure proper permissions on storage directories
2. **Data Validation**: Validate all data before saving to files
3. **Access Control**: Implement proper access control for file operations
4. **Backup Security**: Encrypt backups of sensitive data files
5. **Audit Trail**: Log all file operations for security auditing
6. **Student Data Protection**:
   - University student data is treated as sensitive information
   - Name modifications restricted at both UI and API levels
   - AJAX endpoints properly validate student status
   - Profile restrictions cannot be bypassed through direct API calls
7. **Authentication & Authorization**:
   - Student status checks require authenticated sessions
   - University data access limited to authorized processes
   - Input validation on all student-related endpoints
   - Secure handling of university email validation 