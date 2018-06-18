@extends('shop.layouts.index')
@section('content')
    <body style="overflow-y: scroll;background-color: #f7f7f7;">
    <div class="warpper clearfix">
        <div class="fromlist clearfix">
            <div class="main-info">
                <form action="{{url('admin/attribute')}}" method="post" class="form-horizontal"
                      enctype="multipart/form-data" onsubmit="return false;">
                    {{csrf_field()}}
                    <div class="form-group">
                        <label class="col-sm-4 control-label"><b>*</b>属性名称：</label>
                        <div class="col-sm-3">
                            <input type="text" name="attr_name" class="form-control" value=""
                                   placeholder="属性名称">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">所属商品类型：</label>
                        <div class="col-sm-8 pre-cate">
                            <div class="cate-option fl">
                                <select class="form-control select" name="cat_id" data-parent_id="0">
                                    <option value="0">顶级分类</option>
                                    @foreach($goodsTypes as $goodsType)
                                        <option value="{{$goodsType->cat_id}}">{{$goodsType->cat_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">分类筛选样式：</label>
                        <div class="col-sm-4 n-wd400">
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_cat_type" value="1" checked="true"> 普通
                            </label>
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_cat_type" value="0"> 颜色
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">能否进行检索：</label>
                        <div class="col-sm-4 n-wd400">
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_index" value="0" checked="true"> 不需要检索
                            </label>
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_index" value="1"> 关键字检索
                            </label>
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_index" value="2"> 范围检索
                            </label>
                            <div class="notic fl">
                                不需要该属性成为检索商品条件的情况请选择不需要检索，需要该属性进行关键字检索商品时选择关键字检索，如果该属性检索时希望是指定某个范围时，选择范围检索。
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">相同属性值的商品是否关联：</label>
                        <div class="col-sm-4 n-wd400">
                            <label class="radio-inline fl">
                                <input type="radio" name="is_linked" value="0" checked="true"> 否
                            </label>
                            <label class="radio-inline fl">
                                <input type="radio" name="is_linked" value="1"> 是
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">属性是否可选：</label>
                        <div class="col-sm-4 n-wd400">
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_type" value="0" checked="true"> 唯一属性
                            </label>
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_type" value="1"> 单选属性
                            </label>
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_type" value="2"> 复选属性
                            </label>
                            <div class="notic fl">
                                选择"单选/复选属性"时，可以对商品该属性设置多个值，同时还能对不同属性值指定不同的价格加价，用户购买商品时需要选定具体的属性值。选择"唯一属性"时，商品的该属性值只能设置一个值，用户只能查看该值。
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">该属性值的录入方式：</label>
                        <div class="col-sm-6 n-wd400">
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_input_type" value="0" checked="true"> 手工录入
                            </label>
                            <label class="radio-inline fl">
                                <input type="radio" name="attr_input_type" value="1"> 从下面的列表中选择（一行代表一个可选值）
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">可选值列表：</label>
                        <div class="col-sm-4 n-wd400">
                                <textarea name="attr_values" rows="5" class="form-control"
                                          disabled="disabled"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label"><b>*</b>排序：</label>
                        <div class="col-sm-3">
                            <input type="text" name="sort_order" class="form-control" value="100"
                                   placeholder="排序">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 control-label">&nbsp;</div>
                        <div class="">
                            <input type="submit" value="　确定　" class="btn btn-danger clearfix">
                            <a type="button" class="btn btn-default clearfix mar-left-20 close-win" href="javascript:;">取消</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </body>
@section('script')
    <script>
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.iframeAuto(index);
        $(function () {
            $('body').on('click', 'input[name=attr_input_type]', function () {
                if ($(this).val() == 0) {
                    $('textarea[name=attr_values]').attr("disabled",true);
                }else{
                    $('textarea[name=attr_values]').removeAttr("disabled");
                }
            });

            $(".form-horizontal").submit(function () {
                var ajax_data = $(this).serialize();
                $.post("{{url('admin/attribute/addattribute')}}", ajax_data, function (data) {
                    if (data) {
                        parent.layer.close(index);
                    }else {
                        layer.msg('添加失败');
                        return false;
                    }
                });
            });

            $('.close-win').click(function () {
                parent.layer.close(index);
            });
        });
    </script>
@endsection
@endsection