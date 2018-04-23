@extends('shop.layouts.index')
@section('content')
    <body style="overflow-y: scroll;background-color: #f7f7f7;">
    <div class="warpper clearfix">
        <div class="title">商品管理 - 商品列表</div>
        <div class="content">
            <div class="tip">
                <div class="tip_title">
                    <i class="tip_icon"></i>
                    <h5>操作提示</h5>
                </div>
                <ul>
                    <li>标识<em>"*"</em>的选项为必填项，其余为选填项。</li>
                    <li>商城相关信息设置，请谨慎填写信息。</li>
                </ul>
            </div>
            <div class="tabs mar-top-20">
                <ul class="fl">
                    @foreach($goodsNav as $nav)
                        <li class="@if($loop->index == 0) curr @endif fl">
                            <a href="javascript:void(0);">{{$nav['title']}}(<em>{{$nav['count']}}</em>)</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="fromlist clearfix">
                <div>
                    <a href="{{url('admin/goods/create')}}"
                       class="btn btn-success btn-add btn-sm">　添加商品　</a>
                </div>
                <div class="main-info">
                    <table class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th style="width: 40px">
                                <input type="checkbox" name="all_list" class="checkbox check-all">
                            </th>
                            <th class="col-md-1"><a>编号</a></th>
                            <th class="col-md-3"><a>商品名称</a></th>
                            <th class="col-md-1">商家名称</th>
                            <th class="col-md-2">价格/货号/运费</th>
                            <th class="col-md-1">标签</th>
                            <th class="col-md-1">排序</th>
                            <th class="col-md-1">SKU/库存</th>
                            <th class="col-md-1">审核状态</th>
                            <th class="col-md-1">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($goodsList as $goods)
                            <tr>
                                <td><input type="checkbox" name="checkboxes[]" value="{{$goods->goods_id}}"
                                           class="checkbox check-all"
                                           id="checkbox_{{$goods->goods_id}}"></td>
                                <td>{{$goods->goods_id}}</td>
                                <td>
                                    <div class="tDiv goods-info">
                                        <div class="img fl pad-all-5">
                                            <a href="" target="_blank" title="">
                                                <img src="{{$goods->goods_thumb}}" width="68" height="68">
                                            </a>
                                        </div>
                                        <div class="desc fl pad-all-5">
                                            <div class="name">
                                                <span class="max-wd-250 line-hg-20" title="{{$goods->goods_name}}"
                                                      data-toggle="tooltip"
                                                      data-placement="bottom">{{$goods->goods_name}}</span>
                                            </div>
                                            <p class="brand">品牌：
                                                <em class="em-blue">{{$goods->brand_name}}</em>　　
                                                @if($goods->is_shipping)
                                                    <em class="free"></em>
                                                @endif
                                                @if($goods->stages)
                                                    <em class="byStage"></em>
                                                @endif
                                                @if(!$goods->is_alone_sale)
                                                    <em class="parts"></em>
                                                @endif
                                                @if($goods->is_promote)
                                                    @if(time() > $goods->promote_end_date)
                                                        <em class="sale-end"></em>
                                                    @else
                                                        <em class="sale"></em>
                                                    @endif
                                                @endif
                                                @if($goods->is_limit_buy)
                                                    @if(time() > $goods->limit_buy_end_date)
                                                        <em class="purchaseEnd"></em>
                                                    @else
                                                        <em class="purchase"></em>
                                                    @endif
                                                @endif
                                                @if($goods->is_distribution)
                                                    <em class="distribution">{$lang.distribution}</em>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td><em>直营</em></td>
                                <td>
                                    <div class="tDiv">
                                        <div class="tDiv_item">
                                            <span class="fl">价格：</span>
                                            <div class="value">
                                                @if($goods->model_attr == 1)
                                                    <input name="goods_model_price" data-goodsid="{{$goods->goods_id}}"
                                                           class="btn btn-primary btn-sm" value="warehouse_price"
                                                           type="button">
                                                @elseif($goods->model_attr == 2)
                                                    <input name="goods_model_price" data-goodsid="{{$goods->goods_id}}"
                                                           class="btn btn-primary btn-sm" value="region_price"
                                                           type="button">
                                                @else
                                                    <span onclick="listTable.edit(this, 'edit_goods_price', {{$goods->goods_id}})">{{$goods->shop_price}}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="tDiv_item">
                                            <span class="fl">货号：</span>
                                            <div class="value">
                                                <span onclick="listTable.edit(this, 'edit_goods_sn', {{$goods->goods_id}})">{{$goods->goods_sn}}</span>
                                            </div>
                                        </div>

                                        <div class="tDiv_item">
                                            <span class="fl">运费：</span>
                                            <div class="value">
                                                <a href="" target="_blank">
                                                    按运费模板
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input type="hidden" value="{{$goods->goods_id}}" class="goode-id">
                                    <div class="switch-wrap clearfix"><span class="fl">精品：</span>
                                        <div class="switch @if($goods->is_best) active @endif" data-type="is_best"
                                             title="是">
                                            <div class="circle"></div>
                                            <input type="hidden" value="{{$goods->goods_id}}">
                                        </div>
                                    </div>
                                    <div class="switch-wrap clearfix"><span class="fl">新品：</span>
                                        <div class="switch @if($goods->is_new) active @endif" data-type="is_new"
                                             title="是">
                                            <div class="circle"></div>
                                            <input type="hidden" value="{{$goods->goods_id}}">
                                        </div>
                                    </div>
                                    <div class="switch-wrap clearfix"><span class="fl">热销：</span>
                                        <div class="switch @if($goods->is_hot) active @endif" data-type="is_hot"
                                             title="是">
                                            <div class="circle"></div>
                                            <input type="hidden" value="{{$goods->goods_id}}">
                                        </div>
                                    </div>
                                    <div class="switch-wrap clearfix"><span class="fl">上架：</span>
                                        <div class="switch @if($goods->is_on_sale) active @endif" data-type="is_on_sale"
                                             title="是">
                                            <div class="circle"></div>
                                            <input type="hidden" value="{{$goods->goods_id}}">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a type="button" href="javascript:;"
                                       class="btn btn-info btn-weight-order btn-sm" data-goodsid="{{$goods->goods_id}}">权重排序</a>
                                </td>
                                <td>@if($goods->is_attr)
                                        <a href="javascript:;" ectype="add_sku" data-goodsid="{{$goods->goods_id}}"
                                           data-userid="{{$goods->user_id}}">
                                            <i class="glyphicon glyphicon-edit"></i>
                                        </a>
                                    @else
                                        <span onclick="listTable.edit(this, 'edit_goods_number', {{$goods->goods_id}})">{{$goods->goods_number}}</span>
                                    @endif</td>
                                <td>
                                    @if($goods->review_status == 1)
                                        <font class="org2">未审核</font>
                                    @elseif($goods->review_status == 2)
                                        <font class="red">审核不通过</font><br/>
                                        <i class="tip yellow" title="{$goods.review_content}" data-toggle="tooltip">{$lang.prompt}</i>
                                    @elseif($goods->review_status == 3 || $goods->review_status == 4)
                                        <font class="blue">审核已通过</font>
                                    @elseif($goods->review_status == 5)
                                        <font class="navy2">无需审核</font>
                                    @endif
                                </td>
                                <td>
                                    <a type="button" href="{{url('admin/goods/'.$goods->goods_id.'/edit')}}"
                                       class="btn btn-primary btn-edit btn-sm fl mar-all-5">审核</a>
                                    <a type="button" href="{{url('admin/goods/'.$goods->goods_id.'/edit')}}"
                                       class="btn btn-info btn-edit btn-sm fl mar-all-5">查看</a>
                                    <a type="button" href="{{url('admin/goods/'.$goods->goods_id.'/edit')}}"
                                       class="btn btn-warning btn-edit btn-sm fl mar-all-5">编辑</a>
                                    <a type="button" class="btn btn-danger btn-del btn-sm fl mar-all-5"
                                       data-id="{{$goods->goods_id}}">删除</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="clearfix bg-color-dray pad-top-4">
                        <div class="fl mar-all-5 checkwrap">
                            <label class="label-tip">
                                <input type="checkbox" name="all_list" value=""
                                       class="checkbox check-all fl ">全选</label>
                        </div>
                        <div class="fl mar-all-5">
                            <select name="select_type" class="form-control col-md-2">
                                <option value="select">请选择</option>
                                <option value="is_best_on">精品</option>
                                <option value="is_best_off">取消精品</option>
                                <option value="is_new_on">新品</option>
                                <option value="is_new_off">取消新品</option>
                                <option value="is_hot_on">热销</option>
                                <option value="is_hot_off">取消热销</option>
                                <option value="is_sale_on">上架</option>
                                <option value="is_sale_off">下架</option>
                                <option value="is_delete">回收站</option>
                            </select>
                        </div>
                        <div class="fl">
                            <a type="button" class="btn btn-info btn-sure btn-sm mar-all-8">确定</a>
                        </div>
                    </div>
                    <div class="page_list">
                        {{$goodsList->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @component('shop.components.copyright',['copyright'=>''])@endcomponent
    <div style="height: 30px">　</div>
    </body>
@section('script')
    <script>
        $(function () {
            $('.nyroModal').nyroModal();

            $("[data-toggle='tooltip']").tooltip();

            $('.switch').click(function () {
                var val = 0;
                if ($(this).hasClass('active')) {
                    val = 0
                    $(this).removeClass('active');
                } else {
                    val = 1
                    $(this).addClass('active');
                }

                var tag = $(this).data('type');
                var id = $(this).children('input').val();
                $.post(
                    '{{url("admin/goods/change")}}',
                    {
                        id: id,
                        type: tag,
                        val: val,
                        _token: '{{csrf_token()}}'
                    },
                    function (data) {

                    }
                );
            });

            $('.btn-weight-order').click(function () {
                var goods_id = $(this).data('goodsid');

                layer.open({
                    type: 2,
                    area: ['1000px', '350px'],
                    fixed: true, //不固定
                    maxmin: true,
                    title: '权重排序',
                    content: "{{url('admin/goods/weight/order/')}}" + "/" + goods_id
                });
            });

            $('.chang-order').change(function () {
                $.post(
                    '{{url("admin/brand/change")}}',
                    {
                        id: $(this).data('id'),
                        order: $(this).val(),
                        _token: '{{csrf_token()}}'
                    },
                    function (data) {
                        layer.open({
                            title: '提示',
                            content: data.msg,
                            icon: data.code,
                            success: function (layero, index) {

                            }
                        });
                    }
                );
            });

            $('[name="all_list"]').click(function () {
                var flage = $(this).is(':checked')
                $(".check-all").each(function () {
                    $(this).prop("checked", flage);
                })
            })

            $('.btn-del').click(function () {
                var Id = $(this).data('id');
                layer.confirm('您确定要删除吗', {
                    btn: ['确定', '取消'] //按钮
                }, function () {
                    $.post(
                        "{{url('admin/goods/')}}/" + Id,
                        {'_method': 'delete', '_token': '{{csrf_token()}}'},
                        function (data) {
                            if (data.code == 1) {
                                layer.msg(data.msg, {icon: data.code});
                                setTimeout(function () {
                                    location.href = location.href;
                                }, 3000);
                            } else {
                                layer.msg(data.msg, {icon: data.code});
                            }

                        });
                    // layer.msg('的确很重要', {icon: 1});
                }, function () {
                });
            });
        });
    </script>
@endsection
@endsection