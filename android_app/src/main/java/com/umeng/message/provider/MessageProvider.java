package com.umeng.message.provider;

import android.content.ContentProvider;
import android.content.ContentUris;
import android.content.ContentValues;
import android.content.Context;
import android.content.UriMatcher;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.net.Uri;
import android.text.TextUtils;
import com.umeng.message.common.UmLog;
import com.umeng.message.proguard.k;
import com.umeng.message.proguard.l;

public class MessageProvider extends ContentProvider {
    private static final String a = MessageProvider.class.getSimpleName();
    private static final UriMatcher b = new UriMatcher(-1);
    private static final int g = 1;
    private static final int h = 2;
    private static final int i = 3;
    private static final int j = 4;
    private static final int k = 5;
    private static final int l = 6;
    private static final int m = 7;
    private static final int n = 8;
    private static final int o = 9;
    private static final int p = 10;
    private static Context q;
    private a c;
    private SQLiteDatabase d;
    private b e;
    private SQLiteDatabase f;

    private class a extends SQLiteOpenHelper {
        final /* synthetic */ MessageProvider a;

        public a(MessageProvider messageProvider, Context context) {
            this.a = messageProvider;
            super(context, k.b, null, 3);
        }

        public void onCreate(SQLiteDatabase sQLiteDatabase) {
            UmLog.d(MessageProvider.a, "MessageStoreHelper-->onCreate-->start");
            sQLiteDatabase.execSQL("CREATE TABLE IF NOT EXISTS MessageStore(_id Integer  PRIMARY KEY  AUTOINCREMENT  , MsdId Varchar  , Json Varchar  , SdkVersion Varchar  , ArrivalTime Long  , ActionType Integer )");
            sQLiteDatabase.execSQL("create table if not exists MsgTemp(id INTEGER AUTO_INCREMENT,tempkey varchar default NULL, tempvalue varchar default NULL,PRIMARY KEY(id))");
            sQLiteDatabase.execSQL("create table if not exists MsgAlias(time long,type varchar default NULL,alias varchar default NULL,exclusive int,error int,message varchar,PRIMARY KEY(time))");
            UmLog.d(MessageProvider.a, "MessageStoreHelper-->onCreate-->end");
        }

        public void onUpgrade(SQLiteDatabase sQLiteDatabase, int i, int i2) {
            if (i <= 2) {
                sQLiteDatabase.execSQL("drop table MsgTemp");
            }
            onCreate(sQLiteDatabase);
            UmLog.d(MessageProvider.a, "MessageStoreHelper-->onUpgrade");
        }
    }

    private class b extends SQLiteOpenHelper {
        final /* synthetic */ MessageProvider a;

        public b(MessageProvider messageProvider, Context context) {
            this.a = messageProvider;
            super(context, l.a, null, 5);
        }

        public void onCreate(SQLiteDatabase sQLiteDatabase) {
            sQLiteDatabase.execSQL("create table if not exists MsgLogStore (MsgId varchar, ActionType Integer, Time long, PRIMARY KEY(MsgId, ActionType))");
            sQLiteDatabase.execSQL("create table if not exists MsgLogIdTypeStore (MsgId varchar, MsgType varchar, PRIMARY KEY(MsgId))");
            sQLiteDatabase.execSQL("create table if not exists MsgLogStoreForAgoo (MsgId varchar, TaskId varchar, MsgStatus varchar, Time long, PRIMARY KEY(MsgId, MsgStatus))");
            sQLiteDatabase.execSQL("create table if not exists MsgLogIdTypeStoreForAgoo (MsgId varchar, TaskId varchar, MsgStatus varchar, PRIMARY KEY(MsgId))");
            sQLiteDatabase.execSQL("create table if not exists MsgConfigInfo (SerialNo integer default 1, AppLaunchAt long default 0, UpdateResponse varchar default NULL)");
            sQLiteDatabase.execSQL("create table if not exists InAppLogStore (Time long, MsgId varchar, MsgType Integer, NumDisplay Integer, NumOpenFull Integer, NumOpenTop Integer, NumOpenBottom Integer, NumClose Integer, NumDuration Integer, PRIMARY KEY(Time))");
            UmLog.d(MessageProvider.a, "MsgLogStoreHelper-->onCreate");
        }

