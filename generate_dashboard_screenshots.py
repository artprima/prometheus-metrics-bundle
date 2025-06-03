#!/usr/bin/env python3
"""
Generate realistic dashboard screenshots for the Grafana dashboards.
This script creates representative images showing what the dashboards look like with real data.
"""

import matplotlib.pyplot as plt
import matplotlib.patches as patches
from matplotlib.patches import Rectangle
import numpy as np
from datetime import datetime, timedelta
import os

# Set up the output directory
output_dir = "screenshots"
os.makedirs(output_dir, exist_ok=True)

# Configure matplotlib for better-looking plots
plt.style.use('dark_background')
plt.rcParams.update({
    'font.size': 8,
    'axes.titlesize': 10,
    'axes.labelsize': 8,
    'xtick.labelsize': 7,
    'ytick.labelsize': 7,
    'legend.fontsize': 7,
    'figure.titlesize': 12
})

def create_symfony_overview_dashboard():
    """Create the Symfony Application Overview dashboard screenshot."""
    
    fig = plt.figure(figsize=(16, 10))
    fig.patch.set_facecolor('#1f1f1f')
    
    # Title
    fig.suptitle('Symfony Application Overview Dashboard', fontsize=16, color='white', y=0.95)
    
    # Generate sample time series data
    hours = 24
    times = [datetime.now() - timedelta(hours=hours-i) for i in range(hours)]
    time_labels = [t.strftime('%H:%M') for t in times]
    
    # Create subplots with GridSpec for better control
    gs = fig.add_gridspec(3, 4, hspace=0.3, wspace=0.3)
    
    # 1. HTTP Request Rate (top left, spans 2 columns)
    ax1 = fig.add_subplot(gs[0, :2])
    request_rates = np.random.normal(150, 30, hours)
    ax1.plot(times, request_rates, color='#00ff00', linewidth=2, label='Request Rate')
    ax1.fill_between(times, request_rates, alpha=0.3, color='#00ff00')
    ax1.set_title('HTTP Request Rate (req/min)', color='white')
    ax1.set_ylabel('Requests/min', color='white')
    ax1.grid(True, alpha=0.3)
    ax1.tick_params(colors='white')
    
    # 2. Response Status Distribution (top right, spans 2 columns)
    ax2 = fig.add_subplot(gs[0, 2:])
    status_labels = ['2xx', '3xx', '4xx', '5xx']
    status_values = [87.5, 8.2, 3.8, 0.5]
    colors = ['#00ff00', '#ffff00', '#ff8800', '#ff0000']
    bars = ax2.bar(status_labels, status_values, color=colors)
    ax2.set_title('Response Status Distribution (%)', color='white')
    ax2.set_ylabel('Percentage', color='white')
    ax2.tick_params(colors='white')
    
    # Add value labels on bars
    for bar, value in zip(bars, status_values):
        height = bar.get_height()
        ax2.text(bar.get_x() + bar.get_width()/2., height + 0.5,
                f'{value}%', ha='center', va='bottom', color='white')
    
    # 3. Response Time Percentiles (middle left, spans 2 columns)
    ax3 = fig.add_subplot(gs[1, :2])
    p50_times = np.random.normal(45, 10, hours)
    p95_times = np.random.normal(120, 25, hours)
    p99_times = np.random.normal(280, 50, hours)
    
    ax3.plot(times, p50_times, color='#00ff00', linewidth=2, label='50th percentile')
    ax3.plot(times, p95_times, color='#ffff00', linewidth=2, label='95th percentile')
    ax3.plot(times, p99_times, color='#ff8800', linewidth=2, label='99th percentile')
    ax3.set_title('Response Time Percentiles (ms)', color='white')
    ax3.set_ylabel('Response Time (ms)', color='white')
    ax3.legend()
    ax3.grid(True, alpha=0.3)
    ax3.tick_params(colors='white')
    
    # 4. Exception Count (middle right, spans 2 columns)
    ax4 = fig.add_subplot(gs[1, 2:])
    exception_counts = np.random.poisson(2, hours)
    ax4.bar(times, exception_counts, color='#ff0000', alpha=0.7, width=0.8)
    ax4.set_title('Exception Count (per hour)', color='white')
    ax4.set_ylabel('Exceptions', color='white')
    ax4.tick_params(colors='white')
    
    # 5. Application Info Panel (bottom left)
    ax5 = fig.add_subplot(gs[2, :2])
    ax5.axis('off')
    info_text = """Application Metadata:
    
Instance: symfony-prod-01
Version: v2.1.4
Environment: production
Uptime: 15d 8h 32m
Last Deploy: 2024-06-01 14:30:00"""
    
    ax5.text(0.05, 0.95, info_text, transform=ax5.transAxes, fontsize=10,
            verticalalignment='top', color='white', 
            bbox=dict(boxstyle="round,pad=0.3", facecolor='#2f2f2f', alpha=0.8))
    
    # 6. Key Metrics Summary (bottom right)
    ax6 = fig.add_subplot(gs[2, 2:])
    ax6.axis('off')
    
    # Create metric boxes
    metrics = [
        ("Total Requests", "1.2M", "#00ff00"),
        ("Avg Response Time", "67ms", "#ffff00"),
        ("Error Rate", "0.8%", "#ff8800"),
        ("Active Users", "234", "#00ffff")
    ]
    
    y_positions = [0.8, 0.6, 0.4, 0.2]
    for i, (label, value, color) in enumerate(metrics):
        # Background box
        rect = Rectangle((0.1, y_positions[i]-0.05), 0.8, 0.12, 
                        facecolor='#2f2f2f', alpha=0.8, transform=ax6.transAxes)
        ax6.add_patch(rect)
        
        # Label and value
        ax6.text(0.15, y_positions[i], label, transform=ax6.transAxes,
                fontsize=9, color='white', va='center')
        ax6.text(0.85, y_positions[i], value, transform=ax6.transAxes,
                fontsize=12, color=color, va='center', ha='right', weight='bold')
    
    # Format x-axis labels
    for ax in [ax1, ax3, ax4]:
        ax.tick_params(axis='x', rotation=45)
    
    plt.tight_layout()
    plt.savefig(f'{output_dir}/symfony-app-overview-dashboard.png', 
                dpi=150, bbox_inches='tight', facecolor='#1f1f1f')
    plt.close()

