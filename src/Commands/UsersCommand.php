<?php


namespace App\Console\Commands\Import;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UsersCommand extends ImportCommand
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will import cleaned users from the ############ database, to a mew laravel system';
    
    /**
     * Something needed for this implementation, an enum that I'm moving into the codebase.
     * @var array|int[]
     */
    protected array $viewables = [
        'everyone' => 1,
        'members'  => 2,
        'followed' => 3,
        'none'     => 4,
    ];
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $this->processUsers();
        
        $this->nextCommand();
    }
    
    
    public function cleanLinks(string $var): string
    {
        return str($var)
            ->replace("[URL='http", '[URL="http')
            ->replace("']", '"]');
    }
    
    
    
    public function processJsonField($field, $model, $extra): \Illuminate\Support\Collection
    {
        $jsonObject = object_get($model, $field);
        
        $array = $extra->jsonserialize();
        
        $list = json_decode($jsonObject, true) ?? [];
        
        foreach ( $list as $key => $value ) {
            $compValue = strtolower(trim($value));
            if ( strlen($compValue) == 0 ) {
                continue;
            }
            
            
            if ( in_array($compValue, [ 'null', 'invalid' ]) ) {
                continue;
            }
            
            
            $array[ $field ][ $key ] = $value;
        }
        
        return collect($array);
    }
    
    public function pushFieldToExtra($field, $model, $extra, $value = null): \Illuminate\Support\Collection
    {
        if ( is_null($value) ) {
            $value = data_get($model, $field, "");
        }
        
        $array = $extra->jsonserialize();
        
        if ( in_array(strtolower(trim($value)), [ 'null', 'invalid' ]) ) {
            $value = "";
        }
        
        $array[ $field ] = $value;
        
        return collect($array);
    }
    
    public function processUsers()
    {
        $this->updateStepWithMessage('Importing Users');
        
        $builder = DB::connection($this->connection)
            ->table('sys_user')
            ->select(DB::raw('sys_user.user_id as id, email, username, register_date, sys_user_authenticate.data, last_activity,
            timezone,is_moderator, is_admin, is_banned, visible, activity_visible, privacy_policy_accepted,terms_accepted,
            dob_day,dob_month,dob_year,signature,website,location,about,custom_fields,connected_accounts,following,ignored,
            allow_view_profile,allow_send_personal_conversation,
            show_dob_year, show_dob_date,content_show_signature,creation_watch_state,interaction_watch_state,email_on_conversation'))
            ->leftJoin('sys_user_authenticate', 'sys_user.user_id', '=', 'sys_user_authenticate.user_id')
            ->leftJoin('sys_user_profile', 'sys_user.user_id', '=', 'sys_user_profile.user_id')
            ->leftJoin('sys_user_privacy', 'sys_user.user_id', '=', 'sys_user_privacy.user_id')
            ->leftJoin('sys_user_option', 'sys_user.user_id', '=', 'sys_user_option.user_id')
            ->orderBy('sys_user.user_id');
        
        
        // Create a progress bar
        $bar = $this->createProgressBar($builder, 'Users');
        
        $out  = [];
        $loop = 0;
     
        foreach ( $builder->cursor() as $user ) {
            $bar->advance();
            
            $extra = collect();
            
            $extra = $this->pushFieldToExtra('location', $user, $extra);
            $extra = $this->processJsonField('custom_fields', $user, $extra);
            $extra = $this->processJsonField('connected_accounts', $user, $extra);
            
            
            $password = 'some hash';
            if ( $user->data ) {
                $data = unserialize($user->data);
                if ( isset($data['hash']) ) {
                    $password = $data['hash'];
                }
            }
            unset($data);
            $date = Carbon::createFromTimestampUTC($user->register_date);
            
            if ( empty($user->email) ) {
                continue;
            }
            
            $out[] = [
                'id'                => $user->id,
                'name'              => $user->username,
                'email'             => $user->email,
                'email_verified_at' => $date,
                'password'          => $password,
                'created_at'        => $date,
                
                //Additional Fields
                'updated_at'        =>
                    ( $user->last_activity ?
                        Carbon::createFromTimestamp($user->last_activity) : null ),
                
                'is_moderator'        => $user->is_moderator,
                'is_admin'            => $user->is_admin,
                'is_banned'           => $user->is_banned,
                'is_visible'          => $user->visible,
                'activity_is_visible' => $user->activity_visible,
                'avatar'              => null,
                
                'email_on_dms'             => $user->email_on_conversation,
                'allow_view_profile'    => $this->viewables[ $user->allow_view_profile ],
                'allow_direct_messages' => $this->viewables[ $user->allow_send_personal_conversation ],
                
                'privacy_policy_accepted' =>
                    ( $user->privacy_policy_accepted ?
                        Carbon::createFromTimestamp($user->privacy_policy_accepted) : null ),
                'terms_accepted'          =>
                    ( $user->terms_accepted ?
                        Carbon::createFromTimestamp($user->terms_accepted) : null ),
                'extra'                   => $extra->toJson(),
                
                // look at warning points and reaction score once this has been implemented.
            ];
            
            unset($extra);
            
            //finish processing the output
            $loop++;
            
            //if we've hit the buffer number of users to insert batch insert them, otherwise keep building the loop!
            if ( $loop >= $this->saveEvery ) {
                User::insert($out);
                
                $out  = [];
                $loop = 0;
            }
        }
        
        // when you are done, if there are any left over unprocessed users make sure to insert them now.
        if ( $loop < $this->saveEvery ) {
            User::insert($out);
        }
        
        $this->closeProgressBar($bar);
    }
}
