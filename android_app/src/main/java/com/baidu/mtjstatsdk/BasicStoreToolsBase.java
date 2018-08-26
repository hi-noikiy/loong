package com.baidu.mtjstatsdk;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;
import android.text.TextUtils;

public class BasicStoreToolsBase {
    private final String a = "__Baidu_Stat_SDK_SendRem";
    private SharedPreferences b;
    private SharedPreferences c;

    private SharedPreferences a(Context context) {
        if (this.b == null) {
            this.b = context.getSharedPreferences("__Baidu_Stat_SDK_SendRem", 0);
        }
        return this.b;
    }

    private SharedPreferences b(Context context) {
        if (this.c == null) {
            this.c = PreferenceManager.getDefaultSharedPreferences(context);
        }
        return this.c;
    }

    public boolean getBoolean(Context context, String str, boolean z) {
        return a(context).getBoolean(str, z);
    }

    public int getInt(Context context, String str, int i) {
        return a(context).getInt(str, i);
    }

    public long getLong(Context context, String str, long j) {
        return a(context).getLong(str, j);
    }

    public boolean getSharedBoolean(Context context, String str, boolean z) {
        return b(context).getBoolean(str, z);
    }

    public int getSharedInt(Context context, String str, int i) {
        return b(context).getInt(str, i);
    }

    public long getSharedLong(Context context, String str, long j) {
        return b(context).getLong(str, j);
    }

    public String getSharedString(Context context, String str, String str2) {
        return b(context).getString(str, str2);
    }

    public String getString(Context context, String str, String str2) {
        return a(context).getString(str, str2);
    }

    public void putBoolean(Context context, String str, boolean z) {
        a(context).edit().putBoolean(str, z).commit();
    }

    public void putInt(Context context, String str, int i) {
        a(context).edit().putInt(str, i).commit();
    }

    public void putLong(Context context, String str, long j) {
        a(context).edit().putLong(str, j).commit();
    }

    public void putSharedBoolean(Context context, String str, boolean z) {
        b(context).edit().putBoolean(str, z).commit();
    }

    public void putSharedInt(Context context, String str, int i) {
        b(context).edit().putInt(str, i).commit();
    }

    public void putSharedLong(Context context, String str, long j) {
        b(context).edit().putLong(str, j).commit();
    }

    public void putSharedString(Context context, String str, String str2) {
        b(context).edit().putString(str, str2).commit();
    }

    public void putString(Context context, String str, String str2) {
        a(context).edit().putString(str, str2).commit();
    }

    public void removeShare(Context context, String str) {
        b(context).edit().remove(str).commit();
    }

    public boolean updateShareBoolean(Intent intent, Activity activity, String str) {
        return updateShareBoolean(intent, activity, str, true);
    }

    public boolean updateShareBoolean(Intent intent, Activity activity, String str, boolean z) {
        if (intent != null) {
            boolean booleanExtra = intent.getBooleanExtra(str, z);
            if (booleanExtra != getSharedBoolean(activity, str, z)) {
                putSharedBoolean(activity, str, booleanExtra);
                return true;
            }
        }
        return false;
    }

    public boolean updateShareInt(Intent intent, Activity activity, String str, int i) {
        if (intent != null) {
            int intExtra = intent.getIntExtra(str, i);
            if (intExtra != getSharedInt(activity, str, i)) {
                putSharedInt(activity, str, intExtra);
                return true;
            }
        }
        return false;
    }

    public boolean updateShareString(Intent intent, Activity activity, String str) {
        if (intent != null) {
            Object stringExtra = intent.getStringExtra(str);
            if (stringExtra != null) {
                stringExtra = stringExtra.trim();
                if (stringExtra.length() == 0) {
                    stringExtra = null;
                }
            }
            if (!TextUtils.equals(stringExtra, getSharedString(activity, str, null))) {
                putSharedString(activity, str, stringExtra);
                return true;
            }
        }
        return false;
    }
}