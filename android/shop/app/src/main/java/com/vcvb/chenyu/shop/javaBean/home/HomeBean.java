package com.vcvb.chenyu.shop.javaBean.home;

import com.vcvb.chenyu.shop.javaBean.goods.Goods;
import com.vcvb.chenyu.shop.tools.JsonUtils;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class HomeBean {

    private List<Adses> adses;
    private List<Goods> goodses;

    public List<Adses> getAdses() {
        return adses;
    }

    public void setAdses(List<Adses> adses) {
        this.adses = adses;
    }

    public List<Goods> getGoodses() {
        return goodses;
    }

    public void setGoodses(List<Goods> goodses) {
        this.goodses = goodses;
    }

    public void setData(JSONObject Json){
        try {
            JSONArray goodsesJsonArray = Json.getJSONArray("goodses");
            List<Goods> goodses = new ArrayList<>();
            for (int i = 0; i < goodsesJsonArray.length(); i++) {
                JSONObject object = (JSONObject) goodsesJsonArray.get(i);
                Goods goods = JsonUtils.fromJsonObject(object, Goods.class);
                goodses.add(goods);
            }
            this.setGoodses(goodses);
        } catch (JSONException e) {
            e.printStackTrace();
        } catch (IllegalAccessException e) {
            e.printStackTrace();
        } catch (java.lang.InstantiationException e) {
            e.printStackTrace();
        }

        try {
            JSONArray adsesJsonArray = Json.getJSONArray("adses");
            List<Adses> adses = new ArrayList<>();
            for (int i = 0; i < adsesJsonArray.length(); i++) {
                JSONObject object = (JSONObject) adsesJsonArray.get(i);
                Adses ads = new Adses();
                ads.setData(object);
                adses.add(ads);
            }
            this.setAdses(adses);
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }
}
