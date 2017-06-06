<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;

/* From 5.2 default, FYI
/*
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
*/

class User extends Model implements UserInterface
{
    use Notifiable;

    use UserTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password_hash', 'remember_token'];

    protected $fillable = ['name', 'email', 'password'];

    public function series()
    {
        return $this->belongsToMany(\App\Series::class, 'user_series');
    }

    public function notifications()
    {
        return $this->hasMany(\App\Notification::class);
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function hasSuper()
    {
        return !!$this->is_super;
    }

    public static function usernameIsUnique($username)
    {
        $count = self::whereUsername($username)->count();
        return ($count === 0);
    }

    public static function register($username, $password)
    {
        $user = new User();
        $user->username = $username;
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    public static function getByUsernamePassword($username, $password)
    {
        $user = self::whereUsername($username)->first();
        if ($user) {
            if (password_verify($password, $user->getAuthPassword())) {
                return $user;
            }
        }

        return null;
    }

    public function setPassword($password)
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function watchSeries(Series $series)
    {
        $watching = $this->isWatchingSeries($series);

        if ($watching) {
            $this->series()->detach($series->id);
        } else {
            $this->series()->attach($series->id);
        }

        return !$watching;
    }

    public function isWatchingSeries(Series $series)
    {
        $result = $this
            ->series()
            ->whereSeriesId($series->id)
            ->count();

        return ($result > 0);
    }

    public function touchLoggedInDate()
    {
        $this->logged_in_at = $this->freshTimestamp();
        $this->save();
    }
}
