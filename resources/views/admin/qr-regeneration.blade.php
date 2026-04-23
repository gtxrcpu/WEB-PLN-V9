@extends('layouts.app')

@section('title', 'QR Code Regeneration')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">QR Code Regeneration</h1>
            <div class="text-sm text-gray-600">
                Fix scanning issues with improved layout
            </div>
        </div>

        <!-- Equipment Counts -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
            @foreach([
                'apar' => 'APAR',
                'apat' => 'APAT', 
                'apab' => 'APAB',
                'p3k' => 'P3K',
                'box_hydrant' => 'Box Hydrant',
                'fire_alarm' => 'Fire Alarm',
                'rumah_pompa' => 'Rumah Pompa'
            ] as $key => $label)
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $counts[$key] }}</div>
                <div class="text-sm text-gray-600">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        <!-- Regeneration Controls -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-yellow-800 mb-2">⚠️ QR Code Issues Identified</h3>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• QR codes may have scanning issues due to overlapping visual elements</li>
                <li>• Error correction level too high (H) affecting readability</li>
                <li>• Insufficient quiet zone around QR code</li>
                <li>• Logo positioning may interfere with QR scanning</li>
            </ul>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-green-800 mb-2">✅ Improvements Applied</h3>
            <ul class="text-sm text-green-700 space-y-1">
                <li>• Error correction level changed from H to M (better scanning)</li>
                <li>• Proper quiet zone (4 modules minimum) implemented</li>
                <li>• QR area kept completely free from overlapping elements</li>
                <li>• Improved visual layout with proper spacing</li>
                <li>• Logo positioning optimized to avoid QR interference</li>
            </ul>
        </div>

        <!-- Regeneration Form -->
        <form id="regenerationForm" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Equipment Type to Regenerate
                </label>
                <select name="type" id="equipmentType" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Equipment Types</option>
                    <option value="apar">APAR Only ({{ $counts['apar'] }} items)</option>
                    <option value="apat">APAT Only ({{ $counts['apat'] }} items)</option>
                    <option value="apab">APAB Only ({{ $counts['apab'] }} items)</option>
                    <option value="p3k">P3K Only ({{ $counts['p3k'] }} items)</option>
                    <option value="box_hydrant">Box Hydrant Only ({{ $counts['box_hydrant'] }} items)</option>
                    <option value="fire_alarm">Fire Alarm Only ({{ $counts['fire_alarm'] }} items)</option>
                    <option value="rumah_pompa">Rumah Pompa Only ({{ $counts['rumah_pompa'] }} items)</option>
                </select>
            </div>

            <div class="flex space-x-4">
                <button type="submit" id="regenerateBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                    <span id="regenerateText">Regenerate QR Codes</span>
                    <span id="regenerateSpinner" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>

                <button type="button" id="testBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                    Generate Test QR
                </button>

                <button type="button" id="validateBtn" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                    Validate QR Codes
                </button>
            </div>
        </form>

        <!-- Progress Display -->
        <div id="progressSection" class="hidden mt-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Regeneration Progress</h3>
                <div id="progressContent"></div>
            </div>
        </div>

        <!-- Results Display -->
        <div id="resultsSection" class="hidden mt-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Results</h3>
                <div id="resultsContent"></div>
            </div>
        </div>

        <!-- Test QR Display -->
        <div id="testQrSection" class="hidden mt-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Test QR Codes</h3>
                <div id="testQrContent" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
            </div>
        </div>

        <!-- Validation Results -->
        <div id="validationSection" class="hidden mt-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-3">QR Code Validation</h3>
                <div id="validationContent"></div>
            </div>
        </div>

        <!-- Testing Instructions -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-800 mb-2">📱 Testing Recommendations</h3>
            <div class="text-sm text-blue-700 space-y-2">
                <p><strong>1. Test QR codes with multiple scanner apps:</strong></p>
                <ul class="ml-4 space-y-1">
                    <li>• Built-in camera apps (iOS/Android)</li>
                    <li>• QR Code Reader apps</li>
                    <li>• Barcode Scanner apps</li>
                </ul>
                <p><strong>2. Test in different conditions:</strong></p>
                <ul class="ml-4 space-y-1">
                    <li>• Various lighting conditions</li>
                    <li>• Different distances and angles</li>
                    <li>• Both printed and screen display</li>
                </ul>
                <p><strong>3. Verify all equipment types are scannable</strong></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('regenerationForm');
    const regenerateBtn = document.getElementById('regenerateBtn');
    const regenerateText = document.getElementById('regenerateText');
    const regenerateSpinner = document.getElementById('regenerateSpinner');
    const testBtn = document.getElementById('testBtn');
    const validateBtn = document.getElementById('validateBtn');
    const progressSection = document.getElementById('progressSection');
    const resultsSection = document.getElementById('resultsSection');
    const testQrSection = document.getElementById('testQrSection');
    const validationSection = document.getElementById('validationSection');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        // Show loading state
        regenerateBtn.disabled = true;
        regenerateText.classList.add('hidden');
        regenerateSpinner.classList.remove('hidden');
        progressSection.classList.remove('hidden');
        resultsSection.classList.add('hidden');
        
        document.getElementById('progressContent').innerHTML = '<div class="text-blue-600">Starting QR code regeneration...</div>';
        
        fetch('{{ route("admin.qr.regenerate") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayResults(data.stats);
            } else {
                displayError(data.message);
            }
        })
        .catch(error => {
            displayError('Network error: ' + error.message);
        })
        .finally(() => {
            // Reset loading state
            regenerateBtn.disabled = false;
            regenerateText.classList.remove('hidden');
            regenerateSpinner.classList.add('hidden');
        });
    });

    testBtn.addEventListener('click', function() {
        testBtn.disabled = true;
        testBtn.textContent = 'Generating...';
        
        fetch('{{ route("admin.qr.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTestQr(data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Network error: ' + error.message);
        })
        .finally(() => {
            testBtn.disabled = false;
            testBtn.textContent = 'Generate Test QR';
        });
    });

    validateBtn.addEventListener('click', function() {
        const type = document.getElementById('equipmentType').value;
        if (type === 'all') {
            alert('Please select a specific equipment type for validation');
            return;
        }
        
        validateBtn.disabled = true;
        validateBtn.textContent = 'Validating...';
        
        fetch('{{ route("admin.qr.validate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ type: type, limit: 5 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayValidation(data.results);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Network error: ' + error.message);
        })
        .finally(() => {
            validateBtn.disabled = false;
            validateBtn.textContent = 'Validate QR Codes';
        });
    });

    function displayResults(stats) {
        progressSection.classList.add('hidden');
        resultsSection.classList.remove('hidden');
        
        let html = '<div class="overflow-x-auto"><table class="min-w-full table-auto">';
        html += '<thead><tr class="bg-gray-100"><th class="px-4 py-2 text-left">Equipment Type</th><th class="px-4 py-2 text-center">Success</th><th class="px-4 py-2 text-center">Total</th><th class="px-4 py-2 text-center">Percentage</th></tr></thead><tbody>';
        
        let totalSuccess = 0;
        let totalItems = 0;
        
        for (const [type, data] of Object.entries(stats)) {
            if (data.total > 0) {
                const percentage = Math.round((data.success / data.total) * 100);
                const rowClass = percentage === 100 ? 'bg-green-50' : (percentage > 80 ? 'bg-yellow-50' : 'bg-red-50');
                
                html += `<tr class="${rowClass}">`;
                html += `<td class="px-4 py-2 font-medium">${type.toUpperCase().replace('_', ' ')}</td>`;
                html += `<td class="px-4 py-2 text-center">${data.success}</td>`;
                html += `<td class="px-4 py-2 text-center">${data.total}</td>`;
                html += `<td class="px-4 py-2 text-center">${percentage}%</td>`;
                html += '</tr>';
                
                totalSuccess += data.success;
                totalItems += data.total;
            }
        }
        
        if (totalItems > 0) {
            const totalPercentage = Math.round((totalSuccess / totalItems) * 100);
            html += `<tr class="bg-blue-100 font-bold">`;
            html += `<td class="px-4 py-2">TOTAL</td>`;
            html += `<td class="px-4 py-2 text-center">${totalSuccess}</td>`;
            html += `<td class="px-4 py-2 text-center">${totalItems}</td>`;
            html += `<td class="px-4 py-2 text-center">${totalPercentage}%</td>`;
            html += '</tr>';
        }
        
        html += '</tbody></table></div>';
        
        if (totalSuccess === totalItems && totalItems > 0) {
            html += '<div class="mt-4 p-3 bg-green-100 border border-green-300 rounded text-green-800">✅ All QR codes regenerated successfully!</div>';
        } else if (totalSuccess < totalItems) {
            html += '<div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded text-yellow-800">⚠️ Some QR codes failed to regenerate. Check the logs for details.</div>';
        }
        
        document.getElementById('resultsContent').innerHTML = html;
    }

    function displayTestQr(data) {
        testQrSection.classList.remove('hidden');
        
        const html = `
            <div class="text-center">
                <h4 class="font-medium mb-2">Basic QR Code</h4>
                <img src="${data.basic_qr}" alt="Basic QR" class="mx-auto mb-2 border" style="max-width: 200px;">
                <p class="text-sm text-gray-600">Simple QR without visual elements</p>
            </div>
            <div class="text-center">
                <h4 class="font-medium mb-2">Visual QR Code</h4>
                <img src="${data.visual_qr}" alt="Visual QR" class="mx-auto mb-2 border" style="max-width: 200px;">
                <p class="text-sm text-gray-600">QR with improved layout and branding</p>
            </div>
            <div class="col-span-full mt-4 p-3 bg-blue-100 border border-blue-300 rounded">
                <p class="text-sm text-blue-800"><strong>Test URL:</strong> <a href="${data.test_url}" target="_blank" class="underline">${data.test_url}</a></p>
                <p class="text-sm text-blue-700 mt-1">Scan both QR codes with your phone to verify they work correctly</p>
            </div>
        `;
        
        document.getElementById('testQrContent').innerHTML = html;
    }

    function displayValidation(results) {
        validationSection.classList.remove('hidden');
        
        let html = '<div class="space-y-4">';
        
        results.forEach(result => {
            const hasIssues = result.issues.length > 0;
            const statusClass = hasIssues ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200';
            const statusIcon = hasIssues ? '❌' : '✅';
            
            html += `<div class="border rounded-lg p-4 ${statusClass}">`;
            html += `<div class="flex items-center justify-between mb-2">`;
            html += `<h5 class="font-medium">${statusIcon} ${result.serial_no}</h5>`;
            html += `<span class="text-sm text-gray-600">Size: ${result.file_size} bytes</span>`;
            html += `</div>`;
            
            if (result.qr_url) {
                html += `<div class="mb-2"><img src="${result.qr_url}" alt="QR Code" class="w-24 h-24 border rounded"></div>`;
            }
            
            if (result.issues.length > 0) {
                html += '<div class="text-sm text-red-700"><strong>Issues:</strong><ul class="ml-4 mt-1">';
                result.issues.forEach(issue => {
                    html += `<li>• ${issue}</li>`;
                });
                html += '</ul></div>';
            } else {
                html += '<div class="text-sm text-green-700">No issues detected</div>';
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        
        document.getElementById('validationContent').innerHTML = html;
    }

    function displayError(message) {
        progressSection.classList.add('hidden');
        resultsSection.classList.remove('hidden');
        document.getElementById('resultsContent').innerHTML = `<div class="p-3 bg-red-100 border border-red-300 rounded text-red-800">❌ Error: ${message}</div>`;
    }
});
</script>
@endsection