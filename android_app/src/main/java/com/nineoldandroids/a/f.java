package com.nineoldandroids.a;

/* compiled from: IntEvaluator */
public class f implements l<Integer> {
    public Integer a(float f, Integer num, Integer num2) {
        int intValue = num.intValue();
        return Integer.valueOf((int) ((((float) (num2.intValue() - intValue)) * f) + ((float) intValue)));
    }
}