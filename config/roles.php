<?php

return [
    
    'admin'=>env('ADMIN_ROLE', 1),
    'organizationAdmin'=>env('ORG_ADMIN_ROLE', 2),
    // 'secretary'=>3,
    'boardMembers'=>env('BOARD_MEMBERS_ROLE', 4),
];