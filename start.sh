#!/bin/bash

# Define ports based on configuration
BACKEND_PORT=8081
FRONTEND_PORT=8001

# Check if ports are already in use
if lsof -Pi :$BACKEND_PORT -sTCP:LISTEN -t >/dev/null ; then
    echo "Error: Port $BACKEND_PORT is already in use. Please kill the process using it."
    exit 1
fi

if lsof -Pi :$FRONTEND_PORT -sTCP:LISTEN -t >/dev/null ; then
    echo "Error: Port $FRONTEND_PORT is already in use. Please kill the process using it."
    exit 1
fi

echo "Starting Backend API on 127.0.0.1:$BACKEND_PORT..."
# Start backend from within its directory
cd back-end
php -S 127.0.0.1:$BACKEND_PORT router.php >/dev/null 2>&1 &
BACKEND_PID=$!
cd ..

echo "Starting Frontend on 127.0.0.1:$FRONTEND_PORT..."
# Start frontend from within its directory
cd front-end
php -S 127.0.0.1:$FRONTEND_PORT >/dev/null 2>&1 &
FRONTEND_PID=$!
cd ..

echo ""
echo "================================================="
echo "  Video Recommending System is running!"
echo "================================================="
echo "  Frontend available at: http://127.0.0.1:$FRONTEND_PORT"
echo "  Backend API running at: http://127.0.0.1:$BACKEND_PORT"
echo "================================================="
echo "Press Ctrl+C to stop both servers."

# Graceful shutdown handler
trap "echo -e '\nShutting down servers...'; kill $BACKEND_PID $FRONTEND_PID; exit 0" SIGINT SIGTERM EXIT

# Keep script running while background processes execute
wait
