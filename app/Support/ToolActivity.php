<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ToolActivity
{
    /**
     * Record/update the last activity time for a tool and user.
     * Uses INSERT ... ON DUPLICATE KEY UPDATE against user_tool_activities.
     */
    public static function bump(string $tool, ?int $userId = null): void
    {
        $userId = $userId ?: Auth::id();
        if (!$userId) return;

        DB::statement(
            'INSERT INTO user_tool_activities (user_id, tool, last_at, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW(), NOW())
             ON DUPLICATE KEY UPDATE last_at = NOW(), updated_at = NOW()',
            [$userId, $tool]
        );
    }
}
