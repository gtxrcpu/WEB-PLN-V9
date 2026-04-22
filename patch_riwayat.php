<?php

$files = [
    'resources/views/apab/riwayat.blade.php',
    'resources/views/box-hydrant/riwayat.blade.php',
    'resources/views/fire-alarm/riwayat.blade.php',
    'resources/views/p3k/riwayat.blade.php',
    'resources/views/rumah-pompa/riwayat.blade.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Attempt to match the old Status block pattern
    $pattern1 = '/<td class="px-6 py-4">\s*@if\(\$kartu->isApproved\(\)\).*?<\/td>/s';
    $pattern_alt = '/<td class="px-6 py-4">\s*@if\(\$kartu->rejected_at\).*?<\/td>/s';

    $replace1 = '<td class="px-6 py-4">
                @if($kartu->isApproved())
                  <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approved (Selesai)
                  </span>
                @elseif($kartu->rejected_at || $kartu->leader_rejected_at)
                  <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Direvisi ({{ $kartu->revisi }})
                  </span>
                @elseif($kartu->leader_approved_at)
                  <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Menunggu Admin
                  </span>
                @else
                  <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pending Leader
                  </span>
                @endif
              </td>';

    // Replace the Details block (usually follows the Status block)
    $pattern2 = '/<td class="px-6 py-4 text-sm text-gray-700">\s*@if\(\$kartu->isApproved\(\)\)\s*<div class="flex items-center gap-2">.*?<\/td>/s';

    $replace2 = '<td class="px-6 py-4 text-sm text-gray-700">
                @if($kartu->isApproved())
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                      <div class="font-medium">Admin: {{ get_user_display_name($kartu->approver, \'Unknown Admin\') }}</div>
                      <div class="text-xs text-gray-500">{{ $kartu->approved_at->format(\'d M Y, H:i\') }}</div>
                    </div>
                  </div>
                @elseif($kartu->leader_approved_at)
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                      <div class="font-medium">Leader: {{ get_user_display_name($kartu->leaderApprover, \'Unknown Leader\') }}</div>
                      <div class="text-xs text-gray-500">{{ $kartu->leader_approved_at->format(\'d M Y, H:i\') }}</div>
                    </div>
                  </div>
                @else
                  <span class="text-gray-400 italic">Belum di-approve</span>
                @endif
              </td>';

    $newContent = preg_replace($pattern1, $replace1, $content);
    if ($newContent === $content) {
        $newContent = preg_replace($pattern_alt, $replace1, $content);
    }
    $newContent = preg_replace($pattern2, $replace2, $newContent);
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Updated $file\n";
    }
}
