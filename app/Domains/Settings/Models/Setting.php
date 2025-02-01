<?php

namespace App\Domains\Settings\Models;

use App\Domains\Auth\Models\User;
use App\Facades\SystemSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $table = 'settings';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function save_settings(array $arras)
    {
        SystemSetting::updateOrCreate($arras);
    }
}
