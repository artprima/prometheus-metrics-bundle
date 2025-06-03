#!/bin/bash

# Activity simulation script for generating metrics
SYMFONY_URL="http://symfony-app"
# More weight on error endpoints for better exception metrics
ENDPOINTS=("/" "/api/users" "/api/error" "/api/error" "/api/database-error" "/api/validation-error" "/api/slow" "/health")

echo "Starting activity simulation..."
echo "Symfony URL: $SYMFONY_URL"

# Wait for Symfony to be ready
echo "Waiting for Symfony to be ready..."
until curl -f "$SYMFONY_URL/health" &>/dev/null; do
    echo "Symfony not ready yet, waiting..."
    sleep 5
done
echo "Symfony is ready!"

# Function to make requests
make_request() {
    local endpoint=$1
    local url="$SYMFONY_URL$endpoint"
    
    # Make request and capture response code
    response_code=$(curl -s -o /dev/null -w "%{http_code}" "$url" || echo "000")
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $endpoint -> HTTP $response_code"
}

# Main simulation loop  
while true; do
    # Random endpoint selection
    endpoint=${ENDPOINTS[$RANDOM % ${#ENDPOINTS[@]}]}
    
    # Make request
    make_request "$endpoint"
    
    # Random delay between requests (1-10 seconds)
    sleep_time=$((RANDOM % 10 + 1))
    sleep $sleep_time
done
