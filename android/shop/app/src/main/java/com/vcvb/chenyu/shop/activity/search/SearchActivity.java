package com.vcvb.chenyu.shop.activity.search;

import android.content.Intent;
import android.os.Bundle;
import android.support.constraint.ConstraintLayout;
import android.text.TextUtils;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.View;
import android.view.WindowManager;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;

import com.nex3z.flowlayout.FlowLayout;
import com.vcvb.chenyu.shop.R;
import com.vcvb.chenyu.shop.base.BaseActivity;
import com.vcvb.chenyu.shop.constant.ConstantManager;
import com.vcvb.chenyu.shop.javaBean.search.KeyWords;
import com.vcvb.chenyu.shop.tools.HttpUtils;
import com.vcvb.chenyu.shop.tools.IdsUtils;
import com.vcvb.chenyu.shop.tools.ToastUtils;
import com.vcvb.chenyu.shop.tools.ToolUtils;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import okhttp3.Call;
import xiaofei.library.datastorage.DataStorageFactory;
import xiaofei.library.datastorage.IDataStorage;

public class SearchActivity extends BaseActivity {

    private EditText search;
    private TextView searchBt;
    private List<KeyWords> searchs = new ArrayList<>();
    private List<KeyWords> keys = new ArrayList<>();
    private List<KeyWords> cates = new ArrayList<>();
    private FlowLayout flowLayout1;
    private FlowLayout flowLayout2;
    private FlowLayout flowLayout3;
    private ImageView trashV;
    private TextView changeV;
    private int isFrom;

