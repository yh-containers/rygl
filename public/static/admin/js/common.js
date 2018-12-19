(function($){
    $.common ={
        layer:'',
        do_main:'',//域名
        sysnc_fnc:function(){},
        //共用表单提交
        submit: function(obj, open_redirect_mode) {
            $.common.sysnc_fnc()
            $.post(obj.attr('action'),obj.serialize(),function(result){
                $.common.layer.msg(result.msg)
                if(result.code==1 && open_redirect_mode) {
                    if(open_redirect_mode) {
                        if(open_redirect_mode === true){
                            //返回上级页面
                            setTimeout(function(){window.history.back()},1000)
                        }else if(typeof open_redirect_mode == 'function') {
                            open_redirect_mode();
                        }
                    }

                }
            })
        },
        del:function(obj,url,data,handle_fnc) {
            $.common.layer.confirm("是否删除该数据",function(){
                $.post(url,data,function(result){
                    $.common.layer.msg(result.msg)
                    if(result.code == 1) {
                        if(handle_fnc) {
                            handle_fnc()
                        }else {
                            setTimeout(function(){window.location.reload()},1000)
                        }
                    }
                })
            })
        },

        //富文本
        full_text:function(layedit,route,obj,height){
            var url = this.get_do_main()+'/upload/layuiUpload/route/'+route
            obj = obj?obj:'full_text';
            var index = layedit.build(obj,{
                uploadImage:{
                    'url':url
                }
            });

            height && layedit.set({'height':height+'px'});

            //同步富文本信息
            this.sysnc_fnc = function(){
                layedit.sync(index)
            }
        },
        //获取域名
        get_do_main:function(){
            if(!this.do_main){
                var href = window.location.href;
                var suffix = '.php';
                var url = '';
                var search_index = href.indexOf(suffix);
                if(search_index != -1) {
                    url = href.substr(0,search_index)+suffix
                }
                this.do_main = url;
            }
            return this.do_main;
        }
    }
})(jQuery)