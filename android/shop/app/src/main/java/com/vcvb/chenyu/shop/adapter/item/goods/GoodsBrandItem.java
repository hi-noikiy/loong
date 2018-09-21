package com.vcvb.chenyu.shop.adapter.item.goods;

import android.content.Context;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.adapter.CYCSimpleAdapter;
import com.vcvb.chenyu.shop.adapter.base.BaseItem;
import com.vcvb.chenyu.shop.adapter.base.CYCBaseViewHolder;
import com.vcvb.chenyu.shop.adapter.base.Item;
import com.vcvb.chenyu.shop.adapter.spacesitem.DefaultItemDecoration;
import com.vcvb.chenyu.shop.javaBean.goods.GoodsBrand;

import java.util.ArrayList;
import java.util.List;

public class GoodsBrandItem extends BaseItem<List<GoodsBrand>> {
    public static final int TYPE = 10;
    private DefaultItemDecoration defaultItemDecoration;
    private RecyclerView recyclerView;

    public GoodsBrandItem(List<GoodsBrand> beans, Context c) {
        super(beans, c);
    }

    @Override
    public int getItemType() {
        return TYPE;
    }

    @Override
    public CYCBaseViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        CYCBaseViewHolder base = new CYCBaseViewHolder(LayoutInflater.from(parent.getContext())
                .inflate(R.layout.goods_brand_item, null));
        return base;
    }

    @Override
    public void onBindViewHolder(CYCBaseViewHolder holder, int position) {
        if(recyclerView == null){
            recyclerView = (RecyclerView) holder.getView(R.id.brand_list);
            CYCSimpleAdapter mAdapter = new CYCSimpleAdapter();
            LinearLayoutManager mLayoutManager = new LinearLayoutManager(context);
            mLayoutManager.setOrientation(LinearLayoutManager.HORIZONTAL);
            if (defaultItemDecoration == null) {
                defaultItemDecoration = new DefaultItemDecoration(context, 5);
                recyclerView.addItemDecoration(defaultItemDecoration);
            }
            recyclerView.setLayoutManager(mLayoutManager);
            recyclerView.setAdapter(mAdapter);
            mAdapter.addAll(getItems(mData));
        }
    }

    public List<Item> getItems(List<GoodsBrand> bean) {
        List<Item> cells = new ArrayList<>();
        for (int i = 0; i < bean.size(); i++) {
            cells.add(new GoodsBrandSubItem(bean.get(i), context));
        }
        return cells;
    }
}
