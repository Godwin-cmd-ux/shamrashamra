<?php

return [
    'heading' => 'Entrance check-in',
    'subheading' => 'Scan each invitation QR code once. The server decides every result.',
    'start_camera' => 'Start camera',
    'stop_camera' => 'Stop camera',
    'camera_starting' => 'Starting camera…',
    'scanning' => 'Point the camera at the guest’s QR code…',
    'permission_denied' => 'Camera permission was denied. Allow camera access in your browser, or use manual entry below.',
    'camera_unavailable' => 'No camera is available on this device. Use manual entry below.',
    'unsupported_browser' => 'This browser does not support camera scanning (HTTPS required). Use manual entry below.',
    'manual_title' => 'Manual verification',
    'manual_placeholder' => 'Paste the invitation link or token',
    'manual_hint' => 'Ask the guest to open their invitation link and copy the URL.',
    'manual_verify' => 'Verify',
    'recent' => 'Recent check-ins',
    'no_recent' => 'No check-ins recorded yet.',
    'stats' => [
        'checked_in' => 'Invitations checked in',
        'guests' => 'People admitted',
        'issued' => 'Invitations issued',
    ],
    'result_labels' => [
        'person' => 'Covered people',
        'checked_at' => 'Checked in at',
        'checked_by' => 'By',
        'method' => 'Method',
    ],

    'outcome' => [
        'valid' => 'Valid',
        'invalid' => 'Invalid',
        'revoked' => 'Revoked',
        'wrong_event' => 'Wrong event',
        'duplicate' => 'Already checked in',
    ],

    'messages' => [
        'valid' => 'Invitation accepted — party checked in.',
        'invalid' => 'Not a valid invitation for this event.',
        'revoked' => 'This invitation was revoked or replaced and does not grant entry.',
        'wrong_event' => 'This invitation belongs to a different event.',
        'duplicate' => 'Already checked in — no duplicate record was created.',
    ],
];
