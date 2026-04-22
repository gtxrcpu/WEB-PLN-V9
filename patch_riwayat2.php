<?php

// 1. Update Models
foreach (glob("app/Models/Kartu*.php") as $file) {
    if (str_contains($file, 'KartuTemplate')) continue;
    $content = file_get_contents($file);
    $newContent = preg_replace(
        '/public function isApproved\(\)\s*\{\s*return ! ?is_null\(\$this->approved_at\);\s*\}/s',
        "public function isApproved()\n    {\n        return !is_null(\$this->approved_at) || !is_null(\$this->leader_approved_at);\n    }",
        $content
    );
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated model $file\n";
    }
}

// 2. Update Blades
$files = [
    'resources/views/apar/riwayat.blade.php',
    'resources/views/apab/riwayat.blade.php',
    'resources/views/apat/riwayat.blade.php',
    'resources/views/box-hydrant/riwayat.blade.php',
    'resources/views/fire-alarm/riwayat.blade.php',
    'resources/views/p3k/riwayat.blade.php',
    'resources/views/rumah-pompa/riwayat.blade.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Status column
    $pattern1 = '/<td class="px-6 py-4">\s*@if\(\$kartu->isApproved\(\)\).*?<\/td>/s';
    
    $replace1 = <<<HTML
<td class="px-6 py-4">
                @if(\$kartu->isApproved())
                  <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approved (Selesai)
                  </span>
                @elseif(\$kartu->rejected_at || \$kartu->leader_rejected_at)
                  <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Direvisi ({{\$kartu->revisi}})
                  </span>
                @else
                  <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pending
                  </span>
                @endif
              </td>
HTML;

    // Di-approve Oleh column
    $pattern2 = '/<td class="px-6 py-4 text-sm text-gray-700">\s*@if\(\$kartu->isApproved\(\)\).*?<\/td>/s';
    
    $replace2 = <<<HTML
<td class="px-6 py-4 text-sm text-gray-700">
                @if(\$kartu->isApproved())
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @if(\$kartu->approved_at)
                      <div>
                        <div class="font-medium">Admin: {{ get_user_display_name(\$kartu->approver, 'Unknown Admin') }}</div>
                        <div class="text-xs text-gray-500">{{ \$kartu->approved_at->format('d M Y, H:i') }}</div>
                      </div>
                    @else
                      <div>
                        <div class="font-medium">Leader: {{ get_user_display_name(\$kartu->leaderApprover, 'Unknown Leader') }}</div>
                        <div class="text-xs text-gray-500">{{ \$kartu->leader_approved_at->format('d M Y, H:i') }}</div>
                      </div>
                    @endif
                  </div>
                @else
                  <span class="text-gray-400 italic">Belum di-approve</span>
                @endif
              </td>
HTML;

    // For p3k which uses cards
    $p3kPattern1 = '/@if\(\$kartu->rejected_at \|\| \$kartu->leader_rejected_at\)\s*<div class="flex items-center gap-2 pt-3 border-t border-slate-100">.*?@endif/s';
    $p3kReplace1 = <<<HTML
@if(\$kartu->rejected_at || \$kartu->leader_rejected_at)
                                <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-xs text-slate-500">Ditolak oleh</p>
                                        <p class="text-sm font-semibold text-red-700">{{ get_user_display_name(\$kartu->rejected_at ? \$kartu->rejector : \$kartu->leaderRejector, 'Unknown Rejector') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                            Direvisi ({{\$kartu->revisi}})
                                        </span>
                                    </div>
                                </div>
                            @elseif(\$kartu->isApproved())
                                <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @if(\$kartu->approved_at)
                                      <div class="flex-1">
                                          <p class="text-xs text-slate-500">Di-approve oleh Admin</p>
                                          <p class="text-sm font-semibold text-slate-900">{{ get_user_display_name(\$kartu->approver, 'Unknown Approver') }}</p>
                                      </div>
                                      <div class="text-right">
                                          <p class="text-xs text-slate-500">{{ \$kartu->approved_at->format('d M Y, H:i') }}</p>
                                      </div>
                                    @else
                                      <div class="flex-1">
                                          <p class="text-xs text-slate-500">Di-approve oleh Leader</p>
                                          <p class="text-sm font-semibold text-slate-900">{{ get_user_display_name(\$kartu->leaderApprover, 'Unknown Leader') }}</p>
                                      </div>
                                      <div class="text-right">
                                          <p class="text-xs text-slate-500">{{ \$kartu->leader_approved_at->format('d M Y, H:i') }}</p>
                                      </div>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-slate-600 italic">Menunggu approval</p>
                                </div>
                            @endif
HTML;

    if (str_contains($file, 'p3k')) {
        $newContent = preg_replace($p3kPattern1, $p3kReplace1, $content);
    } else {
        $newContent = preg_replace($pattern1, $replace1, $content);
        $newContent = preg_replace($pattern2, $replace2, $newContent);
    }
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Updated $file\n";
    }
}
