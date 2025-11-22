/**
 * MODULE: positioning.js
 * DESKRIPSI: Mengatur posisi node berdasarkan grading level
 * FUNGSI: 
 * - Adjust Y position nodes agar sejajar per grading
 * - Menjaga konsistensi hierarki visual
 */

import { layoutConfig } from './config.js';

/**
 * Force adjust posisi semua node berdasarkan grading level
 * Dijalankan setelah chart selesai render
 */
export function forceAdjustNodesByGrading() {
    // Validasi data global tersedia
    if (!window.orgData) return;
    
    const treeElement = document.getElementById("tree");
    if (!treeElement) return;
    
    const SVG = treeElement.querySelector('svg');
    if (!SVG) return;
    
    const { gradingGap, baseY } = layoutConfig;
    
    // Map untuk menyimpan posisi setiap node
    const nodePositions = new Map();
    
    // Ambil semua node groups dari SVG
    const nodeGroups = SVG.querySelectorAll('[node-id]');
    
    nodeGroups.forEach(nodeGroup => {
        const nodeId = nodeGroup.getAttribute('node-id');
        const nodeData = window.orgData.find(d => d.id == nodeId);
        
        if (nodeData && nodeData.level !== undefined) {
            // Hitung Y position berdasarkan grading level
            // Semakin tinggi level, semakin ke bawah posisinya
            const targetY = baseY + (nodeData.level * gradingGap);
            
            // Parse current transform attribute
            const currentTransform = nodeGroup.getAttribute('transform') || 'translate(0,0)';
            const matches = currentTransform.match(/translate\(([^,]+),\s*([^)]+)\)/);
            
            if (matches) {
                const currentX = parseFloat(matches[1]);
                
                // Update transform dengan Y position baru
                nodeGroup.setAttribute('transform', `translate(${currentX}, ${targetY})`);
                
                // Simpan posisi untuk keperluan redraw links
                nodePositions.set(nodeId, { x: currentX, y: targetY });
            }
        }
    });
    
    return nodePositions;
}

/**
 * Get node position dari SVG transform
 * Helper function untuk mendapatkan koordinat node
 */
export function getNodePosition(nodeId) {
    const treeElement = document.getElementById("tree");
    if (!treeElement) return null;
    
    const SVG = treeElement.querySelector('svg');
    if (!SVG) return null;
    
    const nodeGroup = SVG.querySelector(`[node-id="${nodeId}"]`);
    if (!nodeGroup) return null;
    
    const transform = nodeGroup.getAttribute('transform') || 'translate(0,0)';
    const matches = transform.match(/translate\(([^,]+),\s*([^)]+)\)/);
    
    if (matches) {
        return {
            x: parseFloat(matches[1]),
            y: parseFloat(matches[2])
        };
    }
    
    return null;
}