# Codebase Analysis & Migration Guide

## System Overview

This document provides a comprehensive analysis of the original PHP event management system and its migration to a modern .NET Core API with CISCO branding.

## Original PHP System Architecture

### Core Framework Structure
```
original code/
├── config/
│   └── config.php                 # Database & app configuration
├── core/                          # MVC Framework Core
│   ├── App.php                   # Main application router & auth middleware
│   ├── Controller.php            # Base controller with auth, views, flash messages
│   ├── Database.php              # MySQL connection with retry logic
│   ├── FileStorage.php           # Hybrid JSON/CSV file storage system
│   └── Model.php                 # Base model with CRUD operations
├── controllers/                   # Business Logic Layer
│   ├── AuthController.php        # Authentication & registration
│   ├── DashboardController.php   # User dashboard & profile management
│   ├── EventController.php       # Event CRUD & participant management
│   ├── EventsController.php      # Alias for EventController
│   ├── HomeController.php        # Root redirect logic
│   └── UsersController.php       # User search functionality
├── models/                        # Data Access Layer
│   ├── Event.php                 # Event management with hybrid storage
│   ├── ExistingStudent.php       # Student verification system
│   ├── Program.php               # Academic program management
│   └── User.php                  # User management with GUID system
└── views/                         # Presentation Layer
    ├── auth/                     # Authentication pages
    ├── dashboard/                # User dashboard pages
    ├── events/                   # Event management pages
    └── shared/                   # Layout templates
```

### Key Features & Functionality

#### Authentication System
- **Email verification** required for login
- **Student validation** against existing student database
- **Password reset** with token expiration
- **Session management** with 30-minute timeout
- **BCrypt password hashing**

#### Event Management
- **CRUD operations** for events
- **Participant management** with status tracking
- **File upload** support for event documentation
- **Attendance tracking** (Invited→Pending→Paid→Attended→Absent→Awaiting Verification)
- **Event key system** for registration

#### Hybrid Storage System
- **MySQL** for structured data (users, events, core relationships)
- **JSON files** for flexible event metadata
- **CSV files** for participant data with dynamic fields
- **File uploads** for event photos and documentation

#### Student Integration
- **Existing student lookup** by email
- **Auto-population** of student data during registration
- **Department-managed accounts** with locked profile fields
- **Program association** for targeted content

## New .NET Core API System

### Project Structure
```
Accounts.Data/                     # Data Access Layer
├── Models/                       # EF Core Entity Models
│   ├── AccountsDbContext.cs      # Main database context
│   ├── UserCredentials.cs        # User entity
│   ├── Events.cs                 # Event entity
│   ├── EventParticipants.cs      # Participant tracking
│   ├── Channels.cs               # Communication channels
│   ├── Messages.cs               # Channel messaging
│   ├── Notifications.cs          # System notifications
│   ├── ExistingStudentInfo.cs    # Student verification
│   └── ProgramList.cs            # Academic programs
└── FileStorage/
    └── FileStorage.cs            # File-based storage system

Accounts.Api/                      # Web API Layer
├── Controllers/                  # API Endpoints
│   ├── AuthController.cs         # Authentication API
│   ├── EventsController.cs       # Event management API
│   ├── ChannelsController.cs     # Communication API
│   └── MessagesController.cs     # Messaging API
├── Services/
│   └── EmailService.cs           # Email notifications
├── DTOs/                         # Data Transfer Objects
│   ├── AuthDtos.cs              # Authentication contracts
│   ├── EventDtos.cs             # Event management contracts
│   ├── ChannelDtos.cs           # Communication contracts
│   └── MessageDtos.cs           # Messaging contracts
├── Pages/                        # Razor Pages (Web Interface)
│   ├── Auth/                    # Authentication pages
│   ├── Dashboard/               # User dashboard
│   ├── Events/                  # Event management
│   └── Shared/                  # Layout templates
└── wwwroot/                     # Static assets
```

## Migration Mapping

