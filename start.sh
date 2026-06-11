#!/bin/bash

# Define ports based on configuration
BACKEND_PORT=8081
FRONTEND_PORT=8001

echo "Starting Backend API on localhost:$BACKEND_PORT..."
# Route requests via router.php as the project architecture demands
php -S localhost:$BACKEND_PORT -t back-end back-end/router.php >/dev/null 2>&1 &
BACKEND_PID=$!

echo "Starting Frontend on localhost:$FRONTEND_PORT..."
# Serve static frontend files
php -S localhost:$FRONTEND_PORT -t front-end >/dev/null 2>&1 &
FRONTEND_PID=$!

echo ""
echo "================================================="
echo "  Video Recommending System is running!"
echo "================================================="
echo "  Frontend available at: http://localhost:$FRONTEND_PORT"
echo "  Backend API running at: http://localhost:$BACKEND_PORT"
echo "================================================="
echo "Press Ctrl+C to stop both servers."

# Graceful shutdown handler
trap "echo -e '\nShutting down servers...'; kill $BACKEND_PID $FRONTEND_PID; exit 0" SIGINT SIGTERM EXIT

# Keep script running while background processes execute
wait
