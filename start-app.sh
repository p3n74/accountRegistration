#!/bin/bash

# Event Management System Startup Script
# Starts both PHP server (port 8000) and Node.js WebSocket server (port 3000)

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
PHP_PORT=8000
NODE_PORT=3000
APP_NAME="Event Management System"
LOG_DIR="./logs"

# PID files for process management
PHP_PID_FILE="$LOG_DIR/php-server.pid"
NODE_PID_FILE="$LOG_DIR/node-server.pid"

# Log files
PHP_LOG_FILE="$LOG_DIR/php-server.log"
NODE_LOG_FILE="$LOG_DIR/node-server.log"
COMBINED_LOG_FILE="$LOG_DIR/combined.log"

# Create logs directory if it doesn't exist
mkdir -p "$LOG_DIR"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[$(date '+%H:%M:%S')]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[$(date '+%H:%M:%S')] ✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}[$(date '+%H:%M:%S')] ⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}[$(date '+%H:%M:%S')] ❌ $1${NC}"
}

print_info() {
    echo -e "${CYAN}[$(date '+%H:%M:%S')] ℹ️  $1${NC}"
}

# Function to check if a port is in use
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        return 0  # Port is in use
    else
        return 1  # Port is free
    fi
}

# Function to cleanup processes on exit
cleanup() {
    print_status "Shutting down $APP_NAME..."

    # Kill PHP server
    if [ -f "$PHP_PID_FILE" ]; then
        PHP_PID=$(cat "$PHP_PID_FILE")
        if kill -0 "$PHP_PID" 2>/dev/null; then
            print_status "Stopping PHP server (PID: $PHP_PID)..."
            kill "$PHP_PID" 2>/dev/null || true
            sleep 2
            if kill -0 "$PHP_PID" 2>/dev/null; then
                kill -9 "$PHP_PID" 2>/dev/null || true
            fi
        fi
        rm -f "$PHP_PID_FILE"
    fi

    # Kill Node.js server
    if [ -f "$NODE_PID_FILE" ]; then
        NODE_PID=$(cat "$NODE_PID_FILE")
        if kill -0 "$NODE_PID" 2>/dev/null; then
            print_status "Stopping Node.js server (PID: $NODE_PID)..."
            kill "$NODE_PID" 2>/dev/null || true
            sleep 2
            if kill -0 "$NODE_PID" 2>/dev/null; then
                kill -9 "$NODE_PID" 2>/dev/null || true
            fi
        fi
        rm -f "$NODE_PID_FILE"
    fi

    # Kill any remaining processes on our ports
    lsof -ti tcp:$PHP_PORT | xargs -r kill -9 2>/dev/null || true
    lsof -ti tcp:$NODE_PORT | xargs -r kill -9 2>/dev/null || true
    pkill -f "php -S localhost:$PHP_PORT" 2>/dev/null || true
    pkill -f "node.*server.js" 2>/dev/null || true

    print_success "Shutdown complete!"
    exit 0
}

# Function to show usage
show_usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -h, --help      Show this help message"
    echo "  -d, --dev       Run in development mode (with nodemon)"
    echo "  -p, --prod      Run in production mode"
    echo "  -s, --status    Show server status"
    echo "  -k, --kill      Kill running servers"
    echo "  -l, --logs      Show recent logs"
    echo ""
    echo "Default: Run in development mode"
}

# Function to show server status
show_status() {
    print_info "Checking server status..."

    if check_port $PHP_PORT; then
        print_success "PHP server is running on port $PHP_PORT"
        curl -s "http://localhost:$PHP_PORT" >/dev/null 2>&1 && print_success "PHP server is responding" || print_warning "PHP server is not responding"
    else
        print_error "PHP server is not running on port $PHP_PORT"
    fi

    if check_port $NODE_PORT; then
        print_success "Node.js server is running on port $NODE_PORT"
        if curl -s "http://localhost:$NODE_PORT/health" >/dev/null 2>&1; then
            HEALTH=$(curl -s "http://localhost:$NODE_PORT/health" | grep -o '"connections":[0-9]*' | cut -d: -f2)
            print_success "Node.js server is responding (WebSocket connections: ${HEALTH:-0})"
        else
            print_warning "Node.js server is not responding"
        fi
    else
        print_error "Node.js server is not running on port $NODE_PORT"
    fi
}

# Function to kill running servers
kill_servers() {
    print_status "Killing running servers..."
    cleanup
}

