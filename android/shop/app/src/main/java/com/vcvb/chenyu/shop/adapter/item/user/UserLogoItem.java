package com.vcvb.chenyu.shop.adapter.item.user;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.request.RequestOptions;
import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.user.UserInfoBean;

public class UserLogoItem extends BaseItem<UserInfoBean> {
    public static final int TYPE = R.layout.user_logo_item;

    public UserLogoItem(UserInfoBean bean, Context c) {
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
        TextView tv = holder.getTextView(R.id.textView139);
        ImageView iv = holder.getImageView(R.id.imageView61);
        tv.setText(R.string.logo_subtitle);
        RequestOptions requestOptions = RequestOptions.circleCropTransform().error(R.drawable
                .icon_boy_head).override(120, 120);
        Glide.with(context).load(mData.getLogo()).apply(requestOptions).into(iv);

    }
}
