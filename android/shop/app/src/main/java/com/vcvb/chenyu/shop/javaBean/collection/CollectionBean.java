package com.vcvb.chenyu.shop.javaBean.collection;

import com.vcvb.chenyu.shop.javaBean.goods.Goods;
import com.vcvb.chenyu.shop.tools.JsonUtils;

import org.json.JSONException;
import org.json.JSONObject;

public class CollectionBean {
    private Integer rec_id;
    private String user_id;
    private Integer add_time;
    private String add_time_format;
    private boolean isLong = false;
    private boolean isSelect = false;
    private boolean isSelectAll = false;
    private Goods goods;

    public Integer getRec_id() {
        return rec_id;
    }

    public void setRec_id(Integer rec_id) {
        this.rec_id = rec_id;
    }

    public String getUser_id() {
        return user_id;
    }

    public void setUser_id(String user_id) {
        this.user_id = user_id;
    }

    public Integer getAdd_time() {
        return add_time;
    }

    public void setAdd_time(Integer add_time) {
        this.add_time = add_time;
    }

    public String getAdd_time_format() {
        return add_time_format;
    }

    public void setAdd_time_format(String add_time_format) {
        this.add_time_format = add_time_format;
    }

    public boolean isLong() {
        return isLong;
    }

    public void setLong(boolean aLong) {
        isLong = aLong;
    }

    public boolean isSelect() {
        return isSelect;
    }

    public void setSelect(boolean select) {
        isSelect = select;
    }

    public boolean isSelectAll() {
        return isSelectAll;
    }

    public void setSelectAll(boolean selectAll) {
        isSelectAll = selectAll;
    }

    public Goods getGoods() {
        return goods;
    }

    public void setGoods(Goods goods) {
        this.goods = goods;
    }

    public void setData(JSONObject json){
        try {
            JSONObject object = json.getJSONObject("goods");
            Goods goods = JsonUtils.fromJsonObject(object, Goods
                    .class);
            this.setGoods(goods);
        } catch (JSONException e) {
            e.printStackTrace();
        } catch (IllegalAccessException e) {
            e.printStackTrace();
        } catch (InstantiationException e) {
            e.printStackTrace();
        }
    }
}
