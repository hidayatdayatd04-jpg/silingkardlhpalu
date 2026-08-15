@props(['name', 'size' => 20, 'stroke' => 2, 'filled' => null])

@php
    // Logo berwarna (filled) otomatis untuk ikon merek seperti WhatsApp.
    $filled = $filled ?? ($name === 'whatsapp');

    $icons = [
        // Navigation & UI
        'dashboard' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v8h-6z"/><path d="M4 16h6v4h-6z"/><path d="M14 12h6v8h-6z"/><path d="M14 4h6v4h-6z"/>',
        'home' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/>',
        'menu' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 6l16 0"/><path d="M4 12l16 0"/><path d="M4 18l16 0"/>',
        'chevron-down' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 9l6 6l6 -6"/>',
        'chevron-up' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 15l6 -6l6 6"/>',
        'chevron-left' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6"/>',
        'chevron-right' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6"/>',
        'x' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/>',
        'search' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/>',
        'filter' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z"/>',
        'dots-vertical' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>',
        
        // Actions
        'plus' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/>',
        'edit' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/>',
        'trash' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>',
        'eye' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/>',
        'eye-off' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.585a2 2 0 1 0 2.829 2.829"/><path d="M16.249 12.249c1.178 -1.178 2.743 -1.833 4.249 -1.833a5 5 0 0 1 1.714 9.622"/><path d="M5 12c-1.811 -1.843 -3 -4.004 -3 -6.5a9.846 9.846 0 0 1 2.675 -6.073"/><path d="M20.999 12a9.846 9.846 0 0 1 -2.675 6.073"/><path d="M12 3c-4.97 0 -9 4.03 -9 9"/><path d="M12 21c4.97 0 9 -4.03 9 -9"/><path d="M3.999 12c0 -4.97 4.03 -9 9 -9"/>',
        'at-sign' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M16 8v5a3 3 0 0 0 6 0v-10l-4 4"/><path d="M4 4v16"/><path d="M4 11h8"/>',
        'check-circle' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 12l2 2l4 -4"/>',
        'download' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/><path d="M7 11l5 5l5 -5"/><path d="M12 4l0 12"/>',
        'upload' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/><path d="M7 9l5 -5l5 5"/><path d="M12 4l0 12"/>',
        'refresh' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>',
        'copy' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"/><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"/>',
        'check' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/>',
        
        // Data & Content
        'file-text' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M9 9l1 0"/><path d="M9 13l6 0"/><path d="M9 17l6 0"/>',
        'folder' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"/>',
        'table' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-14z"/><path d="M3 10l18 0"/><path d="M10 3l0 18"/>',
        'chart' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/>',
        'trending-up' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6l4 4l8 -8"/><path d="M14 7l7 0l0 7"/>',
        'calendar' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/>',
        'clock' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path d="M12 7v5l3 3"/>',
        
        // Users & Social
        'user' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>',
        'users' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>',
        'mail' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"/><path d="M3 7l9 6l9 -6"/>',
        'bell' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/>',
        'message' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8"/><path d="M8 13h6"/><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>',
        
        // Status & Alerts
        'alert-circle' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
        'info-circle' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path d="M12 9h.01"/><path d="M11 12h1v4h1"/>',
        'circle-check' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 12l2 2l4 -4"/>',
        'alert-triangle' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/>',
        
        // Settings & System
        'settings' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>',
        'logout' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M9 12h12l-3 -3"/><path d="M18 15l3 -3"/>',
        'lock' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/>',
        
        // Environment & Nature
        'tree' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 13l-2 -2"/><path d="M12 12l2 -2"/><path d="M12 21v-13"/><path d="M9.824 16a3 3 0 0 1 -2.743 -3.69a3 3 0 0 1 .304 -4.833a3 3 0 0 1 4.615 -3.707a3 3 0 0 1 4.614 3.707a3 3 0 0 1 .305 4.833a3 3 0 0 1 -2.919 3.695"/>',
        'leaf' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21c.5 -4.5 2.5 -8 7 -10"/><path d="M9 18c6.218 0 10.5 -3.288 11 -12v-2h-4.014c-9 0 -11.986 4 -12 9c0 1 0 3 2 5h3z"/>',
        'recycle' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17l-2 2l2 2"/><path d="M10 19h9a2 2 0 0 0 1.75 -2.75l-.55 -1"/><path d="M8.536 11l-.732 -2.732l-2.732 .732"/><path d="M7.804 8.268l-4.5 7.794a2 2 0 0 0 1.506 2.89l1.141 .024"/><path d="M15.464 11l2.732 .732l.732 -2.732"/><path d="M18.196 11.732l-4.5 -7.794a2 2 0 0 0 -3.256 -.14l-.591 .976"/>',
        'building' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0"/><path d="M9 8l1 0"/><path d="M9 12l1 0"/><path d="M9 16l1 0"/><path d="M14 8l1 0"/><path d="M14 12l1 0"/><path d="M14 16l1 0"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/>',
        'map-pin' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"/>',
        'megaphone' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 4a1 1 0 0 1 .707 .293l4 4a1 1 0 0 1 0 1.414l-7.789 7.789a2 2 0 0 1 -1.414 .586l-5.586 0a1 1 0 0 1 -1 -1v-5.586a2 2 0 0 1 .586 -1.414l7.789 -7.789a1 1 0 0 1 .707 -.293z"/><path d="M16 12l0 4"/>',
        'clipboard-check' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/><path d="M9 14l2 2l4 -4"/>',
        'factory' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 11l0 -5l-4 0l0 -3l-4 0l0 3l-4 0l0 9"/><path d="M4 15l0 -3l4 0"/><path d="M12 15l0 -9l4 0l0 5"/><path d="M4 21l16 0"/>',
        'clipboard-list' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/><path d="M9 14l-2 -2l2 -2"/><path d="M9 7l6 0"/><path d="M9 11l6 0"/>',
        'list' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 0"/><path d="M9 12l6 0"/><path d="M9 18l6 0"/><path d="M5 6l0 .01"/><path d="M5 12l0 .01"/><path d="M5 18l0 .01"/>',
        'trash-x' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/><path d="M10 11l4 4"/><path d="M14 11l-4 4"/>',
        'package' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9z"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/><path d="M16.5 7.5l0 0"/>',
        'truck' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"/>',
        'chart-bar' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 20v-8"/><path d="M18 20v-12"/><path d="M6 20v-4"/>',
        'axe' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0"/><path d="M13 21l0 -5l-6 0"/><path d="M10 12l-7 -4l7 -4l4 4l-4 4"/>',
        'park-bench' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 18v-6a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6"/><path d="M2 18h20"/><path d="M4 14v4"/><path d="M20 14v4"/><path d="M6 14v-4a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v4"/>',
        'park' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 22l-4 -4h-2a2 2 0 0 1 -2 -2v-5a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5a2 2 0 0 1 -2 2h-2z"/><path d="M12 2v-0"/><path d="M12 2l-2 3h4z"/><path d="M12 2l2 3h-4z"/>',
        'forest' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l-5 8h3l-2 5h3l-2 5h8l-2 -5h3l-2 -5h3z"/>',
        'route' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6"/><path d="M15 17l6 -6"/><path d="M6 11l6 -6l6 6"/><circle cx="6" cy="17" r="2"/><circle cx="18" cy="17" r="2"/>',
        'seedling' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 22l0 -10"/><path d="M6 10c0 -2.2 2.7 -4 6 -4s6 1.8 6 4"/><path d="M6 10c-1.7 0 -3 1.8 -3 4c0 2.2 1.3 4 3 4"/><path d="M18 10c1.7 0 3 1.8 3 4c0 2.2 -1.3 4 -3 4"/>',
        'shield' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4v5c0 5.25 -3.5 9.74 -8 11c-4.5 -1.26 -8 -5.75 -8 -11v-5z"/>',
        'bin' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>',
        'presentation' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 20l-12 -2l0 -14l12 2l0 12z"/><path d="M3 20l0 -10"/><path d="M15 10l6 -2"/>',
        'news' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19 20h-14a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2z"/><path d="M7 10h2"/><path d="M7 14h6"/><path d="M13 10h4"/>',
        'star' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2l3 6l7 1l-5 5l1 7l-6 -3l-6 3l1 -7l-5 -5l7 -1z"/>',
        'send' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 2l-7 20l-4 -9l-9 -4z"/><path d="M22 2l-11 11"/>',
        'user-check' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M15 11l2 2l4 -4"/>',
        'whatsapp' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.102.117-.204.131-.378.044-.174-.087-.735-.271-1.4-.863-.517-.46-.866-1.03-1.046-1.203-.087-.174-.009-.268.066-.355.068-.068.174-.174.262-.261.087-.087.116-.152.174-.262.058-.087.029-.174-.015-.261-.044-.087-.394-1.016-.555-1.39-.145-.37-.295-.32-.394-.326-.102-.006-.222-.008-.34-.008a.656.656 0 0 0-.47.222c-.16.174-.62.607-.62 1.48 0 .873.638 1.716.727 3.074.149.198 2.096 3.2 5.077 4.487.421.306 1.262.489 1.694.373.422.135 1.36.116 1.871.07.339-.05 1.03-.42 1.176-.827.145-.407.145-.756.102-.827-.044-.07-.16-.116-.334-.203m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 0-1.51-5.26c0-5.45 4.215-9.396 9.397-9.396 1.74 0 3.365-.474 4.77-1.296l4.572 1.203-1.229-4.38a9.32 9.32 0 0 0 2.182-5.98c0-5.45-4.216-9.397-9.397-9.397M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.358.101 11.94c-.003 2.074.543 4.097 1.577 5.885l-1.657 6.115 6.18-1.621a11.9 11.9 0 0 0 5.845 1.49h.005c6.582 0 11.942-5.358 11.945-11.94a11.857 11.857 0 0 0-3.486-8.42"/>',
        
        // Topbar icons
        'command' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 9l0 -4a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-2"/><path d="M17 9l0 -4a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v4a1 1 0 0 0 1 1h2"/><path d="M7 9l0 6a1 1 0 0 0 1 1h2"/><path d="M17 9l0 6a1 1 0 0 1 -1 1h-2"/>',
        'sun' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"/>',
        'moon' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a9 9 0 1 0 9 9a0.8 .8 0 0 0 -.9 -.9a6.7 6.7 0 0 1 -7.4 -7.4a0.8 .8 0 0 0 -.7 -.9a9 9 0 0 0 -3 -.7"/>',
        'grid' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h4v4h-4z"/><path d="M16 4h4v4h-4z"/><path d="M4 16h4v4h-4z"/><path d="M16 16h4v4h-4z"/>',
        'external-link' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10"/><path d="M17 7l0 10m-10 0l10 0"/>',
        'file-plus' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M12 11l0 6"/><path d="M9 14l6 0"/>',
        'message-plus' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8"/><path d="M8 13h6"/><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/><path d="M16 18l4 -4"/><path d="M14 16l4 0l0 -4"/>',
        'database' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 3 0 1 0 18 0a9 3 0 1 0 -18 0"/><path d="M3 12h18"/><path d="M3 15a9 3 0 0 0 18 0"/><path d="M3 6a9 3 0 0 1 18 0"/>',
        'user-plus' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 11l4 0"/><path d="M18 9l0 4"/>',
        'book-open' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/><path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/><path d="M3 6l0 13"/><path d="M12 6l0 13"/><path d="M21 6l0 13"/>',
        'palette' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2c4.5 0 8 3.5 8 8c0 3 -1.5 5.5 -4 7.5c-1 .8 -2 1.5 -2 2.5v2h-4v-2c0 -1 -1 -1.7 -2 -2.5c-2.5 -2 -4 -4.5 -4 -7.5c0 -4.5 3.5 -8 8 -8z"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M7 9m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M17 9m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>',
    ];
@endphp

<svg 
    {{ $attributes->merge(['class' => 'inline-block shrink-0']) }} 
    xmlns="http://www.w3.org/2000/svg" 
    width="{{ $size }}" 
    height="{{ $size }}" 
    viewBox="0 0 24 24" 
    fill="{{ $filled ? 'currentColor' : 'none' }}" 
    stroke="{{ $filled ? 'none' : 'currentColor' }}" 
    stroke-width="{{ $stroke }}" 
    stroke-linecap="round" 
    stroke-linejoin="round"
>
    {!! $icons[$name] ?? $icons['alert-circle'] !!}
</svg>
