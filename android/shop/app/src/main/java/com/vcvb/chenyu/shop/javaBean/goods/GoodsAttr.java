package com.vcvb.chenyu.shop.javaBean.goods;

import java.io.Serializable;

public class GoodsAttr implements Serializable {
    private boolean isSelect = false;
    private int buttonId = 0;

    private Integer goods_attr_id;
    private Integer goods_id;
    private Integer attr_id;
    private Integer attr_group;
    private String color_value;
    private String attr_price;
    private String attr_value;
    private String attr_img_flie;
    private String attr_gallery_flie;
    private String attr_name;


    public boolean getIsSelect() {
        return isSelect;
    }

    public void setIsSelect(boolean isSelect) {
        this.isSelect = isSelect;
    }

    public int getButtonId() {
        return buttonId;
    }

    public void setButtonId(int buttonId) {
        this.buttonId = buttonId;
    }

    public Integer getGoods_attr_id() {
        return goods_attr_id;
    }

    public void setGoods_attr_id(Integer goods_attr_id) {
        this.goods_attr_id = goods_attr_id;
    }

    public Integer getGoods_id() {
        return goods_id;
    }

    public void setGoods_id(Integer goods_id) {
        this.goods_id = goods_id;
    }

    public Integer getAttr_id() {
        return attr_id;
    }

    public void setAttr_id(Integer attr_id) {
        this.attr_id = attr_id;
    }

    public Integer getAttr_group() {
        return attr_group;
    }

    public void setAttr_group(Integer attr_group) {
        this.attr_group = attr_group;
    }

    public String getColor_value() {
        return color_value;
    }

    public void setColor_value(String color_value) {
        this.color_value = color_value;
    }

    public String getAttr_price() {
        return attr_price;
    }

    public void setAttr_price(String attr_price) {
        this.attr_price = attr_price;
    }

    public String getAttr_value() {
        return attr_value;
    }

    public void setAttr_value(String attr_value) {
        this.attr_value = attr_value;
    }

    public String getAttr_img_flie() {
        return attr_img_flie;
    }

    public void setAttr_img_flie(String attr_img_flie) {
        this.attr_img_flie = attr_img_flie;
    }

    public String getAttr_gallery_flie() {
        return attr_gallery_flie;
    }

    public void setAttr_gallery_flie(String attr_gallery_flie) {
        this.attr_gallery_flie = attr_gallery_flie;
    }

    public String getAttr_name() {
        return attr_name;
    }

    public void setAttr_name(String attr_name) {
        this.attr_name = attr_name;
    }
}
