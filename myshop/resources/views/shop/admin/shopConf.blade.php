@extends('shop.layouts.index')
@section('content')
    <body class="iframe_body" style="overflow-y: scroll;">
    <div class="warpper shop_special">
        <div class="title">{{$lang['system_set']}} - {{$lang['shop_setup']}}</div>
        <div class="content">
            <div class="tabs_info">
                <ul>
                    @foreach($conf as $item)
                    <li class="@if($loop->index == 0) curr @endif"><a href="javascript:void(0);">{{$item->name}}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="explanation" id="explanation">
                <div class="ex_tit">
                    <i class="sc_icon"></i>
                    <h4>操作提示</h4>
                    <span id="explanationZoom" title="收起提示"></span>
                </div>
                <ul>
                    <li>标识“*”的选项为必填项，其余为选填项。</li>
                    <li>商店相关信息设置，请谨慎填写信息。</li>
                </ul>
            </div>
            <div class="flexilist">
                <div class="mian-info">
                    <form enctype="multipart/form-data" name="theForm" action="" method="post"
                          id="shopConfigForm">
                        @foreach($conf as $item)
                            <div class="switch_info shopConfig_switch"
                                 @if($loop->index != 0)style="display:none" @endif>
                                @foreach($item->vars as $var)
                                    @component('shop.components.shopconfform', ['var'=>$var,'lang'=>$lang])
                                    @endcomponent
                                @endforeach
                                <div class="item">
                                    <div class="label">&nbsp;</div>
                                    <div class="label_value info_btn">
                                        <input type="submit" value="{{$lang['sure']}}" ectype="btnSubmit"
                                               class="button">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{url('styles/admin/js/region.js')}}"></script>
    <script type="text/javascript">

        $(function () {
            //地区三级联动调用

            //图片点击放大
            $('.nyroModal').nyroModal();

            $('.type-file-box').mouseover(function () {
                $(this).addClass('hover');
            })
            $('.type-file-box').mouseout(function () {
                $(this).removeClass('hover');
            })

            $('.type-file-file').change(function () {
                $(this).parent().find('.type-file-text').val($(this).val())
            })

            //表单验证
            $("[ectype='btnSubmit']").click(function () {
                var invoice_type = $("input[name='invoice_type[]']");
                var invoice_type_val = "";
                var arr = [];

                invoice_type.each(function () {
                    invoice_type_val = $(this).val();
                    arr.push(invoice_type_val);
                });

                var nary = arr.sort();

                for (var i = 0; i < nary.length - 1; i++) {
                    if (nary[i] == nary[i + 1]) {
                        alert("商店设置 -> 购物流程 -> 发票税率重复：" + nary[i]);
                        return false;
                    }
                }

                if ($("#shopConfigForm").valid()) {
                    $("#shopConfigForm").submit();
                }
            });

        /*url重写验证*/
        var ReWriteSelected = null;
        var ReWriteRadiobox = document.getElementsByName("value[209]");

        for (var i = 0; i < ReWriteRadiobox.length; i++) {
            if (ReWriteRadiobox[i].checked) {
                ReWriteSelected = ReWriteRadiobox[i];
            }
        }

        function ReWriterConfirm(sender) {
            if (sender == ReWriteSelected) return true;
            var res = true;
            if (sender != ReWriteRadiobox[0]) {
                var res = confirm('{$rewrite_confirm}');
            }

            if (res == false) {
                ReWriteSelected.checked = true;
            }
            else {
                ReWriteSelected = sender;
            }
            return res;
        }

        function addCon_amount(obj) {
            var obj = $(obj);
            var tbl = obj.parents('#consumtable');
            var fald = true;
            var fald2 = true;
            var error = "";
            var volumeNum = obj.siblings("input");
            var it_val = "";

            var new_it_val = obj.siblings("input[name='invoice_type[]']").val();

            tbl.find(".mt10").each(function () {
                var it_input = $(this).find("input[name='invoice_type[]']");
                it_val = it_input.val();

                if (new_it_val == it_val) {
                    obj.siblings("input[name='invoice_type[]']").addClass("error");
                    fald2 = false;
                    error = "类型已存在";
                }
            });
            if (fald2 == true) {
                volumeNum.each(function (index, element) {
                    var val = $(this).val();
                    if (val == "") {
                        $(this).addClass("error");
                        fald = false;
                        error = "类型和税率不能为空";
                    } else if (!(/^[0-9]+.?[0-9]*$/.test(val)) && index == 1) {
                        $(this).addClass("error");
                        fald = false;
                        error = "税率必须为数字";
                    } else {
                        $(this).removeClass("error");
                        fald = true;
                    }
                });

                if (fald == true) {
                    var input = tbl.find('p:first').clone();
                    input.addClass("mt10");
                    input.find("input[type='button']").remove();
                    input.find(".form_prompt").remove();
                    input.append("<a href='javascript:;' class='removeV' onclick='removeCon_amount(this)'><img src='images/no.gif' title='删除'></a>")
                    tbl.append(input);
                    volumeNum.val("");
                } else {
                    obj.next(".form_prompt").find(".error").remove();
                    obj.next(".form_prompt").append("<label class='error'><i class='icon icon-exclamation-sign'></i>" + error + "</label>");
                }
            } else {
                obj.next(".form_prompt").find(".error").remove();
                obj.next(".form_prompt").append("<label class='error'><i class='icon icon-exclamation-sign'></i>" + error + "</label>");
            }
        }

        function removeCon_amount(obj) {
            var obj = $(obj);
            obj.parent('p').remove();
        }

    </script>
    </body>

@endsection