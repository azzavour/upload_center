<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExcelFormat;
use App\Models\Department;

/**
 * Command untuk memperbaiki format yang tidak memiliki department_id
 * 
 * Jalankan: php artisan fix:orphan-formats
 */
class FixOrphanFormats extends Command
{
    protected $signature = 'fix:orphan-formats {--auto : Automatically assign to first department}';
    protected $description = 'Fix Excel formats without department_id';

    public function handle()
    {
        $this->info('🔍 Checking for orphan formats...');
        $this->newLine();

        // Cari format tanpa department_id
        $orphanFormats = ExcelFormat::whereNull('department_id')->get();

        if ($orphanFormats->isEmpty()) {
            $this->info('✅ No orphan formats found! All formats have department assigned.');
            return 0;
        }

        $this->warn("⚠️  Found {$orphanFormats->count()} format(s) without department:");
        $this->newLine();

        foreach ($orphanFormats as $format) {
            $this->line("  • ID: {$format->id} - {$format->format_name}");
        }
        $this->newLine();

        // Dapatkan semua department
        $departments = Department::active()->orderBy('name')->get();

        if ($departments->isEmpty()) {
            $this->error('❌ No departments found! Please create departments first.');
            return 1;
        }

        // Mode auto
        if ($this->option('auto')) {
            $defaultDept = $departments->first();
            $this->info("Auto-assigning all orphan formats to: {$defaultDept->name}");
            
            foreach ($orphanFormats as $format) {
                $format->update(['department_id' => $defaultDept->id]);
                $this->info("  ✅ {$format->format_name} → {$defaultDept->name}");
            }
            
            $this->newLine();
            $this->info('✅ All orphan formats have been assigned!');
            return 0;
        }

        // Mode manual - assign satu per satu
        $this->info('📋 Available departments:');
        foreach ($departments as $index => $dept) {
            $this->line("  [{$index}] {$dept->name} ({$dept->code})");
        }
        $this->newLine();

        foreach ($orphanFormats as $format) {
            $this->info("Format: {$format->format_name}");
            
            $deptIndex = $this->ask('Select department number (or "s" to skip)');
            
            if (strtolower($deptIndex) === 's') {
                $this->line("  ⏩ Skipped");
                continue;
            }

            if (!is_numeric($deptIndex) || !isset($departments[$deptIndex])) {
                $this->error("  ❌ Invalid selection");
                continue;
            }

            $selectedDept = $departments[$deptIndex];
            $format->update(['department_id' => $selectedDept->id]);
            
            $this->info("  ✅ Assigned to: {$selectedDept->name}");
            $this->newLine();
        }

        $this->newLine();
        $this->info('✅ Format assignment completed!');
        
        return 0;
    }
}