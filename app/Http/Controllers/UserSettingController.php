<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting;

class UserSettingController extends Controller
{
    public function show(Request $request)
    {
        return $request->user()->setting;
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_registration' => 'boolean',
            'email_password_reset' => 'boolean',
            'email_withdrawal' => 'boolean',
            'inapp_like' => 'boolean',
            'inapp_comment' => 'boolean',
            'inapp_follow' => 'boolean',
        ]);

        $setting = $request->user()->setting;
        $setting->update($validated);

        return response()->json(['message' => '設定を更新しました。']);
    }
}
