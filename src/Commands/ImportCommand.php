<?php


namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class ImportCommand extends Command
{
    /**
     * The maximum amount of memory you are willing to let this script take, in some cases depending on
     * speed and cpu's in the sever or dev machine you may want to increase this to 8G or 16G etc...
     *
     * @var string $memoryLimit
     */
    protected string $memoryLimit = '1G';
    
    /**
     * When you are pulling items from one database into another, I've left the destination as the default access
     * and the explicit connection as the original database. It's also not a bad idea to make sure you are working
     * with a copy of your original database, because sometimes its quicker to do repair transforms on it than
     * your target. And you shouldn't do that without first making a copy and calling it something like export :)
     *
     * @var string
     */
    protected string $originalDatabase = 'export';
    
    /**
     * User for Notes with helper commands
     * @var string
     */
    protected string $model = '';
    
    /**
     * This is for speed tuning, you want to build up piles of data and batch it.You really don't need to worry about
     * this until 100,000 records+. It's once your migrations take minutes and hours. I'm sure there are people who
     * take days, but I've never worked anywhere that big.
     *
     * @var int $saveEvery
     */
    protected int $saveEvery = 1000;
    
    /**
     * I just use this often to leave a visual log on the screen, as data is moving all over the place, especially if
     * if you run into an issue you can have a visual cue on screen of how far along you are.
     *
     * @var int $step
     */
    protected int $step = 1;
    
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setup();
        
        
    }
    
    /**
     * This is mysql specific, you'd need some pgsql or other guru to help with another way but there are a huge number
     * of people running mysql so you might find this helpful as a starting point. The key to all this is like a lazy
     * collection. You need to force the data to get streamed over a connection from and to the database and clear out
     * memory as you use it.
     */
    public function setup()
    {
        // Prepare the database to run in non buffered mode.
        DB::connection($this->originalDatabase)
            ->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        
        // Vastly increase memory for the following operation
        ini_set('memory_limit', $this->memoryLimit);
    }
    
    /**
     * Make a progress bar, Some of the transforms can take 1 - 30 minutes, staring at a blank screen is not a great
     * idea in those cases.
     *
     * @param $builder
     * @param $model
     * @param string $time
     *
     * @return ProgressBar
     */
    public function createProgressBar($builder, $model, $time = '10 - 20 seconds'): ProgressBar
    {
        $this->model = $model;
        // Create a progress bar
        $this->info('Collecting ' . $model . ', the initial count will take ' . $time . ' ...');
        
        $count = $builder->count();
        
        $this->info('Found ' . $count . ' ' . $model);
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        return $bar;
    }
    
    
    public function updateStepWithMessage(string $message): void
    {
        $this->info($this->step++ . '. ' . $message);
    }
    
    public function closeProgressBar($bar)
    {
        $bar->finish();
        $this->info("\n" . $this->model . " Import Completed");
    }
    
    
    /**
     * Keep track of where you are in the process if it's a long set of transforms. Break it up into multiple commands.
     * It will relinquish memory and give you better backup points in case something goes wrong you don't have to start
     * at the beginning.
     */
    public function nextCommand()
    {
        $steps = collect([
            'import:prep',
            'import:users',
            'import:categories',
            'import:stuff',
            'import:things',
            'import:more-things',
            'import:etc...',
        ]);
        
        $current = $steps->flip()->first(fn ($value, $key) => $key === $this->signature);
        if ( $current < $steps->count() - 1 ) {
            $this->info('The next step is ');
            $this->info('php artisan ' . $steps[ $current + 1 ]);
        } else {
            $this->info('All done. Please do both visual and database tests');
        }
    }
    
    /**
     * When all else fails, open up tinker, and purge some things... ImportCommand::truncateTable('users');
     * @param $table
     */
    public static function truncateTable($table)
    {
        Schema::disableForeignKeyConstraints();
        
        DB::table($table)->truncate();
        
        Schema::enableForeignKeyConstraints();
    }
    
    abstract public function handle();
}
