package com.vcvb.chenyu.shop.adapter;

import android.content.Context;

import com.donkingliang.groupedadapter.adapter.GroupedRecyclerViewAdapter;
import com.donkingliang.groupedadapter.holder.BaseViewHolder;
import com.vcvb.chenyu.shop.javaBean.BaseBean;

import java.util.List;

public class GroupedListAdapter<T extends BaseBean> extends GroupedRecyclerViewAdapter {

    private List<T> mGroups;

    public GroupedListAdapter(Context context) {
        super(context);
    }

    public void setData(List<T> groups) {
        mGroups = groups;
    }

    @Override
    public int getGroupCount() {
        return mGroups == null ? 0 : mGroups.size();
    }

    @Override
    public int getChildrenCount(int groupPosition) {
        return mGroups.get(groupPosition) == null ? 0 : mGroups.get(groupPosition).getObjs().size();
    }

    @Override
    public boolean hasHeader(int groupPosition) {
        if (mGroups.get(groupPosition).getHeader() != null) {
            return true;
        } else {
            return false;
        }
    }

    @Override
    public boolean hasFooter(int groupPosition) {
        if (mGroups.get(groupPosition).getFooter() != null) {
            return true;
        } else {
            return false;
        }
    }

    @Override
    public int getHeaderLayout(int viewType) {
        return viewType;
    }

    @Override
    public int getFooterLayout(int viewType) {
        return viewType;
    }

    @Override
    public int getChildLayout(int viewType) {
        return viewType;
    }

    @Override
    public int getHeaderViewType(int groupPosition) {
        return mGroups.get(groupPosition).getMheader().getItemType();
    }

    @Override
    public int getFooterViewType(int groupPosition) {
        return mGroups.get(groupPosition).getMfooter().getItemType();
    }

    @Override
    public int getChildViewType(int groupPosition, int childPosition) {
        return mGroups.get(groupPosition).getItemList().get(childPosition).getItemType();
    }

    @Override
    public void onBindHeaderViewHolder(BaseViewHolder holder, int groupPosition) {
        mGroups.get(groupPosition).getMheader().onBindViewHolder(holder, groupPosition, 0);
    }

    @Override
    public void onBindFooterViewHolder(BaseViewHolder holder, int groupPosition) {
        mGroups.get(groupPosition).getMfooter().onBindViewHolder(holder, groupPosition, 0);
    }

    @Override
    public void onBindChildViewHolder(BaseViewHolder holder, int groupPosition, int childPosition) {
        mGroups.get(groupPosition).getItemList().get(childPosition).onBindViewHolder(holder,
                groupPosition, childPosition);
    }
}
