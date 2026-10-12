<?php

return [
    // Admin is logged out after this many minutes without any activity.
    'idle_minutes' => (int) env('CMS_IDLE_MINUTES', 5),
];
