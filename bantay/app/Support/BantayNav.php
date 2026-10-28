<?php

namespace App\Support;

class BantayNav
{
    /**
     * Sidebar nav items per role, matching the BANTAY wireframes.
     * Phase 6: every Administrator item now has a real route. Other roles'
     * Notifications/Reports items are updated where applicable.
     */
    public static function forRole(string $role): array
    {
        return match ($role) {
            'Administrator' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard'],
                ['label' => 'Data Management', 'icon' => 'database', 'route' => 'admin.uploads.index'],
                ['label' => 'Surveillance', 'icon' => 'map', 'route' => 'admin.dashboard.map'],
                ['label' => 'Rules Engine', 'icon' => 'flow', 'route' => 'admin.rules.index'],
                ['label' => 'Alerts', 'icon' => 'alert', 'route' => 'admin.alerts.index'],
                ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'admin.notifications.index'],
                ['label' => 'Reports', 'icon' => 'doc', 'route' => 'admin.reports.index'],
                ['label' => 'Reference Data', 'icon' => 'list', 'route' => 'admin.barangays.index'],
                ['label' => 'Users & Roles', 'icon' => 'users', 'route' => 'admin.users.index'],
                ['label' => 'System Status', 'icon' => 'pulse', 'route' => 'admin.system-status.index'],
                ['label' => 'Audit Trail', 'icon' => 'doc', 'route' => 'admin.audit-trail.index'],
                ['label' => 'System Settings', 'icon' => 'cog', 'route' => 'admin.settings.index'],
                ['label' => 'Backup & Recovery', 'icon' => 'archive', 'route' => 'admin.backup.index'],
            ],
            'Health Officer' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard'],
                ['label' => 'Validation Summary', 'icon' => 'check', 'route' => null],
                ['label' => 'Processing Review', 'icon' => 'flow', 'route' => null],
                ['label' => 'Surveillance', 'icon' => 'map', 'route' => 'admin.dashboard.map'],
                ['label' => 'Rule Review', 'icon' => 'shield', 'route' => 'admin.rules.index'],
                ['label' => 'Alerts', 'icon' => 'alert', 'route' => 'admin.alerts.index'],
                ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'admin.notifications.index'],
                ['label' => 'Reports', 'icon' => 'doc', 'route' => 'admin.reports.index'],
            ],
            'Surveillance Officer' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard'],
                ['label' => 'Dataset Upload', 'icon' => 'upload', 'route' => 'admin.uploads.create'],
                ['label' => 'Validation Results', 'icon' => 'check', 'route' => 'admin.uploads.index'],
                ['label' => 'Quarantined Records', 'icon' => 'flag', 'route' => 'admin.uploads.index'],
                ['label' => 'Processing Status', 'icon' => 'pulse', 'route' => 'admin.uploads.index'],
                ['label' => 'Surveillance', 'icon' => 'map', 'route' => 'admin.dashboard.map'],
                ['label' => 'Alerts', 'icon' => 'alert', 'route' => 'admin.alerts.index'],
                ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'admin.notifications.index'],
                ['label' => 'Reports', 'icon' => 'doc', 'route' => 'admin.reports.index'],
            ],
            'Viewer' => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard'],
                ['label' => 'Trends', 'icon' => 'chart', 'route' => 'admin.dashboard'],
                ['label' => 'Geographic Monitoring', 'icon' => 'map', 'route' => 'admin.dashboard.map'],
                ['label' => 'Alert Summaries', 'icon' => 'alert', 'route' => 'admin.alerts.index'],
                ['label' => 'Reports', 'icon' => 'doc', 'route' => null],
            ],
            default => [
                ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard'],
            ],
        };
    }

    public static function alertBadgeCount(): int
    {
        return \App\Models\Alert::openStatus()->count();
    }

    public static function unreadNotificationCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public static function blurbForRole(string $role): array
    {
        return match ($role) {
            'Administrator' => [
                'title' => 'Full administrative access',
                'body' => 'Manage users, reference data, rules, and system configuration. All actions are recorded in the Audit Trail.',
            ],
            'Health Officer' => [
                'title' => 'Epidemiological review access',
                'body' => 'Review accepted surveillance information, manage authorized alert decisions, and access approved reports. No direct record modification.',
            ],
            'Surveillance Officer' => [
                'title' => 'Operational access',
                'body' => 'Upload, validate, correct, monitor alerts, and generate reports.',
            ],
            'Viewer' => [
                'title' => 'Authorized read-only access',
                'body' => 'View only the approved dashboards, maps, alert summaries, and reports assigned to the Viewer role.',
            ],
            default => ['title' => '', 'body' => ''],
        };
    }
}
