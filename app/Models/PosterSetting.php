<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosterSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
