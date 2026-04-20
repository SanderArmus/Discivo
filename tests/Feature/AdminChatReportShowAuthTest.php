<?php

use App\Models\ChatReport;
use App\Models\User;

test('only admin can view report details page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $reporter = User::factory()->create();
    $reported = User::factory()->create();

    $report = ChatReport::create([
        'reporter_id' => $reporter->id,
        'reported_id' => $reported->id,
        'match_id' => null,
        'reason' => 'spam',
        'details' => 'test',
    ]);

    $this->actingAs($reporter)
        ->get("/admin/chat-reports/{$report->id}")
        ->assertForbidden();

    $this->actingAs($admin)
        ->get("/admin/chat-reports/{$report->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/ChatReportShow'));
});
