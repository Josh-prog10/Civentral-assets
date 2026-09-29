document.addEventListener("DOMContentLoaded", () => {
    let trendsChart, demoChart, radarChart;

    // Dynamic theme styling provider
    function getThemeColors() {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            gridColor: isDark ? '#1e293b' : '#F1F5F9',
            ticksColor: isDark ? '#94A3B8' : '#64748B',
            legendColor: isDark ? '#cbd5e1' : '#475569',
            radarGridColor: isDark ? '#334155' : '#E2E8F0',
            radarAngleColor: isDark ? '#1e293b' : '#F1F5F9',
            radarBg: isDark ? '#0f172a' : '#ffffff'
        };
    }

    // 1. Line/Area Chart for Trends
    function renderTrendsChart() {
        const trendsCanvas = document.getElementById('trendsChart');
        if (!trendsCanvas) return;
        const trendsCtx = trendsCanvas.getContext('2d');
        const colors = getThemeColors();
        
        const citizenGrad = trendsCtx.createLinearGradient(0, 0, 0, 300);
        citizenGrad.addColorStop(0, 'rgba(30, 81, 123, 0.22)');
        citizenGrad.addColorStop(1, 'rgba(30, 81, 123, 0)');

        const appGrad = trendsCtx.createLinearGradient(0, 0, 0, 300);
        appGrad.addColorStop(0, 'rgba(245, 158, 11, 0.22)');
        appGrad.addColorStop(1, 'rgba(245, 158, 11, 0)');

        trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Citizens Registered',
                        data: [12000, 19000, 24000, 31000, 42000, 56000],
                        borderColor: '#1E517B',
                        borderWidth: 3,
                        fill: true,
                        backgroundColor: citizenGrad,
                        tension: 0.4,
                        pointBackgroundColor: '#1E517B',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Applications Filed',
                        data: [8000, 11000, 15000, 22000, 21000, 29000],
                        borderColor: '#F59E0B',
                        borderWidth: 3,
                        fill: true,
                        backgroundColor: appGrad,
                        tension: 0.4,
                        pointBackgroundColor: '#F59E0B',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 9,
                                weight: '600'
                            },
                            color: colors.ticksColor
                        }
                    },
                    y: {
                        grid: {
                            color: colors.gridColor,
                            lineWidth: 1
                        },
                        ticks: {
                            font: {
                                size: 9,
                                weight: '600'
                            },
                            color: colors.ticksColor
                        }
                    }
                }
            }
        });
    }

    // 2. Doughnut Chart for Demographics
    function renderDemoChart() {
        const demoCanvas = document.getElementById('demographicsChart');
        if (!demoCanvas) return;
        const demoCtx = demoCanvas.getContext('2d');
        const colors = getThemeColors();
        demoChart = new Chart(demoCtx, {
            type: 'doughnut',
            data: {
                labels: ['Youth', 'Seniors', 'Working Class', 'Students'],
                datasets: [{
                    data: [35, 15, 30, 20],
                    backgroundColor: [
                        '#1E517B', // youth
                        '#F59E0B', // senior
                        '#0D9488', // working class (teal)
                        '#7C3AED'  // student (purple)
                    ],
                    borderWidth: 3,
                    borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 9,
                                weight: '700'
                            },
                            boxWidth: 10,
                            color: colors.legendColor,
                            padding: 12
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Radar Chart for Municipal Service Load
    function renderRadarChart() {
        const radarCanvas = document.getElementById('workloadRadarChart');
        if (!radarCanvas) return;
        const radarCtx = radarCanvas.getContext('2d');
        const colors = getThemeColors();
        radarChart = new Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: ['Health Care', 'Social Welfare', 'Education', 'Permits', 'Public Assets'],
                datasets: [
                    {
                        label: 'Service Workload Ratio',
                        data: [85, 70, 90, 60, 50],
                        backgroundColor: 'rgba(124, 58, 237, 0.15)',
                        borderColor: '#7C3AED',
                        borderWidth: 2,
                        pointBackgroundColor: '#7C3AED',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            color: colors.radarAngleColor
                        },
                        grid: {
                            color: colors.radarGridColor
                        },
                        ticks: {
                            display: false
                        },
                        pointLabels: {
                            font: {
                                size: 8,
                                weight: '700'
                            },
                            color: colors.ticksColor
                        }
                    }
                }
            }
        });
    }

    // Initial Render
    renderTrendsChart();
    renderDemoChart();
    renderRadarChart();

    // Smooth Skeleton Loader Transition
    function handleSkeletonTransition() {
        const skeleton = document.getElementById('dashboardSkeleton');
        const realContent = document.getElementById('dashboardRealContent');

        if (!skeleton || !realContent) return;

        // Show real content in DOM
        realContent.classList.remove('hidden');

        // Allow browser frame to calculate layout before fading
        requestAnimationFrame(() => {
            skeleton.classList.add('opacity-0', 'pointer-events-none');
            realContent.classList.remove('opacity-0', 'translate-y-2');
            realContent.classList.add('opacity-100', 'translate-y-0');

            setTimeout(() => {
                skeleton.classList.add('hidden');
            }, 500);
        });
    }

    // Trigger skeleton hide and real content reveal after a smooth loading window
    setTimeout(handleSkeletonTransition, 600);

    // Listen for Theme Toggle to dynamically redraw charts
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            setTimeout(() => {
                // Destroy old instances
                if (trendsChart) trendsChart.destroy();
                if (demoChart) demoChart.destroy();
                if (radarChart) radarChart.destroy();

                // Re-render with new colors
                renderTrendsChart();
                renderDemoChart();
                renderRadarChart();
            }, 150);
        });
    }
});
