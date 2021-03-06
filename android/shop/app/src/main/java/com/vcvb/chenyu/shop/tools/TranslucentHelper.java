package com.vcvb.chenyu.shop.tools;

import android.app.Activity;
import android.app.ActivityOptions;
import android.os.Build;
import android.os.Handler;

import java.lang.reflect.InvocationHandler;
import java.lang.reflect.Method;
import java.lang.reflect.Proxy;

public class TranslucentHelper {
    public interface TranslucentListener {
        void onTranslucent();
    }

    private static class MyInvocationHandler implements InvocationHandler {
        private TranslucentListener listener;

        MyInvocationHandler(TranslucentListener listener) {
            this.listener = listener;
        }

        @Override
        public Object invoke(Object proxy, Method method, Object[] args) throws Throwable {
            try {
                boolean success = (boolean) args[0];
                if (success && listener != null) {
                    listener.onTranslucent();
                }
            } catch (Exception ignored) {
            }
            return null;
        }
    }

    public static boolean convertActivityFromTranslucent(Activity activity) {
        try {
            Method method = Activity.class.getDeclaredMethod("convertFromTranslucent");
            method.setAccessible(true);
            method.invoke(activity);
            return true;
        } catch (Throwable t) {
            return false;
        }
    }

    public static void convertActivityToTranslucent(Activity activity, final TranslucentListener
            listener) {
        try {
            Class<?>[] classes = Activity.class.getDeclaredClasses();
            Class<?> translucentConversionListenerClazz = null;
            for (Class clazz : classes) {
                if (clazz.getSimpleName().contains("TranslucentConversionListener")) {
                    translucentConversionListenerClazz = clazz;
                }
            }

            MyInvocationHandler myInvocationHandler = new MyInvocationHandler(listener);
            Object obj = Proxy.newProxyInstance(Activity.class.getClassLoader(),
                    new Class[]{translucentConversionListenerClazz}, myInvocationHandler);

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
                Method getActivityOptions = Activity.class.getDeclaredMethod("getActivityOptions");
                getActivityOptions.setAccessible(true);
                Object options = getActivityOptions.invoke(activity);

                Method method = Activity.class.getDeclaredMethod("convertToTranslucent",
                        translucentConversionListenerClazz, ActivityOptions.class);
                method.setAccessible(true);
                method.invoke(activity, obj, options);
            } else {
                Method method =
                        Activity.class.getDeclaredMethod("convertToTranslucent",
                                translucentConversionListenerClazz);
                method.setAccessible(true);
                method.invoke(activity, obj);
            }
        } catch (Throwable t) {
            new Handler().postDelayed(new Runnable() {
                @Override
                public void run() {
                    if (listener != null) {
                        listener.onTranslucent();
                    }
                }
            }, 100);
        }
    }
}
