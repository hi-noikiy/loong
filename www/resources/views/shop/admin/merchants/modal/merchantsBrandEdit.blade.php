@extends('shop.layouts.index')
@section('content')
    <body style="overflow-y: scroll;background-color: #f7f7f7;">
    <div class="warpper clearfix">
        <div class="content">
            <div class="fromlist clearfix">
                <div class="main-info">
                    <form name="brand" action="{{url('admin/dialog/merchants/brand/modify')}}" method="post"
                          class="form-horizontal"
                          enctype="multipart/form-data">
                        {{csrf_field()}}

                        <input type="hidden" name="user_id" value="{{$mBrand->user_id}}">
                        <input type="hidden" name="bid" value="{{$bid}}">

                        <div class="form-group">
                            <label class="col-sm-2 control-label"><b>*</b>品牌中文名称：</label>
                            <div class="col-sm-3">
                                <input type="text" name="brand_name" class="form-control input-sm"
                                       value="{{$mBrand->brand_name}}"
                                       placeholder="品牌中文名称">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">品牌英文名称：</label>
                            <div class="col-sm-4 n-wd400">
                                <input type="text" name="brand_name_letter" class="form-control input-sm"
                                       value="{{$mBrand->brand_name_letter}}"
                                       placeholder="品牌英文名称">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><b>*</b>品牌首字母：</label>
                            <div class="col-sm-4 n-wd400">
                                <input type="text" name="brand_first_char" class="form-control input-sm"
                                       value="{{$mBrand->brand_first_char}}"
                                       placeholder="品牌首字母">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><b>*</b>品牌LOGO：</label>
                            <div class="col-sm-4 n-wd400">
                                <input type="file" name="brand_logo" class="fl">
                                <input type="hidden" name="brand_logo_bak" value="{{$mBrand->brand_logo}}">
                                <span>
                                    <a href="{{url($mBrand->brand_logo)}}"
                                       target="_blank" class="nyroModal">
                                        <i class="glyphicon glyphicon-picture top5"
                                           data-tooltipimg="" ectype="tooltip"
                                           data-toggle="tooltip"
                                           title="tooltip"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">品牌类型：</label>
                            <div class="col-sm-4 n-wd400">
                                <select name="brand_type" class="form-control input-sm input-sm">
                                    <option value="0">请选择...</option>
                                    <option value="1" @if($mBrand->brand_type == 1) selected @endif>国内品牌</option>
                                    <option value="2" @if($mBrand->brand_type == 2) selected @endif>国外品牌</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">经营类型：</label>
                            <div class="col-sm-4 n-wd400">
                                <select name="brand_operate_type" class="form-control input-sm input-sm">
                                    <option value="0">请选择...</option>
                                    <option value="1" @if($mBrand->brand_operate_type == 1) selected @endif>自有品牌
                                    </option>
                                    <option value="2" @if($mBrand->brand_operate_type == 2) selected @endif>代理品牌
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"><b>*</b>品牌使用期限：</label>
                            <div class="col-sm-6">
                                <input type="text" name="brand_end_time" class="form-control wd-250 fl input-sm"
                                       value="{{$mBrand->brand_end_time?$mBrand->brand_end_time:$now}}"
                                       placeholder="">
                                <div class="col-sm-3">
                                    <div class="checkbox">
                                        <label class="mar-right-10">
                                            <input type="checkbox" name="brand_end_time_permanent" value="1"
                                                   @if($mBrand->brand_end_time_permanent == 1) checked @endif>永久
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-12 text-center">请上传以下品牌资质扫描件电子版须加盖彩色企业公章 <font class="red">（即纸质版盖章，扫描或拍照上传），文字内容清晰可辨,支持jpg、gif和png图片，大小不超过4M。</font></label>
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="b_fid"
                                   value="{{!empty($mBrand->msbf->b_fid)?$mBrand->msbf->b_fid:0}}">
                            <label class="col-sm-2 control-label"><b>*</b>资质名称：</label>
                            <div class="col-sm-10">
                                <input type="text" name="qualification_name_input"
                                       class="form-control input-sm wd-180 fl"
                                       value="{{!empty($mBrand->msbf->qualification_name_input)?$mBrand->msbf->qualification_name_input:''}}"
                                       placeholder="">
                                <div class="col-sm-6">
                                    <label class="col-sm-4 control-label">资质电子版：</label>
                                    <input type="file" name="qualification_img" class="fl">
                                    <input type="hidden" name="qualification_img_bak"
                                           value="{{!empty($mBrand->msbf->qualification_img)?$mBrand->msbf->qualification_img:''}}">
                                    <span>
                                        <a href="{{url(!empty($mBrand->msbf->qualification_img)?$mBrand->msbf->qualification_img:'')}}"
                                           target="_blank" class="nyroModal">
                                            <i class="glyphicon glyphicon-picture top5"
                                               data-tooltipimg="" ectype="tooltip"
                                               data-toggle="tooltip"
                                               title="tooltip"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">到期日：</label>
                            <div class="col-sm-10">
                                <input type="text" name="expired_date" class="form-control input-sm wd-220 fl"
                                       value="{{!empty($mBrand->msbf->expired_date)?$mBrand->msbf->expired_date:$now}}"
                                       placeholder="">
                                <div class="col-sm-3">
                                    <div class="checkbox">
                                        <label class="mar-right-10">
                                            <input type="checkbox" name="expired_date_permanent" value="1"
                                                   @if(!empty($mBrand->msbf->expired_date_permanent)) checked @endif>永久
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4 control-label">&nbsp;</div>
                            <div class="">
                                <a type="button" class="btn btn-danger mar-left-20 btn-submit"
                                   href="javascript:;">　确定　</a>
                                <a type="button" class="btn btn-default clearfix mar-left-20 btn-close"
                                   href="javascript:;">取消</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </body>
