package com.vcvb.chenyu.shop.activity.center;

import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.support.v7.widget.GridLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.Gravity;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.scwang.smartrefresh.layout.api.RefreshLayout;
import com.scwang.smartrefresh.layout.listener.OnLoadMoreListener;
import com.scwang.smartrefresh.layout.listener.OnRefreshListener;
import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.activity.goods.GoodsDetailActivity;
import com.vcvb.chenyu.shop.activity.search.SearchInfoActivity;
import com.vcvb.chenyu.shop.adapter.base.Item;
import com.vcvb.chenyu.shop.adapter.item.collection.CollectionErrorItem;
import com.vcvb.chenyu.shop.adapter.item.collection.CollectionItem;
import com.vcvb.chenyu.shop.adapter.itemclick.CYCItemClickSupport;
import com.vcvb.chenyu.shop.base.BaseRecyclerViewActivity;
import com.vcvb.chenyu.shop.constant.ConstantManager;
import com.vcvb.chenyu.shop.dialog.LoadingDialog;
import com.vcvb.chenyu.shop.javaBean.collection.CollectionBean;
import com.vcvb.chenyu.shop.popwin.PopWin;
import com.vcvb.chenyu.shop.tools.HttpUtils;
import com.vcvb.chenyu.shop.tools.JsonUtils;
import com.vcvb.chenyu.shop.tools.Routes;
import com.vcvb.chenyu.shop.tools.ToolUtils;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import okhttp3.Call;

public class MyCollectionActivity extends BaseRecyclerViewActivity {
    private List<CollectionBean> collections = new ArrayList<>();

