// 调试代码 - 检查元素加载状态
console.log('Chart.js version:', Chart ? Chart.version : 'Not loaded');
console.log('Sales Chart element:', document.getElementById('salesChart'));
console.log('Category Chart element:', document.getElementById('categoryChart'));

document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar Toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // 2. Debug Canvas Elements
    console.log('Sales Chart Canvas:', document.getElementById('salesChart'));
    console.log('Category Chart Canvas:', document.getElementById('categoryChart'));

    // 3. Initialize Charts
    try {
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        
        // Sales Chart (折线图)
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales',
                    data: [5000, 8000, 12000, 9000, 15000, 18000],
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {  // 新增工具提示配置
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false, // 修改为 false 更合理
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
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
        
        // Category Chart (饼图)
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Dogs', 'Cats', 'Birds', 'Fish', 'Others'],
                datasets: [{
                    data: [35, 30, 15, 10, 10],
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9',
                        '#17a673',
                        '#2c9faf',
                        '#dda20a',
                        '#be2617'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,  // 增加图例间距
                            usePointStyle: true  // 使用圆形图例
                        }
                    },
                    tooltip: {  // 新增工具提示
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                cutout: '70%',
            },
        });

        console.log("Charts initialized successfully!");
    } catch (error) {
        console.error("Chart initialization failed:", error);
    }
});