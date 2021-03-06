package com.vcvb.chenyu.shop.adapter.item.user.real;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;
import com.bumptech.glide.request.RequestOptions;
import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.user.UserReal;

public class UserRealCardItem extends BaseItem<UserReal> {
    public static final int TYPE = R.layout.user_real_card_item;

    public UserRealCardItem(UserReal bean, Context c) {
        super(bean, c);
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
        ImageView iv1 = holder.getImageView(R.id.imageView68);
        ImageView iv2 = holder.getImageView(R.id.imageView69);
        ImageView iv3 = holder.getImageView(R.id.imageView73);
        ImageView iv4 = holder.getImageView(R.id.imageView72);
        RequestOptions requestOptions = RequestOptions.centerInsideTransform().diskCacheStrategy
                (DiskCacheStrategy.ALL).skipMemoryCache(true).override(120, 120);
        if (mData.getFront_of_id_card() != null && !mData.getFront_of_id_card().contains("styles")) {
            Glide.with(context).load(mData.getFront_of_id_card()).apply(requestOptions).into(iv1);
            iv3.setAlpha(255);
        } else {
            Glide.with(context).load(R.drawable.icon_up_card).into(iv1);
            iv3.setAlpha(0);
        }

        if (mData.getReverse_of_id_card() != null && !mData.getReverse_of_id_card().contains("styles")) {
            Glide.with(context).load(mData.getReverse_of_id_card()).apply(requestOptions).into(iv2);
            iv4.setAlpha(255);
        } else {
            Glide.with(context).load(R.drawable.icon_up_card).into(iv2);
            iv4.setAlpha(0);
        }

        TextView textView = holder.get(R.id.textView243);
        if (mData.getReview_status() == 3) {
            textView.setText(R.string.examine);
            textView.setTextColor(context.getResources().getColor(R.color.red));
        } else if (mData.getReview_status() == 2) {
            textView.setText(R.string.examine_adopt_no);
            textView.setTextColor(context.getResources().getColor(R.color.red));
        } else if (mData.getReview_status() == 1) {
            textView.setText(R.string.examine_adopt);
            textView.setTextColor(context.getResources().getColor(R.color.sky));
        } else if (mData.getReview_status() == 0) {
            textView.setText(R.string.no_submit);
            textView.setTextColor(context.getResources().getColor(R.color.black));
        }
    }
}
