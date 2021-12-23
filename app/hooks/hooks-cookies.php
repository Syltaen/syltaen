<?php

namespace Syltaen;

/**
 * Add consent mode defaults
 */
add_action("wp_head", function () {
    if (!defined("GTM4WP_PATH")) {return;}
    ?>
<script data-cfasync="false" data-pagespeed-no-defer>//<![CDATA[
    function gtag(){dataLayer.push(arguments);}
    gtag("consent", "default", {
        functionality_storage: "denied",
        analytics_storage: "denied",
        ad_storage: "denied",
        performance_storage: "denied",
        others_storage: "denied",
        wait_for_update: 2000
    });
    dataLayer.push({"event": "default_consent"});
//]]</script>
    <?php
}, 1);