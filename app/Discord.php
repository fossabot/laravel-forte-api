<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Discord extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'discord_id',
    ];

    /**
     * @brief 1:1 relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return mixed
     */
    public static function scopeAllDiscordAccounts()
    {
        return self::with('user')->get();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function scopeSelfDiscordAccount(int $id)
    {
        return self::where('discord_id', $id)->first();
    }

    public static function scopeSelfDiscordSelectFieldAccount($field, $id)
    {
        return self::where($field, $id)->first();
    }

    /**
     * @param int $id
     * @param array $datas
     * @return array
     * @throws \Exception
     */
    public static function scopeUpdateDiscordAccount(int $id, array $datas = [])
    {
        $discord = self::where('user_id', $id)->first();
        try {
            DB::beginTransaction();

            foreach ($datas as $key => $data) {
                $discord->$key = $data;
            }
            $discord->save();

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();

            return ['error' => $exception->getMessage()];
        }

        return $discord;
    }

    /**
     * @param int $id
     * @return array
     */
    public static function scopeDestoryDiscordAccount(int $id)
    {
        self::where('user_id', $id)->delete();

        return ['message' => 'success'];
    }
}
