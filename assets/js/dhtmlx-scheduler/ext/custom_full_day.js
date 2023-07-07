scheduler.form_blocks.time.set_value =	function(node,value,ev,config){
    var cfg = scheduler.config;
    var s=node.getElementsByTagName("select");
    var map = config._time_format_order;
    var start_date, end_date;

    if(cfg.full_day) {
        if (!node._full_day){
            var html = "<label class='dhx_fullday'><input type='checkbox' name='full_day' value='true'> "+scheduler.locale.labels.full_day+"&nbsp;</label></input>";
            if (!scheduler.config.wide_form)
                html = node.previousSibling.innerHTML+html;
            node.previousSibling.innerHTML=html;
            node._full_day=true;
        }
        var input=node.previousSibling.getElementsByTagName("input")[0];
        input.checked = (scheduler.date.time_part(ev.start_date)===0 && scheduler.date.time_part(ev.end_date)===0);

        s[map[0]].disabled=input.checked;
        s[ map[0] + s.length/2 ].disabled=input.checked;

        input.onclick = function(){
            if(input.checked) {
                var obj = {};
                scheduler.form_blocks.time.get_value(node,obj,config);

                start_date = scheduler.date.date_part(obj.start_date);
                end_date = scheduler.date.date_part(obj.end_date);

                if (+end_date == +start_date || (+end_date >= +start_date && (ev.end_date.getHours() !== 0 || ev.end_date.getMinutes() !== 0)))
                    end_date = scheduler.date.add(end_date, 1435, "minute");
            }else{
                start_date = null;
                end_date = null;
            }

            s[map[0]].disabled=input.checked;
            s[ map[0] + s.length/2 ].disabled=input.checked;

            _fill_lightbox_select(s,0,start_date||ev.start_date);
            _fill_lightbox_select(s,4,end_date||ev.end_date);
        };
    }

    if(cfg.auto_end_date && cfg.event_duration) {
        var _update_lightbox_select = function () {
            start_date = new Date(s[map[3]].value,s[map[2]].value,s[map[1]].value,0,s[map[0]].value);
            end_date = new Date(start_date.getTime() + (scheduler.config.event_duration * 60 * 1000));
            _fill_lightbox_select(s, 4, end_date);
        };
        for(var i=0; i<4; i++) {
            s[i].onchange = _update_lightbox_select;
        }
    }

    function _fill_lightbox_select(s,i,d) {
        var time_values = config._time_values;
        var direct_value = d.getHours()*60+d.getMinutes();
        var fixed_value = direct_value;
        var value_found = false;
        for (var k=0; k<time_values.length; k++) {
            var t_v = time_values[k];
            if (t_v === direct_value) {
                value_found = true;
                break;
            }
            if (t_v < direct_value)
                fixed_value = t_v;
        }

        s[i+map[0]].value=(value_found)?direct_value:fixed_value;
        if(!(value_found || fixed_value)){
            s[i+map[0]].selectedIndex = -1;//show empty select in FF
        }
        s[i+map[1]].value=d.getDate();
        s[i+map[2]].value=d.getMonth();
        s[i+map[3]].value=d.getFullYear();
    }

    _fill_lightbox_select(s,0,ev.start_date);
    _fill_lightbox_select(s,4,ev.end_date);
};