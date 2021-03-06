package com.vcvb.chenyu.shop.adapter.viewholder;

import android.support.v7.widget.RecyclerView;
import android.util.SparseArray;
import android.view.View;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

public class BaseViewHolder<T> extends RecyclerView.ViewHolder {

    //子布局中的控件
    private SparseArray<View> views;
    //子布局
    private View mItemView;

    public BaseViewHolder(View itemView, int viewType, AdapterView.OnItemClickListener
            listener) {
        super(itemView);
        mItemView = itemView;
        views = new SparseArray<>();
    }

    /**
     * 获取ItemView
     *
     * @return
     */
    public View getItemView() {
        return mItemView;
    }

    public View getView(int resId) {
        return retrieveView(resId);
    }

    public TextView getTextView(int resId) {
        return retrieveView(resId);
    }

    public ImageView getImageView(int resId) {
        return retrieveView(resId);
    }

    public Button getButton(int resId) {
        return retrieveView(resId);
    }

    @SuppressWarnings("unchecked")
    protected <V extends View> V retrieveView(int viewId) {
        View view = views.get(viewId);
        if (view == null) {
            view = mItemView.findViewById(viewId);
            views.put(viewId, view);
        }
        return (V) view;
    }

    public void setText(int resId, CharSequence text) {
        getTextView(resId).setText(text);
    }

    public void setText(int resId, int strId) {
        getTextView(resId).setText(strId);
    }

    public void bindViewHolder(T t, int pos) {

    }
}