        public void onUpgrade(SQLiteDatabase sQLiteDatabase, int i, int i2) {
            UmLog.d(MessageProvider.a, "oldVersion:" + i + ",newVersion:" + i2);
            if (i <= 4) {
                UmLog.d(MessageProvider.a, "MsgLogStoreHelper-->drop delete");
                sQLiteDatabase.execSQL("drop table MsgConfigInfo");
            }
            onCreate(sQLiteDatabase);
            UmLog.d(MessageProvider.a, "MsgLogStoreHelper-->onUpgrade");
        }

        private boolean a(SQLiteDatabase sQLiteDatabase, String str) {
            boolean z = false;
            if (!TextUtils.isEmpty(str)) {
                try {
                    Cursor rawQuery = sQLiteDatabase.rawQuery("select count(*) as c from sqlite_master where type = 'table' and name = '" + str.trim() + "'", null);
                    if (rawQuery.moveToNext() && rawQuery.getInt(0) > 0) {
                        z = true;
                    }
                    if (rawQuery != null) {
                        rawQuery.close();
                    }
                } catch (Exception e) {
                }
            }
            return z;
        }
    }

    public boolean onCreate() {
        q = getContext();
        b();
        UriMatcher uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MessageStores", 1);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MsgTemps", 2);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, k.e, 3);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MsgAliasDeleteAll", 4);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MsgLogStores", 5);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MsgLogIdTypeStores", 6);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MsgLogStoreForAgoos", 7);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MsgLogIdTypeStoreForAgoos", 8);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "MsgConfigInfos", 9);
        uriMatcher = b;
        a.a(q);
        uriMatcher.addURI(a.a, "InAppLogStores", 10);
        return true;
    }

    private void b() {
        try {
            synchronized (this) {
                this.c = new a(this, getContext());
                this.e = new b(this, getContext());
                if (this.d == null) {
                    this.d = this.c.getWritableDatabase();
                }
                if (this.f == null) {
                    this.f = this.e.getWritableDatabase();
                }
            }
        } catch (Exception e) {
            if (e != null) {
                e.printStackTrace();
            }
        }
    }

    public Cursor query(Uri uri, String[] strArr, String str, String[] strArr2, String str2) {
        Cursor cursor = null;
        switch (b.match(uri)) {
            case 2:
                cursor = this.d.query(k.d, strArr, str, strArr2, null, null, str2);
                break;
            case 3:
                cursor = this.d.query(k.e, strArr, str, strArr2, null, null, str2);
                break;
            case 5:
                cursor = this.f.query(l.c, strArr, str, strArr2, null, null, str2);
                break;
            case 7:
                cursor = this.f.query(l.e, strArr, str, strArr2, null, null, str2);
                break;
            case 8:
                cursor = this.f.query(l.f, strArr, str, strArr2, null, null, str2);
                break;
            case 9:
                cursor = this.f.query(l.g, strArr, str, strArr2, null, null, str2);
                break;
            case 10:
                cursor = this.f.query(l.h, strArr, str, strArr2, null, null, str2);
                break;
        }
        if (cursor != null) {
            cursor.setNotificationUri(getContext().getContentResolver(), uri);
        }
        return cursor;
    }

    public String getType(Uri uri) {
        switch (b.match(uri)) {
            case 1:
            case 2:
            case 3:
            case 5:
            case 7:
            case 8:
            case 9:
                return com.umeng.message.provider.a.a.k;
            default:
                throw new IllegalArgumentException("Unknown URI " + uri);
        }
    }

    public Uri insert(Uri uri, ContentValues contentValues) {
        long insertWithOnConflict;
        Uri withAppendedId;
        switch (b.match(uri)) {
            case 1:
                insertWithOnConflict = this.d.insertWithOnConflict(k.c, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    withAppendedId = ContentUris.withAppendedId(a.b, insertWithOnConflict);
                    getContext().getContentResolver().notifyChange(withAppendedId, null);
                    return withAppendedId;
                }
                break;
            case 2:
                insertWithOnConflict = this.d.insertWithOnConflict(k.d, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    withAppendedId = ContentUris.withAppendedId(a.b, insertWithOnConflict);
                    getContext().getContentResolver().notifyChange(withAppendedId, null);
                    return withAppendedId;
                }
                break;
            case 3:
                insertWithOnConflict = this.d.insertWithOnConflict(k.e, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    withAppendedId = ContentUris.withAppendedId(a.d, insertWithOnConflict);
                    getContext().getContentResolver().notifyChange(withAppendedId, null);
                    return withAppendedId;
                }
                break;
            case 5:
                insertWithOnConflict = this.f.insertWithOnConflict(l.c, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    withAppendedId = ContentUris.withAppendedId(a.f, insertWithOnConflict);
                    getContext().getContentResolver().notifyChange(withAppendedId, null);
                    return withAppendedId;
                }
                break;
            case 6:
                insertWithOnConflict = this.f.insertWithOnConflict(l.d, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    return ContentUris.withAppendedId(a.g, insertWithOnConflict);
                }
                break;
            case 7:
                insertWithOnConflict = this.f.insertWithOnConflict(l.e, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    return ContentUris.withAppendedId(a.h, insertWithOnConflict);
                }
                break;
            case 8:
                insertWithOnConflict = this.f.insertWithOnConflict(l.f, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    return ContentUris.withAppendedId(a.i, insertWithOnConflict);
                }
                break;
            case 9:
                insertWithOnConflict = this.f.insertWithOnConflict(l.g, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    return ContentUris.withAppendedId(a.j, insertWithOnConflict);
                }
                break;
            case 10:
                insertWithOnConflict = this.f.insertWithOnConflict(l.h, null, contentValues, 5);
                if (insertWithOnConflict > 0) {
                    a.a(q);
                    return ContentUris.withAppendedId(a.k, insertWithOnConflict);
                }
                break;
        }
        return null;
    }

    public int delete(Uri uri, String str, String[] strArr) {
        int i = 0;
        switch (b.match(uri)) {
            case 2:
                i = this.d.delete(k.d, str, strArr);
                break;
            case 3:
                i = this.d.delete(k.e, str, strArr);
                break;
            case 4:
                i = this.d.delete(k.e, null, null);
                break;
            case 5:
                i = this.f.delete(l.c, str, strArr);
                break;
            case 6:
                i = this.f.delete(l.d, str, strArr);
                break;
            case 7:
                i = this.f.delete(l.e, str, strArr);
                break;
            case 8:
                i = this.f.delete(l.f, str, strArr);
                break;
            case 10:
                i = this.f.delete(l.h, str, strArr);
                break;
        }
        getContext().getContentResolver().notifyChange(uri, null);
        return i;
    }

    public int update(Uri uri, ContentValues contentValues, String str, String[] strArr) {
        int updateWithOnConflict;
        switch (b.match(uri)) {
            case 1:
                updateWithOnConflict = this.d.updateWithOnConflict(k.c, contentValues, str, strArr, 5);
                break;
            case 2:
                updateWithOnConflict = this.d.updateWithOnConflict(k.d, contentValues, str, strArr, 5);
                break;
            case 3:
                this.d.updateWithOnConflict(k.e, contentValues, str, strArr, 5);
                updateWithOnConflict = 0;
                break;
            case 9:
                updateWithOnConflict = this.f.updateWithOnConflict(l.g, contentValues, str, strArr, 5);
                break;
            case 10:
                updateWithOnConflict = this.f.updateWithOnConflict(l.h, contentValues, str, strArr, 5);
                break;
            default:
                updateWithOnConflict = 0;
                break;
        }
        getContext().getContentResolver().notifyChange(uri, null);
        return updateWithOnConflict;
    }
}