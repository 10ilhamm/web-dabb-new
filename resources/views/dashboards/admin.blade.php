@extends('layouts.app')

@section('header')
    <div class="text-[13px] text-gray-500 font-medium">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">{{ __('dashboard.header.breadcrumb_home') }}</a> /
        <span class="text-[#0ea5e9]">{{ __('dashboard.admin.title') }}</span>
    </div>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-[22px] font-bold text-[#1E293B] mb-6">{{ __('dashboard.admin.title') }}</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Card 1 -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-3xl font-bold text-gray-900 leading-tight">10</div>
                        <div class="text-xs font-medium text-gray-600 mt-1">{{ __('dashboard.admin.stats.total_visitors') }}
                        </div>
                    </div>
                    <!-- Icon -->
                    <div class="p-2 bg-gray-50 rounded-lg text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <a href="#"
                        class="text-[11px] font-medium text-gray-400 hover:text-blue-500">{{ __('dashboard.admin.stats.view_details') }}</a>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-3xl font-bold text-gray-900 leading-tight">3</div>
                        <div class="text-xs font-medium text-gray-600 mt-1">{{ __('dashboard.admin.stats.daily_visitors') }}
                        </div>
                    </div>
                    <div class="p-2 bg-gray-50 rounded-lg text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <a href="#"
                        class="text-[11px] font-medium text-gray-400 hover:text-blue-500">{{ __('dashboard.admin.stats.view_details') }}</a>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start relative">
                    <div>
                        <div class="text-3xl font-bold text-gray-900 leading-tight">2</div>
                        <div class="text-xs font-medium text-gray-600 mt-1">{{ __('dashboard.admin.stats.online_users') }}
                        </div>
                    </div>
                    <div class="p-2 bg-gray-50 rounded-lg text-gray-600 relative">
                        <!-- Online dot indicator -->
                        <div class="absolute top-1.5 right-1.5 w-2 h-2 bg-green-500 rounded-full border border-white"></div>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <a href="#"
                        class="text-[11px] font-medium text-gray-400 hover:text-blue-500">{{ __('dashboard.admin.stats.view_details') }}</a>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-3xl font-bold text-gray-900 leading-tight">4</div>
                        <div class="text-xs font-medium text-gray-600 mt-1">{{ __('dashboard.admin.stats.total_users') }}
                        </div>
                    </div>
                    <div class="p-2 bg-gray-50 rounded-lg text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <a href="#"
                        class="text-[11px] font-medium text-gray-400 hover:text-blue-500">{{ __('dashboard.admin.stats.view_details') }}</a>
                </div>
            </div>

        </div>

        <!-- Chart Section -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-800">{{ __('dashboard.admin.chart.title') }}</h2>
            </div>

            <div class="w-full relative" style="height: 350px;">
                <canvas id="visitorChart"></canvas>
            </div>

            <!-- Custom Legend at bottom -->
            <div class="flex justify-center flex-col items-center mt-4">
                <div class="text-[11px] font-semibold text-gray-800 mb-2">{{ __('dashboard.admin.chart.user_type') }}</div>
                <div class="flex space-x-6 text-[11px] font-medium text-gray-600">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-[#3B82F6] mr-2"></div>
                        {{ __('dashboard.admin.chart.unregistered_user') }}
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-[#06B6D4] mr-2"></div>{{ __('dashboard.admin.chart.admin') }}
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-[#EAB308] mr-2"></div>
                        {{ __('dashboard.admin.chart.employee') }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('visitorChart').getContext('2d');

            // Define Custom Title inside chart area based on Figma
            const chartTitle = "{{ __('dashboard.admin.chart.subtitle') }}";

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['08:00', '09:00', '10:00', '11:00', '12:00'],
                    datasets: [{
                            label: "{{ __('dashboard.admin.chart.unregistered_user') }}",
                            data: [0, 0, 0, 0, 0],
                            borderColor: '#3B82F6',
                            backgroundColor: '#3B82F6',
                            borderWidth: 2.5,
                            tension: 0.4,
                            pointRadius: 0
                        },
                        {
                            label: "{{ __('dashboard.admin.chart.admin') }}",
                            data: [0, 0, 2, 0, 0],
                            borderColor: '#06B6D4',
                            backgroundColor: '#06B6D4',
                            borderWidth: 2.5,
                            tension: 0.4,
                            pointRadius: 0
                        },
                        {
                            label: "{{ __('dashboard.admin.chart.employee') }}",
                            data: [0, 1, 0, 0, 0],
                            borderColor: '#EAB308',
                            backgroundColor: '#EAB308',
                            borderWidth: 2.5,
                            tension: 0.4,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // We use our custom HTML legend
                        },
                        title: {
                            display: true,
                            text: chartTitle,
                            align: 'start',
                            color: '#334155',
                            font: {
                                size: 13,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            padding: {
                                bottom: 30
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1e293b',
                            bodyColor: '#475569',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            boxPadding: 4,
                            usePointStyle: true,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11,
                                    family: "'Inter', sans-serif"
                                },
                                color: '#94a3b8'
                            },
                            border: {
                                display: false
                            },
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false,
                            },
                            title: {
                                display: true,
                                text: "{{ __('dashboard.admin.chart.y_axis') }}",
                                font: {
                                    size: 10,
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                },
                                color: '#475569'
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    family: "'Inter', sans-serif"
                                },
                                color: '#94a3b8'
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
    </script>
@endpush
