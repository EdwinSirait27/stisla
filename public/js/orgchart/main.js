/**
 * MODULE: main.js
 * DESKRIPSI: Entry point utama aplikasi
 * FUNGSI:
 * - Initialize semua modul
 * - Setup chart instance
 * - Fetch dan load data
 * - Setup event listeners
 */

// Import semua modul
import { 
    initializeTemplate, 
    statusColors, 
    chartConfig 
} from './config.js';

import { forceAdjustNodesByGrading } from './positioning.js';

import { 
    redrawAllLinks, 
    drawSecondaryLinks, 
    setupSecondaryLinksToggle 
} from './links.js';

import { 
    injectSidebarStyles, 
    populateGradingSidebar, 
    setupGradingFilter 
} from './sidebar.js';

// ===== INITIALIZATION =====

// 1. Initialize template
initializeTemplate();

// 2. Inject sidebar styles
injectSidebarStyles();

// 3. Create OrgChart instance
const chart = new OrgChart(document.getElementById("tree"), chartConfig);

// ===== EVENT HANDLERS =====

/**
 * Event: Chart initialized
 * Dipanggil setelah chart pertama kali di-render
 */
chart.on("init", function() {
    console.log("Chart initialized");
    setTimeout(() => drawSecondaryLinks(chart), 500);
});

/**
 * Event: Chart redrawn
 * Dipanggil setiap kali chart di-redraw (zoom, pan, etc)
 */
chart.on("redraw", function() {
    console.log("Chart redrawn");
    setTimeout(() => drawSecondaryLinks(chart), 300);
});

// ===== DATA LOADING =====

/**
 * Fetch data dari server dan initialize chart
 * Pastikan route 'orgchart.orgchart' sudah ada di Laravel
 */
fetch("{{ route('orgchart.orgchart') }}")
    .then(res => res.json())
    .then(data => {
        console.log("Data loaded:", data.length, "nodes");
        
        // Process data: tambahkan statusColor
        const processed = data.map(node => ({
            ...node,
            statusColor: statusColors[(node.status || '').toLowerCase()] || '#9E9E9E'
        }));

        // Simpan di global variable untuk akses dari modul lain
        window.orgData = processed;
        
        // 1. Populate sidebar
        populateGradingSidebar(processed);
        
        // 2. Setup filter event listeners
        setupGradingFilter(chart);
        
        // 3. Load chart dengan data
        chart.load(processed);
        
        // 4. Draw secondary links setelah delay
        setTimeout(() => drawSecondaryLinks(chart), 1000);
        
        // 5. Setup toggle button
        setupSecondaryLinksToggle();
    })
    .catch(err => {
        console.error('Error loading org chart data:', err);
        alert('Failed to load organization chart data. Please refresh the page.');
    });

// ===== OPTIONAL: Manual Positioning Adjustment =====
// Uncomment jika ingin menggunakan manual positioning

// chart.on('redraw', function() {
//     const positions = forceAdjustNodesByGrading();
//     if (positions) {
//         redrawAllLinks(positions);
//     }
// });

// ===== EXPORT UNTUK DEBUGGING =====
window.orgChart = chart; // Untuk akses dari console
console.log("OrgChart initialized. Access via window.orgChart");