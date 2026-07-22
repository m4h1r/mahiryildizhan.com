<?php

it('requires authentication to view reports', function (): void {
    $this->get(route('admin.reports'))->assertRedirect(route('login'));
});

it('denies non-admin users from reports', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.reports'))->assertForbidden();
});

// NOTE: ReportController::index() uses selectRaw('MONTH(date) ...'), a MySQL-only
// function that fails on SQLite (the test driver). A full "renders the report page"
// test is blocked by this pre-existing portability bug — same root cause as the
// already-failing Tests\Feature\Phase12FlowTest::reports_support_multiple_year_filters.
// Fixing ReportController to use a portable date-grouping approach is a separate task.
