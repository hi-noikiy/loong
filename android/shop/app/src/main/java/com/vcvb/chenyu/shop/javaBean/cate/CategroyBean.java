package com.vcvb.chenyu.shop.javaBean.cate;

public class CategroyBean {
    private int isType = 1;
    private String cateName;
    private String pic;
    private boolean isCurrent = false;

    public int getIsType() {
        return isType;
    }

    public void setIsType(int isType) {
        this.isType = isType;
    }

    public String getCateName() {
        return cateName;
    }

    public void setCateName(String cateName) {
        this.cateName = cateName;
    }

    public String getPic() {
        return pic;
    }

    public void setPic(String pic) {
        this.pic = pic;
    }

    public boolean getIsCurrent() {
        return isCurrent;
    }

    public void setIsCurrent(boolean isCurrent) {
        this.isCurrent = isCurrent;
    }
}
