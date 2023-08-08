<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

global $admin_preview;

$admin_preview = $admin_preview ?? new \Syltaen\AdminPreviewController;

try {
    if (wp_doing_ajax()) {
        // Hot edition : render the content directly in an HTML file and display it in an iframe
        $admin_preview->directRender();

    } else {
        // First page loading : display using an iframe that will handle the rendering
        $admin_preview->offloadRender();
    }
} catch (\Throwable $e) {
    echo "<p class='error-message'>" . $e->getMessage() . "</p>";
    echo "<p class='error-message'>" . $e->getTraceAsString() . "</p>";
}