    private RefreshLayout refreshLayout;
    private Integer page = 1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        changeStatusBarTextColor(true);
        setContentView(R.layout.collection_list);
        context = this;
        setNavBack();
        initView();
        initRefresh();
        getData(true);
        initListener();
    }

    @Override
    public void setNavBack() {
        super.setNavBack();
        int gravity = Gravity.CENTER;
        LinearLayout.LayoutParams layoutParams = new LinearLayout.LayoutParams(LinearLayout
                .LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        TextView titleView = new TextView(this);
        titleView.setLayoutParams(layoutParams);
        titleView.setGravity(gravity);
        titleView.setText(R.string.collection);
        titleView.setTextColor(Color.parseColor("#000000"));
        titleView.setTextSize(18);
        titleView.setSingleLine();

        LinearLayout.LayoutParams layoutParams2 = new LinearLayout.LayoutParams(ToolUtils.dip2px
                (context, 60), LinearLayout.LayoutParams.WRAP_CONTENT);
        LinearLayout nav_other = findViewById(R.id.nav_other);
        nav_other.setLayoutParams(layoutParams2);
        nav_other.setAlpha(1);
        ImageView iv1 = findViewById(R.id.collection);
        nav_other.removeView(iv1);

        final PopWin popWindow = new PopWin(MyCollectionActivity.this, ToolUtils.dip2px(context,
                156), ToolUtils.dip2px(context, 148));
        final ImageView iv2 = findViewById(R.id.more);
        iv2.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                popWindow.showAsDropDown(iv2);
            }
        });
        popWindow.setClickListener(new PopWin.OnItemClickListener() {
            @Override
            public void onClicked(View v) {
                System.out.println(v);
            }
        });

        LinearLayout.LayoutParams layoutParams3 = new LinearLayout.LayoutParams(LinearLayout
                .LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        LinearLayout title_wrap = findViewById(R.id.title_wrap);
        title_wrap.setAlpha(1);
        title_wrap.setLayoutParams(layoutParams3);
        title_wrap.addView(titleView);
    }

    public void initView() {
        mRecyclerView = findViewById(R.id.content);
        mLayoutManager = new GridLayoutManager(context, 1);
        mRecyclerView.setLayoutManager(mLayoutManager);
        mRecyclerView.setAdapter(mAdapter);
    }

    public void initRefresh() {
        refreshLayout = findViewById(R.id.collection_list);
        refreshLayout.setOnRefreshListener(new OnRefreshListener() {
            @Override
            public void onRefresh(RefreshLayout refreshLayout) {
                getData(false);
                refreshLayout.finishRefresh(1000/*,false*/);//传入false表示刷新失败
            }
        });
        refreshLayout.setOnLoadMoreListener(new OnLoadMoreListener() {
            @Override
            public void onLoadMore(RefreshLayout refreshLayout) {
                loadmore();
                refreshLayout.finishLoadMore(1000/*,false*/);//传入false表示加载失败
            }
        });
    }

    public void getData(final boolean b) {
        page = 1;
        if (b) {
            loadingDialog = new LoadingDialog(context, R.style.TransparentDialog);
            loadingDialog.show();
        }
        HashMap<String, String> mp = new HashMap<>();
        mp.put("token", token);
        mp.put("page", page + "");
        HttpUtils.getInstance().post(ConstantManager.Url.COLLECT_GOODSES, mp, new HttpUtils
                .NetCall() {
            @Override
            public void success(Call call, final JSONObject json) throws IOException {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        if (b) {
                            loadingDialog.dismiss();
                        }
                        refreshLayout.finishRefresh();
                        bindViewData(json);
                    }
                });
            }

            @Override
            public void failed(Call call, IOException e) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        if (b) {
                            loadingDialog.dismiss();
                        }
                        refreshLayout.finishRefresh();
                    }
                });
            }
        });
    }

    public void bindViewData(JSONObject json) {
        collections.clear();
        mAdapter.clear();
        if (json != null) {
            try {
                Integer code = json.getInt("code");
                if (code == 0) {
                    JSONArray arr = json.getJSONArray("data");
                    for (int i = 0; i < arr.length(); i++) {
                        JSONObject object = (JSONObject) arr.get(i);
                        CollectionBean bean = JsonUtils.fromJsonObject(object, CollectionBean
                                .class);
                        bean.setData(object);
                        collections.add(bean);
                    }
                }
            } catch (JSONException e) {
                e.printStackTrace();
            } catch (IllegalAccessException e) {
                e.printStackTrace();
            } catch (InstantiationException e) {
                e.printStackTrace();
            }
        }
        mAdapter.addAll(getItems(collections));
    }

    public void loadmore() {
        HashMap<String, String> mp = new HashMap<>();
        page += 1;
        mp.put("token", token);
        mp.put("page", page + "");
        HttpUtils.getInstance().post(Routes.getInstance().getIndex(), mp, new HttpUtils.NetCall() {
            @Override
            public void success(Call call, final JSONObject json) throws IOException {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        refreshLayout.finishLoadMore();
                        bindViewMoreData(json);

                    }
                });
            }

            @Override
            public void failed(Call call, IOException e) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        refreshLayout.finishLoadMore();
                    }
                });
            }
        });
    }

    public void bindViewMoreData(JSONObject json) {
        if (json != null) {

        }
    }

    protected List<Item> getItems(List<CollectionBean> list) {
        List<Item> cells = new ArrayList<>();
        if (list == null || list.size() == 0) {
            cells.add(new CollectionErrorItem(null, context));
        } else {
            for (int i = 0; i < list.size(); i++) {
                CollectionItem collectionItem = new CollectionItem(list.get(i), context);
                collectionItem.setOnItemClickListener(collectionListener);
                cells.add(collectionItem);
            }
        }
        return cells;
    }

    public void initListener() {
        CYCItemClickSupport.addTo(mRecyclerView).setOnItemLongClickListener(new CYCItemClickSupport.OnItemLongClickListener() {
            @Override
            public boolean onItemLongClicked(RecyclerView recyclerView, View itemView, int
                    position) {
                clearLong();
                collections.get(position).setLong(true);
                mAdapter.notifyDataSetChanged();
                return true;
            }
        });
    }

    //取消收藏
    public void cancelCollect(final Integer pos) {
        HashMap<String, String> mp = new HashMap<>();
        mp.put("goods_id", collections.get(pos).getGoods().getGoods_id() + "");
        mp.put("token", token);
        HttpUtils.getInstance().post(ConstantManager.Url.ADD_COLLECT_GOODS, mp, new HttpUtils
                .NetCall() {
            @Override
            public void success(Call call, JSONObject json) throws IOException {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        collections.remove(pos);
                        mAdapter.remove(pos);
                        mAdapter.notifyDataSetChanged();
                    }
                });
            }

            @Override
            public void failed(Call call, IOException e) {

            }
        });
    }

    //清理长按显示状态
    public void clearLong() {
        for (int i = 0; i < collections.size(); i++) {
            if (collections.get(i).isLong()) {
                collections.get(i).setLong(false);
            }
        }
    }

    CollectionItem.OnClickListener collectionListener = new CollectionItem.OnClickListener() {
        @Override
        public void onClicked(View view, int pos) {
            clearLong();
            switch (view.getId()) {
                case R.id.textView113:
                    //找相似
                    Intent intentS = new Intent(MyCollectionActivity.this, SearchInfoActivity.class);
                    intentS.putExtra("cate", collections.get(pos).getGoods().getCat_id());
                    startActivity(intentS);
                    break;
                case R.id.imageView48:
                case R.id.imageView47:
                    //商品详情
                    Intent intent = new Intent(MyCollectionActivity.this, GoodsDetailActivity
                            .class);
                    intent.putExtra("id", collections.get(pos).getGoods().getGoods_id());
                    startActivity(intent);
                    mAdapter.notifyDataSetChanged();
                    break;
                case R.id.view32:
                    break;
                case R.id.textView119:
                    //取消收藏
                    cancelCollect(pos);
                    break;
            }
        }
    };
}
