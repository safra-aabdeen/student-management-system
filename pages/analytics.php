<?php
// pages/analytics.php
// In a real application, this data would come from a database
$monthlyData = [
    ['month' => 'Jan', 'visitors' => 1200, 'pageviews' => 3600, 'signups' => 120],
    ['month' => 'Feb', 'visitors' => 1400, 'pageviews' => 4200, 'signups' => 150],
    ['month' => 'Mar', 'visitors' => 1300, 'pageviews' => 3900, 'signups' => 130],
    ['month' => 'Apr', 'visitors' => 1500, 'pageviews' => 4500, 'signups' => 160],
    ['month' => 'May', 'visitors' => 1700, 'pageviews' => 5100, 'signups' => 180],
    ['month' => 'Jun', 'visitors' => 1600, 'pageviews' => 4800, 'signups' => 170],
];

$sources = [
    ['source' => 'Direct', 'percentage' => 35],
    ['source' => 'Search', 'percentage' => 30],
    ['source' => 'Referral', 'percentage' => 20],
    ['source' => 'Social', 'percentage' => 15],
];

$devices = [
    ['device' => 'Desktop', 'percentage' => 60],
    ['device' => 'Mobile', 'percentage' => 35],
    ['device' => 'Tablet', 'percentage' => 5],
];
?>

<div>
    <h2 class="text-2xl font-semibold mb-4">Analytics Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Total Visitors</h3>
            <p class="text-3xl font-bold text-blue-600">8,700</p>
            <p class="text-sm text-gray-500">↑ 12% from last month</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Page Views</h3>
            <p class="text-3xl font-bold text-green-600">26,100</p>
            <p class="text-sm text-gray-500">↑ 8% from last month</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">Avg. Session Duration</h3>
            <p class="text-3xl font-bold text-purple-600">3:24</p>
            <p class="text-sm text-gray-500">↑ 5% from last month</p>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-4">Traffic Overview</h3>
        <div x-data="{ activeTab: 'visitors' }" class="mb-4">
            <div class="flex border-b">
                <button
                    @click="activeTab = 'visitors'"
                    :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'visitors'}"
                    class="px-4 py-2 font-medium"
                >
                    Visitors
                </button>
                <button
                    @click="activeTab = 'pageviews'"
                    :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'pageviews'}"
                    class="px-4 py-2 font-medium"
                >
                    Page Views
                </button>
                <button
                    @click="activeTab = 'signups'"
                    :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'signups'}"
                    class="px-4 py-2 font-medium"
                >
                    Signups
                </button>
            </div>

            <!-- Chart Container -->
            <div class="pt-4 h-64">
                <!-- In a real application, you would use a JavaScript charting library like Chart.js -->
                <!-- For this example, we'll simulate a chart with divs -->
                <div class="relative h-full flex items-end">
                    <?php foreach ($monthlyData as $data): ?>
                        <div class="flex-1 flex flex-col items-center">
                            <div x-show="activeTab === 'visitors'" class="w-full px-2">
                                <div class="bg-blue-500 hover:bg-blue-600 transition-all rounded-t"
                                     style="height: <?= ($data['visitors'] / 2000) * 100 ?>%;">
                                </div>
                            </div>
                            <div x-show="activeTab === 'pageviews'" class="w-full px-2">
                                <div class="bg-green-500 hover:bg-green-600 transition-all rounded-t"
                                     style="height: <?= ($data['pageviews'] / 6000) * 100 ?>%;">
                                </div>
                            </div>
                            <div x-show="activeTab === 'signups'" class="w-full px-2">
                                <div class="bg-purple-500 hover:bg-purple-600 transition-all rounded-t"
                                     style="height: <?= ($data['signups'] / 200) * 100 ?>%;">
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-gray-600"><?= $data['month'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic Sources -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Traffic Sources</h3>
            <div class="space-y-4">
                <?php foreach ($sources as $source): ?>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium"><?= $source['source'] ?></span>
                            <span class="text-sm text-gray-500"><?= $source['percentage'] ?>%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full">
                            <div class="h-full bg-blue-500 rounded-full" style="width: <?= $source['percentage'] ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Device Distribution</h3>
            <div class="space-y-4">
                <?php foreach ($devices as $device): ?>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium"><?= $device['device'] ?></span>
                            <span class="text-sm text-gray-500"><?= $device['percentage'] ?>%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full">
                            <div class="h-full bg-green-500 rounded-full" style="width: <?= $device['percentage'] ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Popular Pages -->
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Popular Pages</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unique Views</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bounce Rate</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">/home</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5,432</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3,721</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2:15</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">32%</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">/products</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4,217</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2,845</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3:42</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">24%</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">/about</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3,125</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2,136</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1:54</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">41%</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">/contact</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2,864</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,927</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2:36</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">18%</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">/blog</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2,458</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,628</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4:12</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">22%</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>