@section('script')
    <script>
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.iframeAuto(index);

        $(function () {
            $('.nyroModal').nyroModal();

            $('.btn-submit').click(function () {
                layer.load();
                var domain = "{{url('/')}}/";
                var form = new FormData();
                form.append('brand_name', $('input[name=brand_name]').val());
                form.append('brand_name_letter', $('input[name=brand_name_letter]').val());
                form.append('brand_first_char', $('input[name=brand_first_char]').val());
                form.append('brand_type', $('select[name=brand_type]').val());
                form.append('brand_operate_type', $('select[name=brand_operate_type]').val());
                form.append('brand_end_time', $('input[name=brand_end_time]').val());
                form.append('qualification_name_input', $('input[name=qualification_name_input]').val());
                form.append('expired_date', $('input[name=expired_date]').val());
                form.append('expired_date_permanent', $('input[name=expired_date_permanent]').val());
                form.append('expired_date', $('input[name=expired_date]').val());
                if ($('input[name=brand_logo]')[0].files[0] != undefined) {
                    form.append('brand_logo', $('input[name=brand_logo]')[0].files[0]);
                }
                form.append('brand_logo_bak', $('input[name=brand_logo_bak]').val());
                if ($('input[name=qualification_img]')[0].files[0] != undefined) {
                    form.append('qualification_img', $('input[name=qualification_img]')[0].files[0]);
                }
                form.append('qualification_img_bak', $('input[name=qualification_img_bak]').val());
                form.append('user_id', $('input[name=user_id]').val());
                form.append('bid', $('input[name=bid]').val());
                form.append('b_fid', $('input[name=b_fid]').val());
                form.append('_token', '{{csrf_token()}}');
                $.ajax({
                    url: "{{url('admin/dialog/merchants/brand/modify')}}",
                    type: "POST",
                    data: form,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        layer.closeAll('loading');
                        layer.msg(data.msg, {icon: data.code});
                        var html = '';
                        $.each(data.data, function (k, v) {
                            html += '<tr class="text-center">' +
                                '<td>' + v.bid + '</td>' +
                                '<td>' + v.brand_name + '</td>' +
                                '<td>' + v.brand_name_letter + '</td>' +
                                '<td>' + v.brand_first_char + '</td>' +
                                '<td><span>' +
                                '<a href="' + domain + v.brand_logo + '" target="_blank" class="nyroModal">' +
                                '<i class="glyphicon glyphicon-picture top2 img_1" data-tooltipimg="" ectype="tooltip" data-toggle="tooltip" title="tooltip"></i>' +
                                '</a>' +
                                '</span></td>' +
                                '<td>' + v.brand_type + '</td>' +
                                '<td>' + v.brand_operate_type + '</td>' +
                                '<td class="text-center">' +
                                '<a type="button" href="javascript:;" class="btn btn-danger brand-edit btn-sm mar-right-10" data-id="' + v.bid + '">修改</a>' +
                                '<a type="button" href="javascript:;" class="btn btn-danger brand-del btn-sm" data-id="' + v.bid + '">删除</a>' +
                                '</td>' +
                                '</tr>';
                        });
                        parent.$('.shop-brand tbody').html(html);
                        setTimeout(function () {
                            parent.layer.close(index);
                        }, 1000);
                    }
                });
                setTimeout(function () {
                    layer.closeAll('loading');
                }, 5000)
            });

            //关闭iframe
            $('.btn-close').click(function () {
                parent.layer.close(index);
            });

            $('input[name=brand_end_time]').daterangepicker(optionDateDay, function (start, end) {
                var s = start.format('YYYY-MM-DD');
                $('input[name=establish_date]').val(s);
            });

            $('input[name=expired_date]').daterangepicker(optionDateDay, function (start, end) {
                var s = start.format('YYYY-MM-DD');
                $('input[name=establish_date]').val(s);
            });
        });
    </script>
@endsection
@endsection