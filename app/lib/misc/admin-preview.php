<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

global $admin_preview;

$admin_preview = $admin_preview ?? new \Syltaen\AdminPreviewController;

try {
    // Hot edition : render the content directly in an HTML file and display it in an iframe
    if (wp_doing_ajax()) {
        $admin_preview->directRender();

    // First page loading : display using an iframe that will handle the rendering
    } else {
        $admin_preview->offloadRender();
    }
} catch (\Throwable$e) {
    echo $e->getMessage();
}