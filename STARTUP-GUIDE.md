# 🚀 Event Management System - Startup Guide

This guide shows you how to use the `start-app.sh` script to run your entire Event Management System with both PHP and Node.js WebSocket servers.

## 📋 Quick Start

### Start the Application (Development Mode)
```bash
./start-app.sh
```
This starts both servers in development mode with auto-restart on file changes.

### Start the Application (Production Mode)
```bash
./start-app.sh --prod
```
This starts both servers in production mode for better performance.

## 🛠️ Available Commands

| Command | Description |
|---------|-------------|
| `./start-app.sh` | Start both servers in development mode (default) |
| `./start-app.sh --dev` | Start both servers in development mode with nodemon |
| `./start-app.sh --prod` | Start both servers in production mode |
| `./start-app.sh --status` | Check if servers are running and responding |
| `./start-app.sh --kill` | Stop all running servers |
| `./start-app.sh --logs` | Show recent log output from both servers |
| `./start-app.sh --help` | Show help message with all options |

## 🔍 What the Script Does

When you run `./start-app.sh`, it will:

1. **✅ Check prerequisites** (package.json, dependencies)
2. **🔍 Verify ports are available** (8000 for PHP, 3000 for Node.js)
3. **🚀 Start PHP server** on `http://localhost:8000`
4. **🔌 Start Node.js WebSocket server** on `http://localhost:3000`
5. **🏥 Health check** both servers
6. **📊 Display access URLs** and useful information
7. **👀 Monitor processes** and restart if they crash

## 📱 Application URLs

Once started, you can access:

- **📱 Main Application:** http://localhost:8000
- **🔌 WebSocket Server:** ws://localhost:3000
- **❤️ Health Check:** http://localhost:3000/health
- **📊 WebSocket Stats:** http://localhost:3000/api/events/stats

## 📁 Log Files

The script creates organized log files in the `./logs/` directory:

- `logs/php-server.log` - PHP server output
- `logs/node-server.log` - Node.js server output  
- `logs/combined.log` - Combined log stream
- `logs/php-server.pid` - PHP server process ID
- `logs/node-server.pid` - Node.js server process ID

## 🔧 Usage Examples

### Start in Development Mode
```bash
# Start with auto-restart on file changes
./start-app.sh --dev

# When ready, press Ctrl+C to stop both servers
```

### Check Status
```bash
# See if servers are running and healthy
./start-app.sh --status
```

### View Recent Logs
```bash
# Show the last 20 lines from both servers
./start-app.sh --logs

# Or monitor live logs
tail -f logs/combined.log
```

### Production Start
```bash
# Start in production mode (better performance)
./start-app.sh --prod
```

### Emergency Stop
```bash
# Force stop all servers
./start-app.sh --kill
```

## 🛡️ Error Handling

The script includes robust error handling:

- **Port conflicts** - Detects if ports are already in use
- **Failed starts** - Automatically cleans up if server startup fails
- **Process monitoring** - Detects if servers crash unexpectedly
- **Graceful shutdown** - Properly stops all processes on Ctrl+C

## 🎯 Integration with Your Workflow

### Development Workflow
```bash
# 1. Start the application
./start-app.sh --dev

# 2. Make your PHP/JavaScript changes
# (Files auto-reload thanks to nodemon)

# 3. Check real-time features
#    - Open http://localhost:8000
#    - Test event participant management
#    - See live WebSocket updates

# 4. Stop when done
# Press Ctrl+C or run: ./start-app.sh --kill
```

### Testing WebSocket Features
```bash
# 1. Start the app
./start-app.sh

# 2. Open multiple browser tabs to test:
#    - Event management page
#    - Add/remove participants
#    - Change participant status

# 3. Watch real-time updates across all tabs!
```

## 🔧 Troubleshooting

### Port Already in Use
```bash
# Check what's using the ports
lsof -i :8000
lsof -i :3000

# Force kill any existing servers
./start-app.sh --kill
```

### Server Won't Start
```bash
# Check recent logs for errors
./start-app.sh --logs

# Or check specific server logs
tail -f logs/php-server.log
tail -f logs/node-server.log
```

### Dependencies Missing
```bash
# The script auto-installs npm dependencies
# But you can manually run:
npm install
```

### Reset Everything
```bash
# Kill servers and clean up
./start-app.sh --kill
rm -rf logs/
rm -rf node_modules/
npm install
```

## 🎉 What You Get

With this startup script, you now have:

- ✅ **One-command startup** for your entire application
- ✅ **Real-time WebSocket** functionality for event management
- ✅ **Automatic process monitoring** and restart
- ✅ **Organized logging** and debugging
- ✅ **Graceful shutdown** handling
- ✅ **Development and production** modes
- ✅ **Health checking** and status monitoring

## 🚀 Next Steps

1. **Start the application:** `./start-app.sh`
2. **Open your browser:** http://localhost:8000
3. **Test real-time features:** Create events, add participants
4. **Watch the magic:** See live updates without page refreshes!

Your Event Management System is now running with full real-time capabilities! 🎊 