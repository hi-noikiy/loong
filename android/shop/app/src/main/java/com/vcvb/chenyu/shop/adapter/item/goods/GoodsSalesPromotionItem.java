package com.vcvb.chenyu.shop.adapter.item.goods;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.TextView;

import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.goods.GoodsDetail;
import com.vcvb.chenyu.shop.javaBean.goods.GoodsFaat;
import com.vcvb.chenyu.shop.javaBean.goods.GoodsSecKill;

import cn.iwgang.countdownview.CountdownView;

public class GoodsSalesPromotionItem extends BaseItem<GoodsDetail> {
    public static final int TYPE = R.layout.goods_sales_promotion_item;

    public GoodsSalesPromotionItem(GoodsDetail beans, Context c) {
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
        TextView tv1 = holder.get(R.id.textView13);
        TextView tv2 = holder.get(R.id.textView14);
        CountdownView cdv = holder.get(R.id.countdownView);
        CountdownView cdv1 = holder.get(R.id.countdownView1);

        GoodsFaat goodsFaat = mData.getGoodsFaat();
        GoodsSecKill goodsSecKill = mData.getGoodsSecKill();

        if (goodsFaat != null) {
            tv1.setText(goodsFaat.getAct_name());
            String str = "满%s";
            if (goodsFaat.getAct_type() == 0) {
                //享受赠品（特惠品）
                str += "送价值%s的%s";
                Integer price = 0;
                StringBuilder name = new StringBuilder();
                for (int i = 0; i < goodsFaat.getGifts().size(); i++) {
                    price += Integer.valueOf(goodsFaat.getGifts().get(i).getPrice());
                    name.append(goodsFaat.getGifts().get(i).getName()).append(",");
                }
                tv2.setText(String.format(str, goodsFaat.getMin_amount(), price, name.toString()));
            } else if (goodsFaat.getAct_type() == 1) {
                //享受现金减免
                str += "减%s";
                tv2.setText(String.format(str, goodsFaat.getMin_amount(), goodsFaat
                        .getAct_type_ext()));
            } else if (goodsFaat.getAct_type() == 2) {
                //享受价格折扣
                str += "打%s折";
                tv2.setText(String.format(str, goodsFaat.getMin_amount(), goodsFaat
                        .getAct_type_ext()));
            }

            Integer countDown = goodsFaat.getEnd_time() - goodsFaat.getCurrent_time();
            Long current_time = countDown.longValue() * 1000;

            if (countDown / 86400 > 2) {
                cdv.setAlpha(1);
                cdv1.setAlpha(0);
            } else {
                cdv.setAlpha(0);
                cdv1.setAlpha(1);
            }
            //毫秒数
            cdv.start(current_time);
            cdv1.start(current_time);
        } else if (goodsSecKill != null) {
            tv1.setText(goodsSecKill.getActi_title());
            String str = "秒杀价 ￥%s";
            tv2.setText(String.format(str, goodsSecKill.getSec_price()));
            cdv.setAlpha(0);
            cdv1.setAlpha(1);
            Integer countDown = goodsSecKill.getE_time() - Integer.valueOf(mData.getCurrent_time().toString());
            Long current_time = countDown.longValue() * 1000;
            cdv1.start(current_time);
        }
    }
}
