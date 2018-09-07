package com.vcvb.chenyu.shop.adapter.spacesitem;

import android.content.Context;
import android.graphics.Rect;
import android.support.v7.widget.GridLayoutManager;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.View;

import com.vcvb.chenyu.shop.javaBean.collection.CollectionBean;

import java.util.List;

public class BrowseItemDecoration extends RecyclerView.ItemDecoration {
    private Context mContext;
    private List<CollectionBean> mList;

    public BrowseItemDecoration(Context context, List<CollectionBean> list) {
        super();
        mContext = context;
        mList = list;
    }

    @Override
    public void getItemOffsets(Rect outRect, View view,
                               RecyclerView parent, RecyclerView.State state) {
        if (parent.getLayoutManager() instanceof GridLayoutManager) {
            int position = parent.getChildAdapterPosition(view);
            CollectionBean bean = mList.get(position);
            if(bean != null){
                if (bean.getIsType() == 2) {
                    outRect.set(0, 16, 0, 0);
                }else{
                    outRect.set(0, 0, 0, 0);
                }
            }else {
                outRect.set(0, 0, 0, 16);
            }
        } else if (parent.getLayoutManager() instanceof LinearLayoutManager) {
            outRect.set(0, 0, 0, 0);
        } else {
            super.getItemOffsets(outRect, view, parent, state);
        }
    }
}
