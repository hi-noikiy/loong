package anet.channel.appmonitor;

import android.text.TextUtils;
import anet.channel.statist.AlarmObject;
import anet.channel.statist.AmdcStatistic;
import anet.channel.statist.CountObject;
import anet.channel.statist.Dimension;
import anet.channel.statist.ExceptionStatistic;
import anet.channel.statist.Measure;
import anet.channel.statist.Monitor;
import anet.channel.statist.RequestStatistic;
import anet.channel.statist.SessionStatistic;
import anet.channel.statist.StatObject;
import anet.channel.util.ALog;
import anet.channel.util.StringUtils;
import com.alibaba.mtl.appmonitor.AppMonitor;
import com.alibaba.mtl.appmonitor.AppMonitor.Alarm;
import com.alibaba.mtl.appmonitor.AppMonitor.Counter;
import com.alibaba.mtl.appmonitor.AppMonitor.Stat;
import com.alibaba.mtl.appmonitor.model.DimensionSet;
import com.alibaba.mtl.appmonitor.model.DimensionValueSet;
import com.alibaba.mtl.appmonitor.model.MeasureSet;
import com.alibaba.mtl.appmonitor.model.MeasureValueSet;
import java.lang.reflect.Field;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.concurrent.atomic.AtomicBoolean;

/* compiled from: Taobao */
class DefaultAppMonitor implements IAppMonitor {
    private static final String TAG = "awcn.DefaultAppMonitor";
    private static Map<Class<?>, List<Field>> dimensionFieldsCache = new HashMap();
    private static AtomicBoolean isRegistered = new AtomicBoolean(false);
    private static boolean mAppMonitorValid = false;
    private static Map<Class<?>, List<Field>> measureFieldsCache = new HashMap();

    DefaultAppMonitor() {
        try {
            Class.forName("com.alibaba.mtl.appmonitor.AppMonitor");
            mAppMonitorValid = true;
        } catch (Exception e) {
            mAppMonitorValid = false;
        }
    }

    public void register() {
        if (!mAppMonitorValid) {
            ALog.w(TAG, "no appmonitor sdk", null, new Object[0]);
        } else if (isRegistered.compareAndSet(false, true)) {
            register(RequestStatistic.class);
            register(SessionStatistic.class);
            register(ExceptionStatistic.class);
            register(AmdcStatistic.class);
        }
    }

    public void commitStat(StatObject statObject) {
        if (mAppMonitorValid && statObject != null) {
            Class cls = statObject.getClass();
            Monitor monitor = (Monitor) cls.getAnnotation(Monitor.class);
            if (monitor != null && statObject.beforeCommit()) {
                try {
                    Map hashMap;
                    DimensionValueSet create = DimensionValueSet.create();
                    MeasureValueSet create2 = MeasureValueSet.create();
                    List<Field> list = (List) dimensionFieldsCache.get(cls);
                    if (ALog.isPrintLog(1)) {
                        hashMap = new HashMap();
                    } else {
                        hashMap = null;
                    }
                    Object obj;
                    String name;
                    String str;
                    if (list != null) {
                        for (Field field : list) {
                            obj = field.get(statObject);
                            name = field.getName();
                            if (obj == null) {
                                str = "";
                            } else {
                                str = obj.toString();
                            }
                            create.setValue(name, str);
                        }
                        for (Field field2 : (List) measureFieldsCache.get(cls)) {
                            Double valueOf = Double.valueOf(field2.getDouble(statObject));
                            create2.setValue(field2.getName(), valueOf.doubleValue());
                            if (hashMap != null) {
                                hashMap.put(field2.getName(), valueOf);
                            }
                        }
                    } else {
                        Field[] declaredFields = cls.getDeclaredFields();
                        for (Field field22 : declaredFields) {
                            field22.setAccessible(true);
                            if (field22.isAnnotationPresent(Dimension.class)) {
                                obj = field22.get(statObject);
                                name = field22.getName();
                                if (obj == null) {
                                    str = "";
                                } else {
                                    str = obj.toString();
                                }
                                create.setValue(name, str);
                            } else if (field22.isAnnotationPresent(Measure.class)) {
                                Double valueOf2 = Double.valueOf(field22.getDouble(statObject));
                                create2.setValue(field22.getName(), valueOf2.doubleValue());
                                if (hashMap != null) {
                                    hashMap.put(field22.getName(), valueOf2);
                                }
                            }
                        }
                    }
                    Stat.commit(monitor.module(), monitor.monitorPoint(), create, create2);
                    if (ALog.isPrintLog(1)) {
                        ALog.d(TAG, "commit monitor point: " + monitor.monitorPoint(), null, "\nDimensions", create.getMap().toString(), "\nMeasures", hashMap.toString());
                    }
                } catch (Throwable th) {
                    ALog.e(TAG, "commit monitor point failed", null, th, new Object[0]);
                }
            }
        }
    }

    public void commitAlarm(AlarmObject alarmObject) {
        if (mAppMonitorValid && alarmObject != null && !TextUtils.isEmpty(alarmObject.module) && !TextUtils.isEmpty(alarmObject.modulePoint)) {
            if (alarmObject.isSuccess) {
                Alarm.commitSuccess(alarmObject.module, alarmObject.modulePoint, StringUtils.stringNull2Empty(alarmObject.arg));
            } else {
                Alarm.commitFail(alarmObject.module, alarmObject.modulePoint, StringUtils.stringNull2Empty(alarmObject.arg), StringUtils.stringNull2Empty(alarmObject.errorCode), StringUtils.stringNull2Empty(alarmObject.errorMsg));
            }
        }
    }

    public void commitCount(CountObject countObject) {
        if (mAppMonitorValid && countObject != null && !TextUtils.isEmpty(countObject.module) && !TextUtils.isEmpty(countObject.modulePoint)) {
            Counter.commit(countObject.module, countObject.modulePoint, StringUtils.stringNull2Empty(countObject.arg), countObject.value);
        }
    }

    public void register(Class<?> cls) {
        if (cls != null && mAppMonitorValid) {
            Monitor monitor = (Monitor) cls.getAnnotation(Monitor.class);
            if (monitor != null) {
                Field[] declaredFields = cls.getDeclaredFields();
                List arrayList = new ArrayList();
                List arrayList2 = new ArrayList();
                DimensionSet create = DimensionSet.create();
                MeasureSet create2 = MeasureSet.create();
                for (Field field : declaredFields) {
                    if (((Dimension) field.getAnnotation(Dimension.class)) != null) {
                        field.setAccessible(true);
                        arrayList.add(field);
                        create.addDimension(field.getName());
                    } else {
                        Measure measure = (Measure) field.getAnnotation(Measure.class);
                        if (measure != null) {
                            field.setAccessible(true);
                            arrayList2.add(field);
                            if (measure.max() != Double.MAX_VALUE) {
                                create2.addMeasure(new com.alibaba.mtl.appmonitor.model.Measure(field.getName(), Double.valueOf(measure.constantValue()), Double.valueOf(measure.min()), Double.valueOf(measure.max())));
                            } else {
                                create2.addMeasure(field.getName());
                            }
                        }
                    }
                }
                dimensionFieldsCache.put(cls, arrayList);
                measureFieldsCache.put(cls, arrayList2);
                AppMonitor.register(monitor.module(), monitor.monitorPoint(), create2, create);
            }
        }
    }
}