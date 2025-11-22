/**
 * MODULE: config.js
 * DESKRIPSI: Berisi semua konfigurasi template, warna status, dan pengaturan chart
 * FUNGSI: Centralized configuration untuk memudahkan maintenance
 */

// ===== TEMPLATE CONFIGURATION =====
// Mendefinisikan tampilan visual dari setiap node dalam org chart
export function initializeTemplate() {
    // Extend dari template 'ana' yang sudah ada di OrgChart library
    OrgChart.templates.myTemplate = Object.assign({}, OrgChart.templates.ana);
    
    // Ukuran node: lebar 250px, tinggi 150px
    OrgChart.templates.myTemplate.size = [250, 150];

    // Node background: kotak dengan sudut rounded
    OrgChart.templates.myTemplate.node =
        `<rect x="0" y="0" width="250" height="150" fill="#ffffff"
            stroke="#cccccc" stroke-width="5" rx="10" ry="10"></rect>`;

    // Field untuk nama karyawan (paling atas)
    OrgChart.templates.myTemplate.field_ =
        `<text style="font-size:14px;font-weight:700;" fill="#212121" x="125" y="40" text-anchor="middle">{val}</text>`;

    // Field untuk grading (jabatan level)
    OrgChart.templates.myTemplate.fieldgrading =
        `<text style="font-size:13px;font-weight:600;" fill="#616161" x="125" y="60" text-anchor="middle">{val}</text>`;

    // Field untuk posisi
    OrgChart.templates.myTemplate.field_0 =
        `<text style="font-size:12px;font-weight:500;" fill="#424242" x="125" y="80" text-anchor="middle">{val}</text>`;

    // Field untuk lokasi
    OrgChart.templates.myTemplate.field_1 =
        `<text style="font-size:11px;font-weight:500;" fill="#757575" x="125" y="95" text-anchor="middle">{val}</text>`;

    // Background untuk status badge (dinamis berdasarkan warna)
    OrgChart.templates.myTemplate.field_2 =
        `<g transform="translate(60,105)">
            <rect width="130" height="25" rx="12" ry="12" fill="{val}"></rect>
        </g>`;

    // Text status (Active/Inactive/Vacant)
    OrgChart.templates.myTemplate.field_3 =
        `<text style="font-size:12px;font-weight:600;" fill="#ffffff" x="125" y="122" text-anchor="middle">{val}</text>`;
}

// ===== COLOR CONFIGURATION =====
// Mapping warna untuk setiap status karyawan
export const statusColors = {
    active: '#4CAF50',      // Hijau untuk aktif
    inactive: '#F44336',    // Merah untuk tidak aktif
    vacant: '#9E9E9E',      // Abu-abu untuk posisi kosong
};

// ===== CHART CONFIGURATION =====
// Pengaturan dasar untuk OrgChart instance
export const chartConfig = {
    template: "myTemplate",
    enableSearch: true,                          // Enable search bar
    mouseScrool: OrgChart.action.zoom,           // Mouse scroll untuk zoom
    scaleInitial: OrgChart.match.boundary,       // Initial zoom fit to boundary
    layout: OrgChart.normal,                     // Layout normal (top to bottom)
    
    // Jarak antar level dan node
    levelSeparation: 100,
    siblingSeparation: 100,
    
    // Mapping data ke field template
    nodeBinding: {
        field_: "Employee",        // Nama karyawan
        fieldgrading: "Grading",   // Level jabatan
        field_0: "Position",       // Posisi/role
        field_1: "Location",       // Lokasi kerja
        field_2: "statusColor",    // Warna status
        field_3: "status"          // Text status
    },

    // Toolbar configuration
    toolbar: {
        zoom: true,        // Tombol zoom in/out
        fit: true,         // Tombol fit to screen
        expandAll: true    // Tombol expand/collapse all
    },

    // Disable default context menu dan click action
    nodeMenu: null,
    nodeMouseClick: OrgChart.action.none
};

// ===== GRADING ORDER =====
// Urutan hierarki jabatan dari tertinggi ke terendah
export const gradingOrder = [
    'Director',
    'Head',
    'Senior Manager',
    'Manager',
    'Assistant Manager',
    'Supervisor',
    'Staff',
    'Daily Worker'
];

// ===== LAYOUT CONFIGURATION =====
// Pengaturan untuk positioning nodes berdasarkan grading
export const layoutConfig = {
    gradingGap: 220,  // Jarak vertikal antar grading level
    baseY: 80         // Y position awal untuk level pertama
};