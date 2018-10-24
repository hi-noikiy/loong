package com.vcvb.chenyu.shop.adapter.item.dialog;

import android.content.Context;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.style.ForegroundColorSpan;
import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.TextView;

import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.goods.GoodsGVP;

public class GoodsGVPItem extends BaseItem<GoodsGVP> {
    public static final int TYPE = 1;

    public GoodsGVPItem(GoodsGVP beans, Context c) {
        super(beans, c);
    }

    @Override
    public int getItemType() {
        return TYPE;
    }

    @Override
    public CYCBaseViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        CYCBaseViewHolder base = new CYCBaseViewHolder(LayoutInflater.from(parent.getContext())
                .inflate(R.layout.dialog_faat_item, null));
        return base;
    }

    @Override
    public void onBindViewHolder(CYCBaseViewHolder holder, int position) {
        TextView tv = holder.getTextView(R.id.textView231);
        double total = Integer.valueOf(mData.getVolume_number())*Double.valueOf(mData.getVolume_price());
        SpannableString spannableString = new SpannableString("买" + mData.getVolume_number() +
                "件￥" + String.valueOf(total));
        ForegroundColorSpan foregroundColorSpan = new ForegroundColorSpan(context.getResources()
                .getColor(R.color.red));
        spannableString.setSpan(foregroundColorSpan, 1, mData.getVolume_number().length()+1,
                Spanned.SPAN_INCLUSIVE_EXCLUSIVE);
        tv.setText(spannableString);
    }
}
