<?php namespace NielsFilmer\MultiTokenGuard\Guards;


use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MultiTokenGuard extends TokenGuard implements Guard
{
    /**
     * @var string
     */
    protected $storageTable;

    /**
     * @var string
     */
    protected $storageUserId;

    /**
     * @var string
     */
    protected $storageValidBool;


    /**
     * MultiTokenGuard constructor.
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        parent::__construct($provider, $request);

        $this->storageTable = 'api_tokens';
        $this->storageKey = 'key';
        $this->storageUserId = 'user_id';
        $this->storageValidBool = 'is_valid';
    }


    /**
     * @return Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (!empty($token) && $tokenObj = DB::table($this->storageTable)->where($this->storageKey, $token)->first()) {
            if($tokenObj->{$this->storageValidBool}) {
                $user = $this->provider->retrieveById($tokenObj->{$this->storageUserId});
            }
        }

        return $this->user = $user;
    }


    /**
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        if(!$token = DB::table($this->storageTable)->where($this->storageKey, $credentials[$this->inputKey])->first()) {
            return false;
        }

        if(!$token->{$this->storageValidBool}) {
            return false;
        }

        return true;
    }
}