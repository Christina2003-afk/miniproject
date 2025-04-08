<?php
include("dbconfig.php");
session_start();
// Check if user is logged in and fetch their role
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['email'];
$role_query = "SELECT role FROM table_reg WHERE email = ?";
$stmt = mysqli_prepare($conn, $role_query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$role_result = mysqli_stmt_get_result($stmt);

if ($role_result && $row = mysqli_fetch_assoc($role_result)) {
    if ($row['role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Overview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #2c3e50;
        }

        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .report-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .report-header h1 {
            color: rgb(207 131 37);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .report-header p {
            color: rgb(207 131 37);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .report-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .filter-item {
            flex: 1;
            min-width: 200px;
        }

        .filter-item label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
            display: block;
        }

        .filter-item select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: white;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .filter-item select:hover {
            border-color: #cbd5e1;
        }

        .filter-item select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .report-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .report-card:hover {
            transform: translateY(-2px);
        }

        .report-card h2 {
            font-size: 1.1rem;
            color: #4a5568;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 600;
            color: rgb(207 131 37);
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.5rem;
            border-radius: 6px;
            background: #f8fafc;
        }

        .trend.up {
            color: #059669;
            background: #ecfdf5;
        }

        .trend.down {
            color: #dc2626;
            background: #fef2f2;
        }

        .export-btn {
            background: rgb(207 131 37);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .export-btn:hover {
            background: #283593;
            transform: translateY(-1px);
        }

        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            width: 100%;
            height: 400px;
        }

        .chart-container h2 {
            font-size: 1.25rem;
            color: rgb(207 131 37);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .report-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="report-header">
            <h1>Reports Overview</h1>
            <p>View and analyze your business metrics</p>
            <div class="report-filters">
                <div class="filter-item">
                    <label>Date Range</label>
                    <select>
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Last 3 months</option>
                        <option>Custom</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Category</label>
                    <select>
                        <option>All Categories</option>
                        <option>Paintings</option>
                        <option>Sculptures</option>
                        <option>Digital Art</option>
                    </select>
                </div>
                <button class="export-btn">
                    <i class="fas fa-download"></i> Export Report
                </button>
            </div>
        </div>

        <div class="report-grid">
            <div class="report-card">
                <h2>Total Sales</h2>
                <div class="stat-value">₹25</div>
                <div class="stat-label">Revenue this month</div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> 12.5% vs last month
                </div>
            </div>

            <div class="report-card">
                <h2>Total Orders</h2>
                <div class="stat-value">25</div>
                <div class="stat-label">Orders this month</div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> 8.2% vs last month
                </div>
            </div>

            <div class="report-card">
                <h2>Average Order Value</h2>
                <div class="stat-value">₹25</div>
                <div class="stat-label">Per order</div>
                <div class="trend down">
                    <i class="fas fa-arrow-down"></i> 3.1% vs last month
                </div>
            </div>
        </div>

        <div class="chart-container">
            <h2>Sales Trend</h2>
            <canvas id="salesChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>Top Selling Categories</h2>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <script>
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar'],
                datasets: [{
                    label: 'Sales',
                    data: [10,20,30],
                    borderColor: '#1a237e',
                    backgroundColor: 'rgba(26, 35, 126, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26, 35, 126, 0.9)',
                        padding: 12,
                        bodyFont: {
                            size: 14
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paintings', 'Sculptures', 'Digital Art', 'Photography'],
                datasets: [{
                    data: [40, 25, 20, 15],
                    backgroundColor: [
                        'rgb(207 131 37)',
                        'hsl(5deg 60.54% 66.08%)',
                        'hsl(176.12deg 47.52% 52.89%)',
                        'hsl(60.92deg 83.2% 72.81%)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14
                            }
                        }
                    }
                }
            }
        });

        // PDF Export functionality
        document.querySelector('.export-btn').addEventListener('click', function() {
            // Element to be exported
            const reportElement = document.querySelector('.container');
            
            // Configuration options for html2pdf
            const options = {
                margin: 10,
                filename: 'report_overview.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Generate PDF
            html2pdf().from(reportElement).set(options).save();
        });
    </script>
</body>
</html>