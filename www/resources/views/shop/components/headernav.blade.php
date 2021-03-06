<div class="admin-header clearfix">
    <div class="admin-logo fl">
        <a href="javascript:void(0);" data-param="home" target="workspace">
            <img src="{{asset('styles/images/admin_logo.png')}}"/>
        </a>
        <div class="foldsider"><i class="icon icon-indent-left"></i></div>
    </div>
    <div class="fl admin-menu">
        <ul>
            @foreach($navs['index'] as $k => $v)
                <li data-param="{{$k}}" class="fl text-center lh48 @if($loop->index == 0) active @endif"><a
                            href="{{url($navs[array_keys($navs[$k])[0]][array_keys($navs[array_keys($navs[$k])[0]])[0]]['url'])}}"
                            target="main">{{$v}}</a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="admin-fun-right fr">
        <div class="manager">
            <dl>
                <dt class="name">{{$user->user_name}}</dt>
                <dd class="group">@if(!empty($user->action_list['all'])) 超级管理员 @else 普通管理员 @endif</dd>
            </dl>
            <span class="avatar">
				<input name="img" type="file" class="admin-avatar-file" id="_pic" title="设置管理员头像">
				<img src="{{$user->admin_user_img}}">
			</span>
            <div id="admin-manager-btn" class="admin-manager-btn">
                <i class="arrow"></i>
                <div class="manager-menu" style="display: none;">
                    <div class="title">
                        <h4>最后登录</h4>
                        <a href="{{url('admin/privilege/'.$user->user_id.'/edit')}}" target="main"
                           class="edit_pwd">修改密码</a>
                    </div>
                    <div class="login-date">
                        <strong>{{date('Y-m-d H:i:s', $user->last_login)}}</strong>
                        <span>(IP:{{$user->last_ip}})</span>
                    </div>
                    <div class="title mt10">
                        <h4>常用操作</h4>
                        <a href="javascript:;" class="add_nav">添加菜单</a>
                    </div>
                    <div class="quick_link">
                        <ul>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="operate">
            <li><a href="javascript:;" id="notice" class="notice sc-icon" title="消息提示"></a>
                <s id="total">28</s>
                <div id="msg-container" style="display: none;">
                    <div class="item">
                        <h3 class="order_msg" ectype="msg_tit">自营订单提示<em class="iconfont icon-down"></em></h3><s
                                id="total">0</s>
                        <div class="msg_content" style="display: none;">
                            <p><a href="javascript:;" target="main" class="message">您有新订单</a>
                                <span class="tiptool">（<em id="new_orders">0</em>）</span></p>
                            <p><a href="javascript:;"
                                  target="main" class="message">待发货订单</a>
                                <span class="tiptool">（<em id="no_paid">0</em>）</span></p>
                            <p><a href="javascript:;"
                                  target="workspace"
                                  class="message">待处理退换货订单</a> <span class="tiptool">（<em id="no_change">0</em>）</span>
                            </p>
                            <p><a href="javascript:;" target="workspace" class="message">交易纠纷</a>
                                <span class="tiptool">（<em id="no_change">0</em>）</span></p>
                            <p><a href="javascript:;" target="workspace"
                                  class="message">缺货商品</a> <span class="tiptool">（<em id="no_change">0</em>）</span></p>
                        </div>
                    </div>

                    <div class="item">
                        <h3 class="goods_msg">商品提示<em class="iconfont icon-down"></em></h3><s
                                id="total">8</s>
                        <div class="msg_content"><p><a href="javascript:void(0);"
                                                       data-url="goods_report.php?act=list&amp;handle_type=6"
                                                       data-param="menushopping|goods_report"
                                                       target="workspace" class="message">商品举报</a>
                                <span class="tiptool">（<em id="goods_report">0</em>）</span></p>
                            <p><a href="javascript:void(0);" data-url="sale_notice.php?act=list"
                                  data-param="menushopping|sale_notice" target="workspace" class="message">商品降价通知</a>
                                <span class="tiptool">（<em id="goods_report">0</em>）</span></p>
                            <p><a href="javascript:void(0);" data-url="goods.php?act=review_status&amp;seller_list=1"
                                  data-param="menushopping|01_goods_list" target="workspace" class="message">未审核商家商品</a>
                                <span class="tiptool">（<em id="goods_report">6</em>）</span></p>
                            <p><a href="javascript:void(0);" data-url="merchants_brand.php?act=list&amp;audit_status=3"
                                  data-param="menushopping|06_goods_brand" target="workspace"
                                  class="message">未审核商家品牌</a> <span class="tiptool">（<em
                                            id="goods_report">2</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="goods.php?act=list&amp;warn_number=1&amp;seller_list=0"
                                  data-param="menushopping|01_goods_list" target="workspace"
                                  class="message">自营普通商品库存预警</a> <span class="tiptool">（<em
                                            id="goods_report">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="goods.php?act=list&amp;warn_number=1&amp;seller_list=1"
                                  data-param="menushopping|01_goods_list" target="workspace"
                                  class="message">商家普通商品库存预警</a> <span class="tiptool">（<em
                                            id="goods_report">0</em>）</span></p></div>
                    </div>

                    <div class="item">
                        <h3 class="shop_msg" ectype="msg_tit">商家审核提示<em class="iconfont icon-down"></em></h3><s
                                id="total">15</s>
                        <div class="msg_content" ectype="sellerMsg"><p><a href="javascript:void(0);"
                                                                          data-url="merchants_users_list.php?act=list&amp;check=1"
                                                                          data-param="menushopping|02_merchants_users_list"
                                                                          target="workspace" class="message">未审核商家</a>
                                <span class="tiptool">（<em id="shop_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="merchants_users_list.php?act=list&amp;shopinfo_check=1"
                                  data-param="menushopping|02_merchants_users_list" target="workspace" class="message">未审核店铺信息</a>
                                <span class="tiptool">（<em id="shopinfo_account">15</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="merchants_account.php?act=list&amp;act_type=account_log&amp;handler=2&amp;rawals=1"
                                  data-param="menushopping|12_seller_account" target="workspace"
                                  class="message">待审核商家提现</a> <span class="tiptool">（<em id="wait_cash">0</em>）</span>
                            </p>
                            <p><a href="javascript:void(0);"
                                  data-url="user_real.php?act=list&amp;review_status=0&amp;user_type=1"
                                  data-param="menushopping|16_users_real" target="workspace"
                                  class="message">待审核商家实名认证</a> <span class="tiptool">（<em
                                            id="seller_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="merchants_account.php?act=list&amp;act_type=detail&amp;log_type=2"
                                  data-param="menushopping|12_seller_account" target="workspace"
                                  class="message">待审核商家结算</a> <span class="tiptool">（<em
                                            id="seller_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="merchants_account.php?act=list&amp;act_type=detail&amp;log_type=3&amp;handler=2"
                                  data-param="menushopping|12_seller_account" target="workspace"
                                  class="message">待审核商家充值</a> <span class="tiptool">（<em
                                            id="seller_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);" data-url="seller_apply.php?act=list"
                                  data-param="menushopping|02_merchants_users_list" target="workspace" class="message">待审核店铺等级</a>
                                <span class="tiptool">（<em id="seller_account">0</em>）</span></p></div>
                    </div>

                    <div class="item">
                        <h3 class="ad_msg" ectype="msg_tit">广告位提示<em class="iconfont icon-down"></em></h3><s id="total">0</s>
                        <div class="msg_content" ectype="advMsg"><p><a href="javascript:void(0);"
                                                                       data-url="ads.php?act=list&amp;advance_date=1"
                                                                       data-param="menuplatform|ad_list"
                                                                       target="workspace" class="message">广告位即将到期</a>
                                <span class="tiptool">（<em id="advance_date">0</em>）</span></p></div>
                    </div>

                    <div class="item">
                        <h3 class="user_msg" ectype="msg_tit">会员提醒<em class="iconfont icon-down"></em></h3><s
                                id="total">0</s>
                        <div class="msg_content" ectype="userMsg"><p><a href="javascript:void(0);"
                                                                        data-url="user_real.php?act=list&amp;review_status=0&amp;user_type=0"
                                                                        data-param="menuplatform|03_users_list"
                                                                        target="workspace" class="message">会员实名认证</a>
                                <span class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="user_account.php?act=list&amp;process_type=0&amp;is_paid=0"
                                  data-param="menuplatform|09_user_account" target="workspace"
                                  class="message">会员充值申请</a> <span class="tiptool">（<em id="user_account">0</em>）</span>
                            </p>
                            <p><a href="javascript:void(0);"
                                  data-url="user_account.php?act=list&amp;process_type=1&amp;is_paid=0"
                                  data-param="menuplatform|09_user_account" target="workspace"
                                  class="message">会员提现申请</a> <span class="tiptool">（<em id="user_account">0</em>）</span>
                            </p>
                            <p><a href="javascript:void(0);" data-url="user_vat.php?act=list&amp;audit_status=0"
                                  data-param="menuplatform|15_user_vat_info" target="workspace"
                                  class="message">会员增票资质审核</a> <span class="tiptool">（<em
                                            id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);" data-url="discuss_circle.php?act=list&amp;review_status=1"
                                  target="workspace" class="message">网友讨论圈审核</a> <span class="tiptool">（<em
                                            id="user_discuss">0</em>）</span></p></div>
                    </div>

                    <div class="item">
                        <h3 class="campaign_msg" ectype="msg_tit">活动提醒<em class="iconfont icon-down"></em></h3><s
                                id="total">5</s>
                        <div class="msg_content" ectype="promotionMsg"><p><a href="javascript:void(0);"
                                                                             data-url="snatch.php?act=list&amp;seller_list=1&amp;review_status=1"
                                                                             data-param="menushopping|02_snatch_list"
                                                                             target="workspace" class="message">夺宝奇兵</a>
                                <span class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="bonus.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|04_bonustype_list" target="workspace"
                                  class="message">红包类型</a> <span class="tiptool">（<em id="user_account">0</em>）</span>
                            </p>
                            <p><a href="javascript:void(0);"
                                  data-url="group_buy.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|08_group_buy" target="workspace" class="message">团购活动</a>
                                <span class="tiptool">（<em id="user_account">3</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="topic.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|09_topic" target="workspace" class="message">专题</a> <span
                                        class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="auction.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|10_auction" target="workspace" class="message">拍卖活动</a> <span
                                        class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="favourable.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|12_favourable" target="workspace" class="message">优惠活动</a>
                                <span class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="presale.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|16_presale" target="workspace" class="message">预售活动</a> <span
                                        class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="package.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|14_package_list" target="workspace" class="message">超值礼包</a>
                                <span class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="exchange_goods.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|15_exchange_goods" target="workspace"
                                  class="message">积分商品</a> <span class="tiptool">（<em id="user_account">0</em>）</span>
                            </p>
                            <p><a href="javascript:void(0);"
                                  data-url="coupons.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|17_coupons" target="workspace" class="message">优惠券</a> <span
                                        class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="gift_gard.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|gift_gard_list" target="workspace" class="message">礼品卡</a>
                                <span class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="wholesale.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|13_wholesale" target="workspace" class="message">批发</a> <span
                                        class="tiptool">（<em id="user_account">0</em>）</span></p>
                            <p><a href="javascript:void(0);"
                                  data-url="seckill.php?act=list&amp;seller_list=1&amp;review_status=1"
                                  data-param="menushopping|03_seckill_list" target="workspace" class="message">秒杀</a>
                                <span class="tiptool">（<em id="user_account">2</em>）</span></p></div>
                    </div>
                </div>
            </li>
            <i class="sc-icon"></i>
            <li><a href="{{url('/')}}" target="_blank" class="home sc-icon" title="商城首页"></a></li>
            <i class="sc-icon"></i>
            <li><a href="{{url('admin/clearcache')}}" class="clear sc-icon" title="清除缓存"></a></li>
            <i class="sc-icon"></i>
            <li><a href="{{url('admin/logout')}}" class="prompt sc-icon" title="安全退出"></a></li>
        </div>
    </div>
</div>
<div class="top-border"></div>
<div class="nav-left">
    @foreach($navs['index'] as $k => $v)
        <div class="sub-nav sub_{{$k}}" style="display:@if($k == 'home')block @else none @endif">
            @foreach($navs[$k] as $key => $val)
                <div class="item fl @if($loop->index == 0)current @endif">
                    <div class="title fl">
                        <a href="{{url($navs[$key][array_keys($navs[$key])[0]]['url'])}}" target="main">
                            <i class="nav_icon icon_{{$key}}"></i>
                            <h6>{{$val}}</h6>
                        </a>
                    </div>
                    <div class="sub-item fl" style="display:@if($loop->index == 0)block @else none @endif">
                        <ul>
                            @foreach($navs[$key] as $n => $m)
                                <li class="@if($loop->index == 0) curr @endif">
                                    <s></s>
                                    <a href="{{url($m['url'])}}" target="main">{{$m['name']}}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