def create_symfony_monitoring_dashboard():
    """Create the Symfony Application Monitoring dashboard screenshot."""
    
    fig = plt.figure(figsize=(16, 10))
    fig.patch.set_facecolor('#1f1f1f')
    
    # Title
    fig.suptitle('Symfony Application Monitoring Dashboard', fontsize=16, color='white', y=0.95)
    
    # Create subplots
    gs = fig.add_gridspec(4, 4, hspace=0.4, wspace=0.3)
    
    # Top row - KPI stat panels
    kpi_data = [
        ("Request Rate", "142.3", "req/min", "#00ff00"),
        ("Error Rate", "0.8%", "of requests", "#ff8800"),  
        ("Avg Response", "67ms", "response time", "#ffff00"),
        ("Active Sessions", "1,247", "current users", "#00ffff")
    ]
    
    for i, (title, value, subtitle, color) in enumerate(kpi_data):
        ax = fig.add_subplot(gs[0, i])
        ax.axis('off')
        
        # Background
        rect = Rectangle((0.05, 0.1), 0.9, 0.8, facecolor='#2f2f2f', 
                        alpha=0.8, transform=ax.transAxes)
        ax.add_patch(rect)
        
        # Title
        ax.text(0.5, 0.8, title, transform=ax.transAxes, fontsize=10,
                ha='center', va='center', color='white', weight='bold')
        
        # Value
        ax.text(0.5, 0.5, value, transform=ax.transAxes, fontsize=18,
                ha='center', va='center', color=color, weight='bold')
        
        # Subtitle
        ax.text(0.5, 0.25, subtitle, transform=ax.transAxes, fontsize=8,
                ha='center', va='center', color='#cccccc')
    
    # Second row - Request volume over time
    ax_req = fig.add_subplot(gs[1, :])
    hours = 48
    times = [datetime.now() - timedelta(hours=hours-i) for i in range(hours)]
    request_volume = np.random.normal(140, 20, hours) + 10 * np.sin(np.linspace(0, 4*np.pi, hours))
    
    ax_req.plot(times, request_volume, color='#00ff00', linewidth=2)
    ax_req.fill_between(times, request_volume, alpha=0.3, color='#00ff00')
    ax_req.set_title('Request Volume (48h)', color='white', fontsize=12)
    ax_req.set_ylabel('Requests/min', color='white')
    ax_req.grid(True, alpha=0.3)
    ax_req.tick_params(colors='white', axis='x', rotation=45)
    
    # Third row - Error rate percentage over time  
    ax_err = fig.add_subplot(gs[2, :])
    error_rates = np.random.exponential(0.5, hours)
    error_rates = np.clip(error_rates, 0, 5)  # Cap at 5%
    
    ax_err.plot(times, error_rates, color='#ff8800', linewidth=2)
    ax_err.fill_between(times, error_rates, alpha=0.3, color='#ff8800')
    ax_err.set_title('Error Rate Percentage (48h)', color='white', fontsize=12)
    ax_err.set_ylabel('Error Rate (%)', color='white')
    ax_err.grid(True, alpha=0.3)
    ax_err.tick_params(colors='white', axis='x', rotation=45)
    
    # Bottom row - Instance information tables
    ax_inst = fig.add_subplot(gs[3, :2])
    ax_inst.axis('off')
    ax_inst.text(0.5, 0.9, 'Instance Information', transform=ax_inst.transAxes,
                fontsize=12, ha='center', color='white', weight='bold')
    
    instance_data = [
        ["Instance", "Status", "Uptime"],
        ["symfony-prod-01", "🟢 Healthy", "15d 8h"],
        ["symfony-prod-02", "🟢 Healthy", "15d 8h"],
        ["symfony-prod-03", "🟡 Warning", "2d 4h"],
    ]
    
    table_ax = ax_inst.table(cellText=instance_data[1:], colLabels=instance_data[0],
                            cellLoc='center', loc='center',
                            colWidths=[0.3, 0.3, 0.3])
    table_ax.auto_set_font_size(False)
    table_ax.set_fontsize(9)
    table_ax.scale(1, 2)
    
    # Style the table
    for i in range(len(instance_data)):
        for j in range(len(instance_data[0])):
            cell = table_ax[(i, j)]
            if i == 0:  # Header
                cell.set_facecolor('#4f4f4f')
                cell.set_text_props(weight='bold', color='white')
            else:
                cell.set_facecolor('#2f2f2f')
                cell.set_text_props(color='white')
    
    # PHP Environment info
    ax_php = fig.add_subplot(gs[3, 2:])
    ax_php.axis('off')
    ax_php.text(0.5, 0.9, 'PHP Environment', transform=ax_php.transAxes,
                fontsize=12, ha='center', color='white', weight='bold')
    
    php_info = """PHP Version: 8.2.18
Memory Limit: 512M
Max Execution Time: 30s
OPcache: Enabled
Symfony Version: 6.4.22
Bundle Version: 1.20.0"""
    
    ax_php.text(0.1, 0.7, php_info, transform=ax_php.transAxes, fontsize=9,
               verticalalignment='top', color='white',
               bbox=dict(boxstyle="round,pad=0.3", facecolor='#2f2f2f', alpha=0.8))
    
    plt.tight_layout()
    plt.savefig(f'{output_dir}/symfony-app-monitoring-dashboard.png', 
                dpi=150, bbox_inches='tight', facecolor='#1f1f1f')
    plt.close()

def main():
    """Generate both dashboard screenshots."""
    print("Generating Symfony Application Overview dashboard...")
    create_symfony_overview_dashboard()
    
    print("Generating Symfony Application Monitoring dashboard...")
    create_symfony_monitoring_dashboard()
    
    print(f"Screenshots saved to {output_dir}/ directory:")
    print(f"  - {output_dir}/symfony-app-overview-dashboard.png")
    print(f"  - {output_dir}/symfony-app-monitoring-dashboard.png")

if __name__ == "__main__":
    main()