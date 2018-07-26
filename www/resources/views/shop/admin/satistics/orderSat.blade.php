@extends('shop.layouts.index')
@section('content')
    <body style="overflow-y: scroll;background-color: #f7f7f7;">
    <div class="warpper clearfix">
        <div class="title">报表 - 订单统计</div>
        <div class="content">
            <div class="tip">
                <div class="tip_title">
                    <i class="tip_icon"></i>
                    <h5>操作提示</h5>
                </div>
                <ul>
                    <li>统计商城所有的订单，销售总额、有效金额、总点击数、每日点击数、每日点击购买额。</li>
                    <li>选择不同月份统计出现几个月的订单概括、配送方式、支付方式统计图。</li>
                </ul>
            </div>

            <div class="fromlist clearfix">
                <div class="clearfix">
                    <form class="form-horizontal">
                        <div class="form-group mar-top-20">
                            <label class="col-sm-2 control-label wd-120">总览：</label>
                            <div class="col-sm-10">
                                <div class="fl border-right pad-sat"><span class="fs-14 pad-all-5">总点击数</span><span
                                            class="red fs-14 pad-all-5">12312</span></div>
                                <div class="fl border-right pad-sat"><span class="fs-14 pad-all-5">总点击数</span><span
                                            class="red fs-14 pad-all-5">12312</span></div>
                                <div class="fl border-right pad-sat"><span class="fs-14 pad-all-5">总点击数</span><span
                                            class="red fs-14 pad-all-5">12312</span></div>
                                <div class="fl pad-sat"><span class="fs-14 pad-all-5">总点击数</span><span
                                            class="red fs-14 pad-all-5">12312</span></div>
                            </div>
                        </div>
                        <div class="form-group mar-top-20">
                            <label class="col-sm-2 control-label wd-120">起止日期：</label>
                            <div class="col-sm-4">
                                <input type="text" style="width: 350px" name="start_end_date"
                                       id="start_end_date" class="form-control input-sm"
                                       value="{{$now_date}}">
                            </div>
                            <div class="col-sm-1">
                                <a type="button" href="javascript:;"
                                   class="btn btn-info btn-edit btn-sm search1" style="padding: 5px 20px;">查询</a>
                            </div>
                        </div>
                        <div class="form-group mar-top-20">
                            <label class="col-sm-2 control-label wd-120">查询年月：</label>
                            <div class="col-sm-9">
                                @foreach($data as $k => $v)
                                    <input type="text" style="width: 145px" name="date"
                                           id="date_{{$k}}"
                                           class="form-control input-sm fl @if($k!=0) mar-left-15 @endif"
                                           value="{{$v}}">
                                @endforeach
                            </div>
                            <div class="col-sm-1">
                                <a type="button" href="javascript:;"
                                   class="btn btn-info btn-search2 btn-sm" style="padding: 5px 20px;">查询</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="main-info">


                </div>
            </div>
        </div>
    </div>
    @component('shop.components.copyright',['copyright'=>$copyright])@endcomponent
    <div style="height: 30px">　</div>
    </body>
@section('script')
    <script type="text/javascript" src="{{url('styles/plugin/charts/echarts.js')}}"></script>
    <script>
        $(function () {
            $('#start_end_date').daterangepicker(optionDateSet, function (start, end) {
                var s = start.format('YYYY-MM-DD HH:mm:ss');
                var e = end.format('YYYY-MM-DD HH:mm');
                var t = s + '～' + e + ':59';
                $('#start_end_date').val(t);
            });
            $('#date_0').daterangepicker(optionDateSingle, function (start, end) {
                var s = start.format('YYYY-MM');
                $('#date_0').val(s);
            });
            $('#date_1').daterangepicker(optionDateSingle, function (start, end) {
                var s = start.format('YYYY-MM');
                $('#date_1').val(s);
            });
            $('#date_2').daterangepicker(optionDateSingle, function (start, end) {
                var s = start.format('YYYY-MM');
                $('#date_2').val(s);
            });
            $('#date_3').daterangepicker(optionDateSingle, function (start, end) {
                var s = start.format('YYYY-MM');
                $('#date_3').val(s);
            });
            $('#date_4').daterangepicker(optionDateSingle, function (start, end) {
                var s = start.format('YYYY-MM');
                $('#date_4').val(s);
            });
        });
    </script>
@endsection
@endsection