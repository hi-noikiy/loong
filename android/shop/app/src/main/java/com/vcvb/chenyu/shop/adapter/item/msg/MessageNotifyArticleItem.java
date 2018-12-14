package com.vcvb.chenyu.shop.adapter.item.msg;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;
import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.msg.NotifyMsgArticle;

import java.util.List;
import java.util.Locale;

public class MessageNotifyArticleItem extends BaseItem<List<NotifyMsgArticle>> {
    public static final int TYPE = 4;

    public MessageNotifyArticleItem(List<NotifyMsgArticle> bean, Context c) {
        super(bean, c);
    }

    @Override
    public int getItemType() {
        return TYPE;
    }

    @Override
    public CYCBaseViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        CYCBaseViewHolder base = new CYCBaseViewHolder(LayoutInflater.from(parent.getContext())
                .inflate(R.layout.message_item, null));
        return base;
    }

    @Override
    public void onBindViewHolder(CYCBaseViewHolder holder, int position) {
        TextView num = holder.get(R.id.textView220);
        ImageView iv = holder.get(R.id.imageView89);
        Glide.with(context).load(R.drawable.icon_article).into(iv);
        int k = 0;
        NotifyMsgArticle notifyMsgArticle = new NotifyMsgArticle();
        for (int i = 0; i < mData.size(); i++) {
            if (!mData.get(i).isIs_look()) {
                k++;
            }
            if (i == mData.size() - 1) {
                notifyMsgArticle = mData.get(i);
            }
        }
        if (k > 0) {
            String str = "%d";
            num.setText(String.format(Locale.CHINA, str, k));
            num.setAlpha(1);
        } else {
            num.setAlpha(0);
        }

        if(notifyMsgArticle.getDescribe() != null){
            TextView title = holder.get(R.id.textView218);
            title.setText(notifyMsgArticle.getDescribe());
        }
        if(notifyMsgArticle.getContent() != null){
            TextView content = holder.get(R.id.textView219);
            content.setText(notifyMsgArticle.getContent());
        }

        View v = holder.getItemView();
        posMap.put(v.getId(),position);
        v.setOnClickListener(listener);
    }
}
