<?php

declare(strict_types=1);

namespace Rinvex\Tenantable\Console\Commands;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rinvex:migrate:tenantable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Rinvex Tenantable Tables.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->warn('Migrate rinvex/tenantable:');
        $this->call('migrate', ['--step' => true, '--path' => 'vendor/rinvex/tenantable/database/migrations']);
    }
}
