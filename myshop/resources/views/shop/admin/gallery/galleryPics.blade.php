@extends('shop.layouts.index')
@section('content')
    <body style="overflow-y: scroll;background-color: #f7f7f7;">
    <div class="warpper clearfix">
        <div class="title">图片库 - 图片库管理</div>
        <div class="content">
            <div class="tip">
                <div class="tip_title">
                    <i class="tip_icon"></i>
                    <h5>操作提示</h5>
                </div>
                <ul>
                    <li>该页面展示图片列表。</li>
                </ul>
            </div>
            <div class="fromlist clearfix">
                <div class="clearfix">
                    <a href="JavaScript:;" class="btn btn-success btn-add btn-sm fl">添加图片</a>
                </div>
                <div class="main-info">
                    <ul class="image-item clearfix" data-album_id="{{$id}}">
                        @foreach($galleryPics as $galleryPic)
                            <li class="image-wrap fl clearfix pic-id-{{$galleryPic->pic_id}}">
                                <div class="img-container">
                                    <img src="{{url($galleryPic->pic_image)}}">
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="pic-id" value="{{$galleryPic->pic_id}}"
                                           class="ui-checkbox">
                                </div>
                                <div class="img-width" style="display: block;">{{$galleryPic->pic_spec}}
                                    ({{round($galleryPic->pic_size/1024,2)}}k)
                                </div>
                                <div class="img-handle" style="display: none;">
                                    <a href="javascript:;" class="t-img" data-pic_id="{{$galleryPic->pic_id}}"><i
                                                class="glyphicon glyphicon-transfer"></i>转移相册</a>
                                    <a href="javascript:;" class="del-img" data-pic_id="{{$galleryPic->pic_id}}"
                                       data-pic_image="{{$galleryPic->pic_image}}"
                                       data-pic_thumb="{{$galleryPic->pic_thumb}}"
                                       data-pic_file="{{$galleryPic->pic_file}}"><i
                                                class="glyphicon glyphicon-trash"></i>移除</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="clearfix bg-color-dray pad-top-4">
                    <div class="fl mar-all-5 checkwrap">
                        <label class="label-tip">
                            <input type="checkbox" name="all_list" value="" class="checkbox check-all fl ">全选</label>
                    </div>
                    <div class="fl mar-all-5">
                        <select name="select_type" class="form-control col-md-2 input-sm">
                            <option value="0">请选择</option>
                            <option value="is_del">删除</option>
                            <option value="is_transfer">转移相册</option>
                        </select>
                    </div>
                    <div class="fl">
                        <a type="button" class="btn btn-info btn-sure btn-sm mar-all-8">确定</a>
                    </div>
                </div>
                <div class="page_list">
                    {{$galleryPics->links()}}
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
            //上传图片窗口
            $('.btn-add').click(function () {
                layer.open({
                    type: 2,
                    area: ['700px', '250px'],
                    fixed: true, //不固定
                    maxmin: true,
                    title: '上传图片',
                    content: ["{{url('admin/gallery/uppicview/'.$id)}}", 'no'],
                    success: function (layero, index) {
                        layer.iframeAuto(index)
                    }
                });
            });

            //鼠标移入与移出图片
            $('.image-item li').on('mouseenter', function () {
                $(this).find('.img-width').hide();
                $(this).find('.img-handle').show();
            });
            $('.image-item li').on('mouseleave', function () {
                $(this).find('.img-width').show();
                $(this).find('.img-handle').hide();
            });

            //全选
            $('input[name=all_list]').on('click', function () {
                var flage = $(this).is(':checked')
                $("input[name=pic-id]").each(function () {
                    $(this).prop("checked", flage);
                });
            });

            //全选操作
            $('.btn-sure').on('click', function () {
                if ($('select[name=select_type]').val() != 0) {
                    var pic_ids = $("input[name=pic-id]");

                    var ids = [];
                    $.each(pic_ids, function (k, v) {
                        if ($(v).is(':checked')) {
                            ids.push($(v).val());
                        }
                    });

                    if ($('select[name=select_type]').val() == 'is_del') {

                    } else if ($('select[name=select_type]').val() == 'is_transfer') {

                    }
                }
            });

            //单选转移相册操作
            $('.image-item').on('click', '.t-img', function () {
                var pic_id = $(this).data('pic_id');
                layer.open({
                    type: 2,
                    area: ['700px', '250px'],
                    fixed: true, //不固定
                    maxmin: true,
                    title: '上传图片',
                    content: ["{{url('admin/gallery/transferpic')}}/" + pic_id, 'no'],
                    success: function (layero, index) {
                        layer.iframeAuto(index)
                    }
                });
            });

            //单选删除操作
            $('.image-item').on('click', '.del-img', function () {
                var that = this;
                var pic_id = $(this).data('pic_id');
                var pic_image = $(this).data('pic_image');
                var pic_thumb = $(this).data('pic_thumb');
                var pic_file = $(this).data('pic_file');
                layer.confirm(
                    '您确定要删除吗',
                    {
                        btn: ['确定', '取消'] //按钮
                    }, function () {
                        $.post(
                            "{{url('admin/gallery/delgallerypic')}}",
                            {
                                '_token': '{{csrf_token()}}',
                                pic_id: pic_id,
                                pic_image: pic_image,
                                pic_thumb: pic_thumb,
                                pic_file: pic_file
                            },
                            function (data) {
                                layer.msg(data.msg, {icon: data.code});
                                if (data.code == 1) {
                                    $(that).parent().parent().remove();
                                }
                            }
                        );
                    }, function () {
                    }
                );
            });
        });
    </script>
@endsection
@endsection