    IDataStorage dataStorage;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.search);
        context = this;
        changeStatusBarTextColor(true);
        dataStorage = DataStorageFactory.getInstance(context, DataStorageFactory.TYPE_DATABASE);
        setNavBack();
        initView();
        getData(true);
        initListener();
        isFrom = getIntent().getIntExtra("isfrom", 0);
    }

    @Override
    public void setNavBack() {
        ImageView nav_back = findViewById(R.id.imageView23);
        if (nav_back != null) {
            nav_back.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    finish();
                    overridePendingTransition(0, 0);
                }
            });
        }
        search = findViewById(R.id.editText13);
        search.setFocusable(true);
        search.setFocusableInTouchMode(true);
        search.requestFocus();
        this.getWindow().setSoftInputMode(WindowManager.LayoutParams
                .SOFT_INPUT_STATE_ALWAYS_VISIBLE);

        searchBt = findViewById(R.id.textView153);
        searchBt.setOnClickListener(listener);
    }

    @Override
    public void initView() {
        super.initView();
        flowLayout1 = findViewById(R.id.wrap_now);
        flowLayout2 = findViewById(R.id.wrap_hot);
        flowLayout3 = findViewById(R.id.wrap_cate);
        flowLayout1.setChildSpacing(8);
        flowLayout1.setRowSpacing(8);
        flowLayout1.setChildSpacingForLastRow(8);
        flowLayout2.setChildSpacing(8);
        flowLayout2.setRowSpacing(8);
        flowLayout2.setChildSpacingForLastRow(8);
        flowLayout3.setChildSpacing(8);
        flowLayout3.setRowSpacing(8);
        flowLayout3.setChildSpacingForLastRow(8);

        trashV = findViewById(R.id.imageView81);
        changeV = findViewById(R.id.textView264);
    }

    @Override
    public void getData(boolean b) {
        super.getData(b);
        initSearchView();

        HttpUtils.getInstance().post(ConstantManager.Url.SEARCH_KEYWORDS, null, new HttpUtils
                .NetCall() {

            @Override
            public void success(Call call, final JSONObject json) throws IOException {
                if (json != null) {
                    try {
                        if (json.getInt("code") == 0) {
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    bindData(json);
                                }
                            });
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }
            }

            @Override
            public void failed(Call call, IOException e) {

            }
        });
    }

    //添加最近搜索
    public void initSearchView() {
        flowLayout1.removeAllViews();
        searchs = dataStorage.loadAll(KeyWords.class);
        if (searchs != null && searchs.size() > 0) {
            for (int i = 0; i < searchs.size(); i++) {
                TextView textView = new TextView(context);
                textView.setText(searchs.get(i).getTitle());
                textView.setId(IdsUtils.generateViewId());
                textView.setTextColor(context.getResources().getColor(R.color.black));
                textView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
                textView.setEllipsize(TextUtils.TruncateAt.END);
                textView.setGravity(Gravity.CENTER_HORIZONTAL);
                textView.setLines(1);
                textView.setMaxEms(8);
                textView.setPadding(ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8),
                        ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8));
                textView.setBackgroundResource(R.drawable.shape_6_gray_d);
                textView.setTag(searchs.get(i).getTitle());
                textView.setOnClickListener(listener);
                flowLayout1.addView(textView);
            }
        } else {
            TextView textView = new TextView(context);
            textView.setText(R.string.now_search_history);
            textView.setTextColor(context.getResources().getColor(R.color.black));
            textView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
            textView.setEllipsize(TextUtils.TruncateAt.END);
            textView.setGravity(Gravity.CENTER_HORIZONTAL);
            textView.setLines(1);
            textView.setMaxEms(8);
            textView.setPadding(ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8),
                    ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8));
            textView.setBackgroundResource(R.drawable.shape_6_gray_d);
            flowLayout1.addView(textView);
        }
    }

    //热门搜索
    public void bindData(JSONObject json) {
        try {
            JSONObject data = json.getJSONObject("data");

            JSONArray keyJsonArray = data.getJSONArray("keyword");
            for (int i = 0; i < keyJsonArray.length(); i++) {
                JSONObject object = (JSONObject) keyJsonArray.get(i);
                KeyWords keyWords = new KeyWords();
                keyWords.setTitle(object.getString("keyword"));
                keys.add(keyWords);
                TextView textView = new TextView(context);
                textView.setText(keyWords.getTitle());
                textView.setId(IdsUtils.generateViewId());
                textView.setTextColor(context.getResources().getColor(R.color.black));
                textView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
                textView.setEllipsize(TextUtils.TruncateAt.END);
                textView.setGravity(Gravity.CENTER_HORIZONTAL);
                textView.setLines(1);
                textView.setMaxEms(8);
                textView.setPadding(ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8),
                        ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8));
                if (i < 4) {
                    textView.setBackgroundResource(R.drawable.shape_6_red);
                    textView.setTextColor(context.getResources().getColor(R.color.white));
                } else {
                    textView.setBackgroundResource(R.drawable.shape_6_gray_d);
                    textView.setTextColor(context.getResources().getColor(R.color.black));
                }
                textView.setTag(keyWords.getTitle());
                textView.setOnClickListener(listener);
                flowLayout2.addView(textView);
            }

            JSONArray cateJsonArray = data.getJSONArray("cate");
            for (int i = 0; i < cateJsonArray.length(); i++) {
                JSONObject object = (JSONObject) cateJsonArray.get(i);
                KeyWords keyWords = new KeyWords();
                keyWords.setTitle(object.getString("cate_name"));
                keyWords.setCateId(object.getInt("cate_id"));
                cates.add(keyWords);
                TextView textView = new TextView(context);
                textView.setText(keyWords.getTitle());
                ConstraintLayout.LayoutParams lp = new ConstraintLayout.LayoutParams(ToolUtils
                        .dip2px(context, 50), ToolUtils.dip2px(context, 50));
                textView.setLayoutParams(lp);
                textView.setId(IdsUtils.generateViewId());
                textView.setTextColor(context.getResources().getColor(R.color.black));
                textView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
                textView.setEllipsize(TextUtils.TruncateAt.END);
                textView.setGravity(Gravity.CENTER);
                textView.setSingleLine(false);
                textView.setLines(2);
                textView.setMaxEms(8);
                textView.setPadding(ToolUtils.dip2px(context, 10), ToolUtils.dip2px(context, 10),
                        ToolUtils.dip2px(context, 10), ToolUtils.dip2px(context, 10));
                textView.setBackgroundResource(R.drawable.shape_60_gray_d);
                textView.setOnClickListener(listener);
                textView.setTag(keyWords.getCateId());
                flowLayout3.addView(textView);
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }

    }

    //更新最近搜索的数据
    public void updateSearchKeywords(String str) {
        KeyWords bean = new KeyWords();
        bean.setTitle(str);
        boolean b = true;
        for (int i = 0; i < searchs.size(); i++) {
            if (search.getText().toString().equals(searchs.get(i).getTitle())) {
                b = false;
            }
        }
        if (b) {
            if (searchs.size() >= 10) {
                searchs.remove(searchs.size() - 1);
            }
            searchs.add(0, bean);
            dataStorage.deleteAll(KeyWords.class);
            dataStorage.storeOrUpdate(searchs);
        }
    }

    //换一换热门关键字
    public void changeKeywords() {
        HttpUtils.getInstance().post(ConstantManager.Url.SEARCH_KEYWORDS_CHANGE, null, new
                HttpUtils.NetCall() {
            @Override
            public void success(Call call, final JSONObject json) throws IOException {
                System.out.println(json);
                if (json != null) {
                    try {
                        if (json.getInt("code") == 0) {
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    bindChangeKeywords(json);
                                }
                            });
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }
            }

            @Override
            public void failed(Call call, IOException e) {

            }
        });
    }

    public void bindChangeKeywords(JSONObject json) {
        flowLayout2.removeAllViews();
        try {
            JSONArray keyJsonArray = json.getJSONArray("data");
            for (int i = 0; i < keyJsonArray.length(); i++) {
                JSONObject object = (JSONObject) keyJsonArray.get(i);
                KeyWords keyWords = new KeyWords();
                keyWords.setTitle(object.getString("keyword"));
                keys.add(keyWords);
                TextView textView = new TextView(context);
                textView.setText(keyWords.getTitle());
                textView.setId(IdsUtils.generateViewId());
                textView.setTextColor(context.getResources().getColor(R.color.black));
                textView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
                textView.setEllipsize(TextUtils.TruncateAt.END);
                textView.setGravity(Gravity.CENTER_HORIZONTAL);
                textView.setLines(1);
                textView.setMaxEms(8);
                textView.setPadding(ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8),
                        ToolUtils.dip2px(context, 18), ToolUtils.dip2px(context, 8));
                if (i < 4) {
                    textView.setBackgroundResource(R.drawable.shape_6_red);
                    textView.setTextColor(context.getResources().getColor(R.color.white));
                } else {
                    textView.setBackgroundResource(R.drawable.shape_6_gray_d);
                    textView.setTextColor(context.getResources().getColor(R.color.black));
                }
                textView.setTag(keyWords.getTitle());
                textView.setOnClickListener(listener);
                flowLayout2.addView(textView);
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    @Override
    public void initListener() {
        super.initListener();
        trashV.setOnClickListener(listener);
        changeV.setOnClickListener(listener);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK) {
            switch (requestCode) {
                case ConstantManager.IsFrom.FROM_HOME:
                    break;
            }
        }
    }

    View.OnClickListener listener = new View.OnClickListener() {
        @Override
        public void onClick(View view) {
            switch (view.getId()) {
                case R.id.textView153:
                    //搜索
                    if (!TextUtils.isEmpty(search.getText())) {
                        updateSearchKeywords(search.getText().toString());
                        Intent intent;
                        if (isFrom == ConstantManager.IsFrom.FROM_HOME) {
                            intent = new Intent(SearchActivity.this, SearchInfoActivity.class);
                            intent.putExtra("keywords", search.getText().toString());
                            startActivity(intent);
                            finish();
                        } else if (isFrom == ConstantManager.IsFrom.FROM_SEARCHINFO) {
                            intent = new Intent();
                            intent.putExtra("keywords", search.getText().toString());
                            setResult(RESULT_OK, intent);
                            finish();
                        }
                        overridePendingTransition(0, 0);
                    } else {
                        ToastUtils.showShortToast(context, "请输入关键字");
                    }
                    break;
                case R.id.imageView81://fixme 删除搜索历史
                    dataStorage.deleteAll(KeyWords.class);
                    initSearchView();
                    break;
                case R.id.textView264://fixme 换一换热门关键字
                    changeKeywords();
                    break;
                default:
                    //常用设置
                    if (findViewById(view.getId()) instanceof TextView) {
                        if (findViewById(view.getId()).getTag() != null) {
                            Intent intent;
                            if (isFrom == ConstantManager.IsFrom.FROM_HOME) {
                                TextView textView = findViewById(view.getId());
                                Object obj = textView.getTag();
                                String text = textView.getText().toString();
                                intent = new Intent(SearchActivity.this, SearchInfoActivity.class);
                                if (obj instanceof Integer) {
                                    intent.putExtra("cate", (Integer) obj);
                                    intent.putExtra("cate_name", text);
                                } else {
                                    intent.putExtra("keywords", (String) obj);
                                    updateSearchKeywords((String) obj);
                                }
                                startActivity(intent);
                                finish();
                            } else if (isFrom == ConstantManager.IsFrom.FROM_SEARCHINFO) {
                                intent = new Intent();
                                TextView textView = findViewById(view.getId());
                                Object obj = textView.getTag();
                                String text = textView.getText().toString();
                                if (obj instanceof Integer) {
                                    intent.putExtra("cate", (Integer) obj);
                                    intent.putExtra("cate_name", text);
                                } else {
                                    intent.putExtra("keywords", (String) obj);
                                    updateSearchKeywords((String) obj);
                                }
                                setResult(RESULT_OK, intent);
                                finish();
                            }
                            overridePendingTransition(0, 0);
                        }
                    }
                    break;
            }
        }
    };
}
