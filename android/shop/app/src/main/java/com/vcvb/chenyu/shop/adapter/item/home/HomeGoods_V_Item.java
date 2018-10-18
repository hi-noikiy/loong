package com.vcvb.chenyu.shop.adapter.item.home;

import android.content.Context;
import android.graphics.Paint;
import android.support.constraint.ConstraintLayout;
import android.support.constraint.ConstraintSet;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;
import com.bumptech.glide.request.RequestOptions;
import com.nex3z.flowlayout.FlowLayout;
import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.goods.Goods;
import com.vcvb.chenyu.shop.tools.ToolUtils;

import java.math.BigDecimal;
import java.math.RoundingMode;

import jp.wasabeef.glide.transformations.RoundedCornersTransformation;

public class HomeGoods_V_Item extends BaseItem<Goods> {
    public static final int TYPE = Integer.MAX_VALUE - 3;

    public HomeGoods_V_Item(Goods beans, Context c) {
        super(beans, c);
    }

    @Override
    public int getItemType() {
        return TYPE;
    }

    @Override
    public CYCBaseViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        CYCBaseViewHolder base = new CYCBaseViewHolder(LayoutInflater.from(parent.getContext())
                .inflate(R.layout.goods_v_item, null));
        return base;
    }

    @Override
    public void onBindViewHolder(CYCBaseViewHolder holder, int position) {
        int width = ToolUtils.getWindowsWidth(context);
        TextView tv = holder.getTextView(R.id.textView164);
        tv.setText(mData.getGoods_name());
        TextView shopPriceV = holder.getTextView(R.id.textView166);
        shopPriceV.setText(mData.getShop_price_format());
        TextView marketPriceV = holder.getTextView(R.id.textView167);
        marketPriceV.setPaintFlags(Paint.STRIKE_THRU_TEXT_FLAG | Paint.ANTI_ALIAS_FLAG);
        marketPriceV.setText(mData.getMarket_price_format());

        FlowLayout fl = holder.get(R.id.tip_wrap);
        fl.setChildSpacing(8);
        fl.setRowSpacing(8);
        fl.setChildSpacingForLastRow(8);
        fl.removeAllViews();
        if (Integer.valueOf(mData.getIs_promote()) == 1) {
            Double marketPrice = Double.valueOf(mData.getMarket_price()).doubleValue();
            Double promotePrice = Double.valueOf(mData.getPromote_price()).doubleValue();
            BigDecimal bd = new BigDecimal(promotePrice / marketPrice * 10).setScale(1,
                    RoundingMode.UP);
            TextView textView = new TextView(context);
            textView.setText(bd.doubleValue() + "折");
            textView.setTextColor(context.getResources().getColor(R.color.red));
            textView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 10);
            textView.setGravity(Gravity.CENTER_HORIZONTAL);
            textView.setLines(1);
            textView.setMaxEms(8);
            textView.setPadding(ToolUtils.dip2px(context, 4), ToolUtils.dip2px(context, 1),
                    ToolUtils.dip2px(context, 4), ToolUtils.dip2px(context, 1));
            textView.setBackgroundResource(R.drawable.red_all_border);
            fl.addView(textView);
            shopPriceV.setText(mData.getPromote_price_format());
        }

        if (Integer.valueOf(mData.getIs_volume()) == 1) {
            Double marketPrice = Double.valueOf(mData.getMarket_price()).doubleValue();
            Double volumePrice = Double.valueOf(mData.getPromote_price()).doubleValue();
            int mun = Integer.valueOf(mData.getVolume_number()).intValue();
            BigDecimal bd = new BigDecimal(volumePrice * mun / marketPrice * mun * 10).setScale
                    (1, RoundingMode.UP);
            TextView textView = new TextView(context);
            textView.setText(mun + "件" + bd.doubleValue() + "折");
            textView.setTextColor(context.getResources().getColor(R.color.red));
            textView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 10);
            textView.setGravity(Gravity.CENTER_HORIZONTAL);
            textView.setLines(1);
            textView.setMaxEms(8);
            textView.setPadding(ToolUtils.dip2px(context, 4), ToolUtils.dip2px(context, 1),
                    ToolUtils.dip2px(context, 4), ToolUtils.dip2px(context, 1));
            textView.setBackgroundResource(R.drawable.red_all_border);
            fl.addView(textView);
            shopPriceV.setText(mData.getPromote_price_format());
        }

        ImageView iv = holder.get(R.id.imageView78);
        RoundedCornersTransformation roundedCorners = new RoundedCornersTransformation(ToolUtils.dip2px(context, 5), 0,
                RoundedCornersTransformation.CornerType.TOP);
        RequestOptions requestOptions = RequestOptions.bitmapTransform(roundedCorners)
                .diskCacheStrategy(DiskCacheStrategy.AUTOMATIC).skipMemoryCache(true).override(
                        (width - ToolUtils.dip2px(context, 9)) / 2, (width - ToolUtils.dip2px(context, 9)) / 2);
        Glide.with(context).load(mData.getOriginal_img()).apply(requestOptions).into(iv);
        ConstraintLayout cly = (ConstraintLayout) holder.getItemView();
        ConstraintSet set = new ConstraintSet();
        set.clone(cly);
        set.constrainWidth(iv.getId(), (width - ToolUtils.dip2px(context, 11)) / 2);
        set.constrainHeight(iv.getId(), (width - ToolUtils.dip2px(context, 10)) / 2);
        set.applyTo(cly);
    }
}
