<?php

namespace Syltaen;

Data::globals([

    // ========== The current user ========== //
    "user" => (new Users)->logged()

]);

