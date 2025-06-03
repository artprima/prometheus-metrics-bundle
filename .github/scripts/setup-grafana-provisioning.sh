#!/bin/bash

# Create Grafana provisioning directories
mkdir -p demo/grafana/provisioning/dashboards
mkdir -p demo/grafana/provisioning/datasources

# Copy provisioning configuration files
cp .github/scripts/grafana/provisioning/datasources/prometheus.yml demo/grafana/provisioning/datasources/
cp .github/scripts/grafana/provisioning/dashboards/symfony.yml demo/grafana/provisioning/dashboards/

# Copy the dashboard JSON files to the provisioning directory
cp grafana/symfony-app-overview.json demo/grafana/provisioning/dashboards/
cp grafana/symfony-app-monitoring.json demo/grafana/provisioning/dashboards/

echo "Grafana provisioning setup completed!"
