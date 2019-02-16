<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

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
     * @return mixed
     */
    static public function scopeAllDiscordAccounts() {
        return self::whereNull('withdraw_at')->get();
    }

    /**
     * @param int $id
     * @return mixed
     */
    static public function scopeSelfDiscordAccount(int $id) {
        return self::where('discord_id', $id)->first();
    }

    /**
     * @param int $id
     * @param array $datas
     * @return array
     */
    static public function scopeUpdateDiscordAccount(int $id, array $datas = []) {
        $discord = self::where('user_id', $id);
        try {
            DB::beginTransaction();

            foreach ($datas as $key => $data) {
                $discord->$key = $data;
            }
            $discord->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => $e->getMessage()];
        }

        return $discord;
    }

    /**
     * @param int $id
     * @return array
     */
    static public function scopeDestoryDiscordAccount(int $id) {
        self::where('user_id', $id)->update([
            'withdraw_at' => date('Y-m-d')
        ]);

        return ['message' => 'success'];
    }

}
