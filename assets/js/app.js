// ============================================
// Toggle sidebar: mode mobile (off-canvas) & desktop (collapse ciut)
// ============================================
function toggleSidebarMobile() {
    var sb = document.getElementById('mainSidebar');
    var bd = document.getElementById('sidebarBackdrop');
    if (!sb) return;
    sb.classList.toggle('mobile-open');
    if (bd) bd.classList.toggle('show');
}

function closeSidebarMobile() {
    var sb = document.getElementById('mainSidebar');
    var bd = document.getElementById('sidebarBackdrop');
    if (!sb) return;
    sb.classList.remove('mobile-open');
    if (bd) bd.classList.remove('show');
}

function toggleSidebarCollapse() {
    var sb = document.getElementById('mainSidebar');
    if (!sb) return;
    sb.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', sb.classList.contains('collapsed') ? '1' : '0');
}

document.addEventListener('DOMContentLoaded', function () {
    var sb = document.getElementById('mainSidebar');
    if (sb && localStorage.getItem('sidebarCollapsed') === '1' && window.innerWidth > 900) {
        sb.classList.add('collapsed');
    }
});
