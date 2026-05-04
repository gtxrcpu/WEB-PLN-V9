@props(['units', 'selectedUnit' => null, 'module' => 'equipment'])

<div class="space-y-4">
    {{-- Unit Filter & Search Box --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Unit Selection Dropdown --}}
        <div class="relative">
            <label class="block text-sm font-medium text-slate-700 mb-2">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Filter Unit
            </label>
            <select id="unitFilter" 
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    onchange="filterByUnit()">
                <option value="">Semua Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ $selectedUnit && $selectedUnit->id == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Search Box --}}
        <div class="relative">
            <label class="block text-sm font-medium text-slate-700 mb-2">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Pencarian
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       id="searchInput"
                       placeholder="Cari serial number, barcode, atau lokasi..." 
                       class="w-full pl-12 pr-4 py-3 border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       onkeyup="filterItems()">
            </div>
        </div>
    </div>

    {{-- Selected Unit Info --}}
    @if($selectedUnit)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-600 font-medium">Menampilkan data untuk:</p>
                    <p class="text-lg font-bold text-blue-900">{{ $selectedUnit->name }}</p>
                </div>
            </div>
            <button onclick="clearUnitFilter()" class="px-4 py-2 bg-white border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors text-sm font-medium">
                Tampilkan Semua
            </button>
        </div>
    @endif
</div>

<script>
function filterByUnit() {
    const unitId = document.getElementById('unitFilter').value;
    const currentUrl = new URL(window.location.href);
    
    if (unitId) {
        currentUrl.searchParams.set('unit_id', unitId);
    } else {
        currentUrl.searchParams.delete('unit_id');
    }
    
    // Show loading state
    showLoadingState();
    
    // Redirect to filtered URL
    window.location.href = currentUrl.toString();
}

function clearUnitFilter() {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete('unit_id');
    
    // Show loading state
    showLoadingState();
    
    window.location.href = currentUrl.toString();
}

function showLoadingState() {
    const container = document.querySelector('[data-item]')?.parentElement;
    if (container) {
        container.innerHTML = `
            <div class="col-span-full flex items-center justify-center py-12">
                <div class="text-center">
                    <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-slate-600 font-medium">Memuat data...</p>
                </div>
            </div>
        `;
    }
}

function filterItems() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('[data-item]');
    let visibleCount = 0;

    items.forEach(item => {
        const serialNo = item.getAttribute('data-serial')?.toLowerCase() || '';
        const barcode = item.getAttribute('data-barcode')?.toLowerCase() || '';
        const location = item.getAttribute('data-location')?.toLowerCase() || '';
        
        const isMatch = serialNo.includes(searchValue) || 
                       barcode.includes(searchValue) || 
                       location.includes(searchValue);
        
        if (isMatch) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    const noResults = document.getElementById('noResults');
    if (noResults) {
        noResults.style.display = visibleCount === 0 && searchValue.length > 0 ? 'block' : 'none';
    }
}
</script>