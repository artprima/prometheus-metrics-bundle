#!/bin/bash

echo "Waiting for Symfony app to be ready..."
for i in {1..30}; do
  if curl -s http://localhost:8080/ > /dev/null; then
    echo "Symfony app is ready!"
    break
  fi
  sleep 2
  echo "Attempt $i/30..."
done

# Check if we should run continuously (for background execution)
if [ "$1" = "--continuous" ]; then
  echo "Starting continuous traffic generation..."
  
  # Run for approximately 5 minutes (300 seconds)
  START_TIME=$(date +%s)
  DURATION=300
  
  while [ $(($(date +%s) - START_TIME)) -lt $DURATION ]; do
    # Generate variety of requests every few seconds
    curl -s http://localhost:8080/ > /dev/null &
    curl -s http://localhost:8080/api/users > /dev/null &
    curl -s http://localhost:8080/health > /dev/null &
    
    # Some error requests for exception metrics
    curl -s http://localhost:8080/api/error > /dev/null &
    curl -s http://localhost:8080/api/database-error > /dev/null &
    curl -s http://localhost:8080/api/validation-error > /dev/null &
    
    # Occasional 404s and slow requests
    if [ $((RANDOM % 10)) -eq 0 ]; then
      curl -s http://localhost:8080/nonexistent > /dev/null &
    fi
    
    if [ $((RANDOM % 15)) -eq 0 ]; then
      curl -s http://localhost:8080/api/slow > /dev/null &
    fi
    
    sleep 2
  done
  
  echo "Continuous traffic generation completed after 5 minutes"
  exit 0
fi

echo "Generating test traffic to populate metrics..."

# Generate successful requests
for i in {1..50}; do
  curl -s http://localhost:8080/ > /dev/null &
  curl -s http://localhost:8080/api/users > /dev/null &
  curl -s http://localhost:8080/health > /dev/null &
  sleep 0.2
done

# Generate error requests to populate exception metrics with more variety
for i in {1..50}; do
  curl -s http://localhost:8080/api/error > /dev/null &
  curl -s http://localhost:8080/api/database-error > /dev/null &
  curl -s http://localhost:8080/api/validation-error > /dev/null &
  sleep 0.2
done

# Additional error generation for better exception metrics
for i in {1..25}; do
  curl -s http://localhost:8080/api/error > /dev/null &
  sleep 0.1
done

# Generate some 404s
for i in {1..10}; do
  curl -s http://localhost:8080/nonexistent > /dev/null &
  sleep 0.1
done

# Generate some slower requests
for i in {1..20}; do
  curl -s http://localhost:8080/api/slow > /dev/null &
  sleep 0.5
done

wait
echo "Traffic generation completed!"