### Controllers Translation
| PHP Controller | .NET Controller | Functionality |
|---------------|----------------|---------------|
| `AuthController.php` | `AuthController.cs` | Registration, login, email verification, password reset |
| `EventController.php` | `EventsController.cs` | Event CRUD, participant management, file uploads |
| `DashboardController.php` | Razor Pages `/Dashboard` | User profile, password change, picture upload |
| `UsersController.php` | Integrated into other controllers | User search functionality |

### Models Translation
| PHP Model | .NET Entity | Storage Method |
|-----------|-------------|----------------|
| `User.php` | `UserCredentials.cs` | Entity Framework |
| `Event.php` | `Events.cs` + `FileStorage.cs` | Hybrid (EF + Files) |
| `ExistingStudent.php` | `ExistingStudentInfo.cs` | Entity Framework |
| `Program.php` | `ProgramList.cs` | Entity Framework |

### Key Enhancements in .NET Version
1. **Modern Architecture**: Clean separation with DI container
2. **Type Safety**: Strong typing with C# and DTOs
3. **API-First**: RESTful APIs with OpenAPI documentation
4. **Email Service**: Professional email templates with CISCO branding
5. **Enhanced Security**: Built-in .NET security features
6. **Communication System**: Channels and messaging capabilities
7. **Notification System**: Structured notification management

## CISCO Branding Integration

### Color Palette
- **Primary Blue**: `#4A90E2` - Buttons, links, primary brand elements
- **Dark Blue**: `#2E5BBA` - Headers, emphasis, hover states
- **White**: `#FFFFFF` - Backgrounds, text on dark surfaces
- **Black**: `#1A1A1A` - Body text, dark backgrounds
- **Light Gray**: `#F5F5F5` - Subtle backgrounds, dividers
- **Medium Gray**: `#9CA3AF` - Secondary text, placeholders

### Design Principles
- **Professional appearance** with USC branding
- **Clean, modern interface** with rounded corners and shadows
- **Consistent typography** with clear hierarchy
- **Accessible color combinations** for readability
- **Responsive design** for all device types

## Migration Strategy

### Phase 1: Core Infrastructure
1. ✅ Set up .NET Core API project structure
2. ✅ Configure Entity Framework with MySQL
3. ✅ Implement hybrid storage system
4. ✅ Create base authentication system

### Phase 2: API Development
1. ✅ Implement authentication endpoints
2. ✅ Create event management APIs
3. ✅ Add participant management
4. ✅ Integrate email service

### Phase 3: Web Interface (Next Steps)
1. 🔄 Create Razor Pages with CISCO branding
2. 🔄 Implement dashboard functionality
3. 🔄 Add event management interface
4. 🔄 Integrate file upload capabilities

### Phase 4: Advanced Features (Future)
1. 📋 Channel-based communication system
2. 📋 Real-time notifications
3. 📋 Advanced reporting and analytics
4. 📋 Mobile application support

## Technical Specifications

### Database Schema
- **MySQL/MariaDB** compatible
- **GUID-based primary keys** for scalability
- **Hybrid storage** combining relational and file-based data
- **Student integration** with existing institutional data

### Authentication & Security
- **BCrypt password hashing**
- **Email verification** workflow
- **Token-based authentication**
- **Session management**
- **Student account validation**

### File Management
- **JSON storage** for flexible event metadata
- **CSV storage** for participant data
- **File uploads** for event documentation
- **Organized directory structure**

### Communication Features
- **Professional email templates** with USC branding
- **SMTP integration** via Gmail
- **Channel-based messaging** (new feature)
- **Notification system** (new feature)

## Development Guidelines

### Code Standards
- **Clean Architecture** principles
- **Dependency Injection** for loose coupling
- **Repository Pattern** for data access
- **DTO Pattern** for API contracts
- **Async/Await** for scalability

### UI/UX Guidelines
- **CISCO branding** consistency
- **Responsive design** principles
- **Accessibility** compliance
- **Professional appearance**
- **Intuitive navigation**

This analysis serves as the foundation for understanding both systems and guiding the migration process while maintaining functionality and improving the user experience with modern CISCO branding. 