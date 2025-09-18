<?php
return [
    // Se true, a soma tanabi_percentage + other_club_percentage deve ser exatamente 100 (com tolerância pequena)
    'percentages_enforce_100' => env('TANABI_PERCENTAGES_ENFORCE_100', false),
    // Tolerância para comparação (flutuação)
    'percentages_tolerance' => 0.0001,
];
