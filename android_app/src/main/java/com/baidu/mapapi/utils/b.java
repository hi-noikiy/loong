package com.baidu.mapapi.utils;

import android.os.IBinder;
import android.os.RemoteException;
import android.util.Log;
import com.baidu.a.a.a.c.a;

final class b extends a {
    final /* synthetic */ int a;

    b(int i) {
        this.a = i;
    }

    public void a(IBinder iBinder) throws RemoteException {
        Log.d(a.c, "onClientReady");
        if (a.e != null) {
            a.e = null;
        }
        a.e = com.baidu.a.a.a.a.a.a(iBinder);
        a.a(this.a);
        a.t = true;
    }
}