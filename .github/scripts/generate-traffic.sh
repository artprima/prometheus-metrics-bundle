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

echo "Generating test traffic to populate metrics..."

# Generate successful requests
for i in {1..50}; do
  curl -s http://localhost:8080/ > /dev/null &
  curl -s http://localhost:8080/health > /dev/null &
  sleep 0.2
done

# Generate some 404s
for i in {1..10}; do
  curl -s http://localhost:8080/nonexistent > /dev/null &
  sleep 0.1
done

# Generate some slower requests
for i in {1..20}; do
  curl -s http://localhost:8080/slow > /dev/null &
  sleep 0.5
done

wait
echo "Traffic generation completed!"
