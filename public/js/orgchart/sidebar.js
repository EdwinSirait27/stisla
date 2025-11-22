/**
 * MODULE: sidebar.js
 * DESKRIPSI: Mengelola sidebar filter berdasarkan grading
 * FUNGSI:
 * - Populate sidebar dengan daftar grading
 * - Filter chart berdasarkan grading yang dipilih
 * - Inject CSS styles untuk sidebar
 */

import { gradingOrder } from './config.js';
import { drawSecondaryLinks } from './links.js';

/**
 * Inject inline CSS styles untuk sidebar
 * Dipanggil sekali saat initialization
 */
export function injectSidebarStyles() {
    // Cek apakah styles sudah di-inject
    if (document.getElementById('inlineSidebarStyles')) return;
    
    const style = document.createElement('style');
    style.id = 'inlineSidebarStyles';
    style.textContent = `
        .grading-sidebar-inline {
            width: 220px;
            height: 700px;
            background: #fafafa;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        .grading-sidebar-inline .sidebar-header {
            padding: 20px 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: 3px solid #5568d3;
            flex-shrink: 0;
        }
        
        .grading-sidebar-inline .sidebar-header h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .grading-sidebar-inline .sidebar-header i {
            font-size: 14px;
        }
        
        .grading-sidebar-inline .sidebar-content {
            padding: 10px 0;
            flex: 1;
            overflow-y: auto;
        }
        
        .grading-sidebar-inline .grading-item {
            padding: 12px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            margin: 2px 0;
        }
        
        .grading-sidebar-inline .grading-item:hover {
            background: #e3f2fd;
            border-left-color: #667eea;
        }
        
        .grading-sidebar-inline .grading-item.active {
            background: #e8eaf6;
            border-left-color: #667eea;
            font-weight: 600;
        }
        
        .grading-sidebar-inline .grading-badge {
            font-size: 13px;
            font-weight: 500;
            color: #424242;
            flex: 1;
        }
        
        .grading-sidebar-inline .grading-count {
            background: #bdbdbd;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        
        .grading-sidebar-inline .grading-item.active .grading-count {
            background: #667eea;
            color: white;
        }
        
        .grading-sidebar-inline .all-badge {
            color: #667eea;
            font-weight: 700;
        }
        
        /* Custom Scrollbar */
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 3px;
        }
        
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: #5568d3;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .grading-sidebar-inline {
                width: 70px;
            }
            
            .grading-sidebar-inline .grading-badge {
                font-size: 10px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .grading-sidebar-inline .sidebar-header h6 {
                font-size: 11px;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Populate sidebar dengan daftar grading dan count
 */
export function populateGradingSidebar(data) {
    // Hitung jumlah node per grading
    const gradingCounts = {};
    data.forEach(node => {
        const grading = node.Grading || 'Empty';
        gradingCounts[grading] = (gradingCounts[grading] || 0) + 1;
    });

    const gradingListContainer = document.getElementById('gradingList');
    
    if (!gradingListContainer) {
        console.error('Grading list container not found');
        return;
    }

    // Update count "All"
    const countAllElement = document.getElementById('count-all');
    if (countAllElement) {
        countAllElement.textContent = data.length;
    }

    // Clear existing items (kecuali "All")
    const existingItems = gradingListContainer.querySelectorAll('.grading-item:not([data-grading="all"])');
    existingItems.forEach(item => item.remove());

    // Tambahkan grading items sesuai urutan
    gradingOrder.forEach(grading => {
        if (gradingCounts[grading]) {
            const item = document.createElement('div');
            item.className = 'grading-item';
            item.dataset.grading = grading;
            item.innerHTML = `
                <span class="grading-badge">${grading}</span>
                <span class="grading-count">${gradingCounts[grading]}</span>
            `;
            gradingListContainer.appendChild(item);
        }
    });
}

/**
 * Setup event listeners untuk filter grading
 */
export function setupGradingFilter(chart) {
    document.querySelectorAll('.grading-item').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active dari semua item
            document.querySelectorAll('.grading-item').forEach(i => i.classList.remove('active'));
            
            // Tambahkan active ke item yang dipilih
            this.classList.add('active');

            const selectedGrading = this.dataset.grading;
            filterByGrading(selectedGrading, chart);
        });
    });
}

/**
 * Filter chart berdasarkan grading yang dipilih
 */
function filterByGrading(grading, chart) {
    if (!window.orgData) return;

    if (grading === 'all') {
        // Tampilkan semua data
        chart.load(window.orgData);
    } else {
        // Filter berdasarkan grading
        const filtered = [];
        const includedIds = new Set();

        // Collect matching nodes (sesuai grading)
        window.orgData.forEach(node => {
            if (node.Grading === grading) {
                filtered.push(node);
                includedIds.add(node.id);
            }
        });

        // Tambahkan parent nodes yang diperlukan
        // Agar hierarchy tetap terhubung
        window.orgData.forEach(node => {
            if (node.Grading === grading) {
                let parentId = node.pid;
                
                // Trace ke atas sampai root
                while (parentId) {
                    if (!includedIds.has(parentId)) {
                        const parentNode = window.orgData.find(n => n.id === parentId);
                        if (parentNode) {
                            filtered.push(parentNode);
                            includedIds.add(parentId);
                            parentId = parentNode.pid;
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                }
            }
        });

        chart.load(filtered);
    }

    // Redraw secondary links setelah filter
    setTimeout(() => drawSecondaryLinks(chart), 500);
}