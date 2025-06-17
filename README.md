# Event Management System - MVC Framework

This is a PHP-based Event Management System built using a custom MVC (Model-View-Controller) framework with Tailwind CSS for styling.

## Project Structure

```
accounts/
├── app/
│   ├── config/
│   │   └── config.php          # Application configuration
│   ├── controllers/
│   │   ├── AuthController.php  # Authentication controller
│   │   ├── DashboardController.php # Dashboard controller
│   │   └── EventController.php # Event management controller
│   ├── core/
│   │   ├── App.php            # Main application class
│   │   ├── Controller.php     # Base controller class
│   │   ├── Database.php       # Database connection class
│   │   └── Model.php          # Base model class
│   ├── models/
│   │   ├── User.php           # User model
│   │   └── Event.php          # Event model
│   └── views/
│       ├── auth/
│       │   ├── login.php      # Login view
│       │   └── register.php   # Registration view
│       ├── dashboard/
│       │   └── index.php      # Dashboard view
│       ├── events/
│       │   └── create.php     # Event creation view
│       └── shared/
│           └── layout.php     # Main layout template
├── public/
│   └── index.php              # Entry point
├── dist/
│   └── output.css             # Compiled Tailwind CSS
├── .htaccess                  # URL rewriting rules
└── README.md                  # This file
```

## Features

- **MVC Architecture**: Clean separation of concerns with Models, Views, and Controllers
- **Authentication System**: User registration, login, logout, and password reset
- **Event Management**: Create, edit, delete, and manage events
- **Badge System**: Users can earn badges for attending events
- **Responsive Design**: Modern UI built with Tailwind CSS
- **Security**: Password hashing, session management, and input validation

## Setup Instructions

1. **Database Configuration**: Update the database settings in `app/config/config.php`
2. **Web Server**: Point your web server's document root to the `public/` directory
3. **URL Rewriting**: Ensure Apache mod_rewrite is enabled for clean URLs
4. **File Permissions**: Make sure upload directories are writable

## URL Structure

- `/` - Redirects to dashboard (if authenticated) or login
- `/auth/login` - Login page
- `/auth/register` - Registration page
- `/auth/logout` - Logout
- `/dashboard` - User dashboard
- `/dashboard/profile` - User profile management
- `/dashboard/badges` - User's earned badges
- `/events/create` - Create new event
- `/events/edit/{id}` - Edit event
- `/events/manage/{id}` - Manage event
- `/events/delete/{id}` - Delete event
- `/events/register/{key}` - Register for event using key

## Database Tables

### user_credentials
- uid (Primary Key)
- fname, mname, lname
- email, password
- profilepicture
- emailverified
- attendedevents (JSON array)
- currboundtoken
- creationtime
- password_reset_token
- password_reset_expiry
- new_email
- verification_code

### events
- eventid (Primary Key)
- eventname
- startdate, enddate
- location
- eventinfopath
- eventbadgepath
- eventcreator (Foreign Key to user_credentials.uid)
- eventkey
- eventshortinfo
- participantcount

## Framework Components

### Core Classes

- **App**: Handles routing and controller instantiation
- **Controller**: Base class with common controller methods
- **Model**: Base class with common database operations
- **Database**: Database connection and query handling

### Controllers

- **AuthController**: Handles authentication and user management
- **DashboardController**: Manages dashboard and user profile
- **EventController**: Handles event creation, editing, and management

### Models

- **User**: User-related database operations
- **Event**: Event-related database operations

## Security Features

- Password hashing using PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- CSRF protection (via session tokens)
- SQL injection prevention (prepared statements)

## Styling

The application uses Tailwind CSS for styling. The compiled CSS is located in `dist/output.css`. To rebuild the CSS after making changes to Tailwind classes, run:

```bash
npx tailwindcss -i ./src/input.css -o ./dist/output.css --watch
```

## Development

This framework provides a solid foundation for building PHP web applications with:
- Clean, maintainable code structure
- Separation of concerns
- Reusable components
- Modern development practices 