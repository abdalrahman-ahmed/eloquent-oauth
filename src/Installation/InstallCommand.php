<?php namespace AdamWathan\EloquentOAuth\Installation;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Composer;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    protected $filesystem;
    protected $composer;
    protected $name = 'eloquent-oauth:install';
    protected $description = 'Install package config and migrations';

    public function __construct(Filesystem $filesystem, Composer $composer)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->composer = $composer;
    }

    public function handle()
    {
        try {
            $this->publishConfig();
            $this->publishMigrations();
        } catch (FileExistsException $e) {
            $this->error('It looks like this package has already been installed. Use --force to override.');
        }

        $this->composer->dumpAutoloads();
        $this->comment('Package configuration and migrations installed!');
    }

    public function publishConfig()
    {
        $this->publishFile(__DIR__ . '/../../config/eloquent-oauth.php', config_path() . '/eloquent-oauth.php');
        $this->info('Configuration published.');
    }

    public function publishMigrations()
    {
        $name = 'create_oauth_identities_table';
        $path = $this->laravel['path.database'] . '/migrations';
        $fullPath = $this->laravel['migration.creator']->create($name, $path);
        $this->filesystem->put($fullPath, $this->filesystem->get(__DIR__ . '/../../migrations/create_oauth_identities_table.stub'));
    }

    public function publishFile($from, $to)
    {
        if ($this->filesystem->exists($to) && !$this->option('force')) {
            throw new FileExistsException;
        }

        $this->filesystem->copy($from, $to);
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Overwrite any existing files.'],
        ];
    }

}
