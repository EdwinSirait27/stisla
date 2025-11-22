/**
 * MODULE: links.js
 * DESKRIPSI: Mengelola semua garis koneksi (primary & secondary)
 * FUNGSI:
 * - Redraw primary links (parent-child)
 * - Draw secondary links (dotted/dashed lines)
 * - Toggle visibility secondary links
 */

/**
 * Redraw semua garis koneksi primary (parent-child)
 * Dipanggil setelah node positions di-adjust
 */
export function redrawAllLinks(nodePositions) {
    if (!window.orgData) return;
    
    const treeElement = document.getElementById("tree");
    const SVG = treeElement?.querySelector('svg');
    if (!SVG) return;
    
    // Hapus semua path lama (kecuali secondary links)
    const oldPaths = SVG.querySelectorAll('path:not(.secondary-link)');
    oldPaths.forEach(path => path.remove());
    
    // Buat path baru berdasarkan hierarchy
    window.orgData.forEach(node => {
        if (!node.pid) return; // Skip root node (tidak punya parent)
        
        const childPos = nodePositions.get(node.id);
        const parentPos = nodePositions.get(node.pid);
        const childData = window.orgData.find(d => d.id == node.id);
        const parentData = window.orgData.find(d => d.id == node.pid);
        
        if (childPos && parentPos && childData && parentData) {
            // Koordinat untuk koneksi
            const childX = childPos.x + 125;  // Tengah node (250/2)
            const childY = childPos.y;        // Top of child
            const parentX = parentPos.x + 125;
            const parentY = parentPos.y + 150; // Bottom of parent node
            
            // Cek perbedaan level untuk menentukan style garis
            const levelDiff = Math.abs((childData.level || 0) - (parentData.level || 0));
            
            let pathData;
            if (levelDiff > 1) {
                // Skip level: buat garis dengan segmen tambahan
                const quarterY = parentY + (childY - parentY) * 0.25;
                const threeQuarterY = parentY + (childY - parentY) * 0.75;
                pathData = `M ${parentX} ${parentY} L ${parentX} ${quarterY} L ${childX} ${threeQuarterY} L ${childX} ${childY}`;
            } else {
                // Garis normal: vertikal -> horizontal -> vertikal
                const midY = (parentY + childY) / 2;
                pathData = `M ${parentX} ${parentY} L ${parentX} ${midY} L ${childX} ${midY} L ${childX} ${childY}`;
            }
            
            // Buat SVG path element
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", pathData);
            path.setAttribute("stroke", "#cccccc");
            path.setAttribute("stroke-width", "2");
            path.setAttribute("fill", "none");
            path.setAttribute("class", "orgchart-link");
            
            // Insert di awal SVG agar tidak menutupi nodes
            SVG.insertBefore(path, SVG.firstChild);
        }
    });
}

/**
 * Draw secondary links (garis putus-putus merah)
 * Untuk menunjukkan hubungan tambahan/sekunder
 */
export function drawSecondaryLinks(chart) {
    const treeElement = document.getElementById("tree");
    if (!treeElement) return;

    const SVG = treeElement.querySelector('svg');
    if (!SVG) return;

    // Hapus secondary links yang sudah ada
    const existingLinks = SVG.querySelectorAll('.secondary-link');
    existingLinks.forEach(link => link.remove());

    if (!window.orgData) return;

    // Loop setiap node yang punya secondary relationship
    window.orgData.forEach(node => {
        if (!node.secondary || node.secondary.length === 0) return;

        node.secondary.forEach(secData => {
            // Get ID dari secondary node
            const secId = typeof secData === 'object' ? secData.id : secData;
            const fromNode = chart.getNode(secId);
            const toNode = chart.getNode(node.id);

            if (!fromNode || !toNode) return;

            // Koordinat untuk kurva bezier
            const fx = fromNode.x + fromNode.w / 2;  // From X (center)
            const fy = fromNode.y + fromNode.h;      // From Y (bottom)
            const tx = toNode.x + toNode.w / 2;      // To X (center)
            const ty = toNode.y;                      // To Y (top)

            if (isNaN(fx) || isNaN(fy) || isNaN(tx) || isNaN(ty)) return;

            // Bezier curve untuk garis melengkung
            const midY = (fy + ty) / 2;
            const pathData = `M ${fx} ${fy} C ${fx} ${midY}, ${tx} ${midY}, ${tx} ${ty}`;

            // Buat path dengan style khusus
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", pathData);
            path.setAttribute("stroke", "#FF5722");           // Merah/orange
            path.setAttribute("stroke-width", "5");
            path.setAttribute("stroke-dasharray", "15,8");    // Dashed
            path.setAttribute("fill", "none");
            path.setAttribute("class", "secondary-link");
            path.setAttribute("stroke-linecap", "round");

            SVG.appendChild(path);
        });
    });
}

/**
 * Setup toggle button untuk show/hide secondary links
 */
export function setupSecondaryLinksToggle() {
    let secondaryLinksVisible = true;
    const toggleButton = document.getElementById('toggleSecondaryLinks');
    
    if (!toggleButton) return;

    toggleButton.addEventListener('click', function() {
        const treeElement = document.getElementById("tree");
        const SVG = treeElement ? treeElement.querySelector('svg') : null;
        const toggleText = document.getElementById('toggleText');
        const icon = this.querySelector('i');

        if (!SVG) return;

        const secondaryLinks = SVG.querySelectorAll('.secondary-link');

        if (secondaryLinksVisible) {
            // Hide secondary links
            secondaryLinks.forEach(link => link.style.display = 'none');
            if (toggleText) toggleText.textContent = 'Show Secondary Links';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-outline-secondary');
        } else {
            // Show secondary links
            secondaryLinks.forEach(link => link.style.display = 'block');
            if (toggleText) toggleText.textContent = 'Hide Secondary Links';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-outline-primary');
        }
        
        secondaryLinksVisible = !secondaryLinksVisible;
    });
}