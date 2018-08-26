package com.umeng.socialize.media;

import android.content.Context;
import com.sina.weibo.sdk.exception.WeiboException;
import com.sina.weibo.sdk.net.AsyncWeiboRunner;
import com.sina.weibo.sdk.net.RequestListener;
import com.sina.weibo.sdk.net.WeiboParameters;
import com.umeng.socialize.PlatformConfig;
import com.umeng.socialize.PlatformConfig.SinaWeibo;
import com.umeng.socialize.UMAuthListener;
import com.umeng.socialize.bean.SHARE_MEDIA;
import java.util.HashMap;
import java.util.Map;

public class SinaExtra {

    final class SinaExtra_1 implements RequestListener {
        final /* synthetic */ UMAuthListener a;

        SinaExtra_1(UMAuthListener uMAuthListener) {
            this.a = uMAuthListener;
        }

        public void onComplete(String str) {
            Map hashMap = new HashMap();
            hashMap.put("result", str);
            this.a.onComplete(SHARE_MEDIA.SINA, 2, hashMap);
        }

        public void onWeiboException(WeiboException weiboException) {
            this.a.onError(SHARE_MEDIA.SINA, 2, new Throwable(weiboException));
        }
    }

    public static void JudgeAccessToken(Context context, String str, UMAuthListener uMAuthListener) {
        WeiboParameters weiboParameters = new WeiboParameters(((SinaWeibo) PlatformConfig.getPlatform(SHARE_MEDIA.SINA)).appKey);
        weiboParameters.put("access_token", str);
        new AsyncWeiboRunner(context).requestAsync("https://api.weibo.com/oauth2/get_token_info", weiboParameters, "POST", new SinaExtra_1(uMAuthListener));
    }
}