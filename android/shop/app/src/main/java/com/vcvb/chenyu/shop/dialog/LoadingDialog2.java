package com.vcvb.chenyu.shop.dialog;

import android.app.AlertDialog;
import android.content.Context;
import android.os.Bundle;

import com.vcvb.chenyu.shop.R;
import com.wang.avi.AVLoadingIndicatorView;

public class LoadingDialog2 extends AlertDialog {
    private static LoadingDialog2 loadingDialog;
    private AVLoadingIndicatorView avi;

    public static LoadingDialog2 getInstance(Context context) {
        if(loadingDialog == null){
            loadingDialog = new LoadingDialog2(context, R.style.TransparentDialog); //设置AlertDialog背景透明
            loadingDialog.setCancelable(false);
            loadingDialog.setCanceledOnTouchOutside(false);
        }
        return loadingDialog;
    }

    public LoadingDialog2(Context context, int themeResId) {
        super(context,themeResId);
    }

    public LoadingDialog2(Context context) {
        super(context,R.style.TransparentDialog);
        this.setCancelable(false);
        this.setCanceledOnTouchOutside(false);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        this.setContentView(R.layout.dialog_loading2);
        avi = (AVLoadingIndicatorView)this.findViewById(R.id.avi);
    }

    public LoadingDialog2 setType(String indicator){
        avi.setIndicator(indicator);
        return loadingDialog;
    }

    @Override
    public void show() {
        super.show();
        avi.show();
    }

    @Override
    public void dismiss() {
        super.dismiss();
    }
}
