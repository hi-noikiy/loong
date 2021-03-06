package com.vcvb.chenyu.shop.adapter.item.goods;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.goods.GoodsDetail;

public class GoodsExplainItem extends BaseItem<GoodsDetail> {
    public static final int TYPE = R.layout.goods_explain_item;

    public GoodsExplainItem(GoodsDetail beans, Context c) {
        super(beans, c);
    }

    @Override
    public int getItemType() {
        return TYPE;
    }

    @Override
    public CYCBaseViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        CYCBaseViewHolder base = new CYCBaseViewHolder(LayoutInflater.from(parent.getContext())
                .inflate(TYPE, null));
        return base;
    }

    @Override
    public void onBindViewHolder(CYCBaseViewHolder holder, int position) {
        TextView tv = holder.getTextView(R.id.textView238);
        String str = "";
        if (mData.getGoodsTexAttr() != null) {
            if (mData.getGoodsTexAttr().getAttr_value().equals("0")) {
                str = "包税 | ";
            } else {
                str = "税率" + mData.getGoodsTexAttr().getAttr_value() + "% | ";
            }
        }
        for (int i = 0; i < mData.getGoodsDescriptions().size(); i++) {
            if (i == mData.getGoodsDescriptions().size() - 1) {
                str += mData.getGoodsDescriptions().get(i).getTitle();
            } else {
                str += mData.getGoodsDescriptions().get(i).getTitle() + " | ";
            }
        }
        tv.setText(str);
        View v = holder.getItemView();
        posMap.put(v.getId(), 0);
        v.setOnClickListener(listener);
    }
}