# Function to show recent logs
show_logs() {
    print_info "Recent application logs:"
    echo -e "${YELLOW}=== PHP Server Logs ===${NC}"
    [ -f "$PHP_LOG_FILE" ] && tail -20 "$PHP_LOG_FILE" || echo "No PHP logs found"

    echo -e "\n${YELLOW}=== Node.js Server Logs ===${NC}"
    [ -f "$NODE_LOG_FILE" ] && tail -20 "$NODE_LOG_FILE" || echo "No Node.js logs found"
}

# Function to start the application
start_app() {
    local mode=$1

    print_status "Forcefully killing processes on ports $PHP_PORT and $NODE_PORT..."
    lsof -ti tcp:$PHP_PORT | xargs -r kill -9 2>/dev/null || true
    lsof -ti tcp:$NODE_PORT | xargs -r kill -9 2>/dev/null || true
    pkill -f "php -S localhost:$PHP_PORT" 2>/dev/null || true
    pkill -f "node.*server.js" 2>/dev/null || true
    sleep 1

    print_status "Starting $APP_NAME..."
    print_info "Mode: $mode"
    print_info "PHP Server: http://localhost:$PHP_PORT"
    print_info "WebSocket Server: http://localhost:$NODE_PORT"
    print_info "Logs directory: $LOG_DIR"

    if [ ! -f "package.json" ]; then
        print_error "package.json not found. Please run this script from the project root directory."
        exit 1
    fi

    if [ ! -d "node_modules" ]; then
        print_warning "node_modules not found. Installing dependencies..."
        npm install
    fi

    print_status "Starting PHP server on port $PHP_PORT..."
    php -S localhost:$PHP_PORT -t public/ -c /dev/null > "$PHP_LOG_FILE" 2>&1 &
    PHP_PID=$!
    echo $PHP_PID > "$PHP_PID_FILE"
    sleep 2
    kill -0 $PHP_PID 2>/dev/null && print_success "PHP server started successfully (PID: $PHP_PID)" || { print_error "PHP server failed to start"; exit 1; }

    print_status "Starting Node.js WebSocket server on port $NODE_PORT..."
    if [ "$mode" = "development" ]; then
        npm run dev > "$NODE_LOG_FILE" 2>&1 &
    else
        npm start > "$NODE_LOG_FILE" 2>&1 &
    fi
    NODE_PID=$!
    echo $NODE_PID > "$NODE_PID_FILE"
    sleep 3
    kill -0 $NODE_PID 2>/dev/null && print_success "Node.js server started successfully (PID: $NODE_PID)" || { print_error "Node.js server failed to start"; cleanup; exit 1; }

    sleep 2
    curl -s "http://localhost:$PHP_PORT" >/dev/null 2>&1 && print_success "PHP server is responding" || print_warning "PHP server may not be ready"
    curl -s "http://localhost:$NODE_PORT/health" >/dev/null 2>&1 && print_success "Node.js server is responding" || print_warning "Node.js server may not be ready"

    print_success "$APP_NAME started successfully!"
    print_info "📱 Web App: http://localhost:$PHP_PORT"
    print_info "💬 Social Demo: http://localhost:$PHP_PORT/social.html"
    print_info "🔌 WebSocket: ws://localhost:$NODE_PORT"
    print_info "❤️ Health Check: http://localhost:$NODE_PORT/health"
    print_info "📊 WebSocket Stats: http://localhost:$NODE_PORT/api/events/stats"

    tail -f "$PHP_LOG_FILE" "$NODE_LOG_FILE" > "$COMBINED_LOG_FILE" 2>&1 &

    print_status "Press Ctrl+C to stop all servers"

    while true; do
        ! kill -0 $PHP_PID 2>/dev/null && { print_error "PHP server stopped"; break; }
        ! kill -0 $NODE_PID 2>/dev/null && { print_error "Node.js server stopped"; break; }
        sleep 5
    done
}

# Handle Ctrl+C
trap cleanup SIGINT SIGTERM

# Parse CLI options
MODE="development"
ACTION="start"

while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help) show_usage; exit 0 ;;
        -d|--dev) MODE="development"; shift ;;
        -p|--prod) MODE="production"; shift ;;
        -s|--status) ACTION="status"; shift ;;
        -k|--kill) ACTION="kill"; shift ;;
        -l|--logs) ACTION="logs"; shift ;;
        *) print_error "Unknown option: $1"; show_usage; exit 1 ;;
    esac
done

# Main
case $ACTION in
    "start") start_app "$MODE" ;;
    "status") show_status ;;
    "kill") kill_servers ;;
    "logs") show_logs ;;
    *) print_error "Unknown action: $ACTION"; show_usage; exit 1 ;;
esac

