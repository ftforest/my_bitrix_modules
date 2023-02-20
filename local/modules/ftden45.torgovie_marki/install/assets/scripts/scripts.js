console.log('ftden45.torgovie_marki');
$(function() {

    /*var params = $('#ftden45.torgovie_marki-params').data().params;

    console.log(params.modul_id);
    console.log(params.key1);
    console.log(params.key2);*/

    function unique(array) {
        return $.grep(array, function(el, index) {
            return index === $.inArray(el, array);
        });
    }
    function removeItemAll(arr, value) {
        var i = 0;
        while (i < arr.length) {
            if (arr[i] === value) {
                arr.splice(i, 1);
            } else {
                ++i;
            }
        }
        return arr;
    }
    function add_sub_categories(array_in = [], array_add = [], map) {
        array_add.forEach((value, index, array)=>{
            let desc = map.get(value)[1]
            array_in.push(desc+"("+value+")")
        })
        return array_in;
    }
    function delete_sub_cats (array_in = [], array_delet = []) {
        //console.log('in: '+array_in);
        array_delet.forEach((value_del, index_del, array_del)=>{
            array_in.forEach((value, index, array)=>{
                let id_array_in = value.match(/\((.*?)\)/i)
                //console.log('id_array_in ' +id_array_in[1]);
                if (id_array_in[1] == value_del) {
                    array.splice(index, 1);
                }
            })
        })
        //console.log('out:'+array_in);
        return array_in;
    }
    function select_key_val (name) {
        let cats_map = new Map();
        let cats_map_parents = new Map();
        let cats_arr_parent = [];
        $("select[name = '" + name + "'] option").each(function()
        {
            // Add $(this).val() to your list
            // initialize array
            var cats_val = $(this).val().split('_')
            var cats_text = $(this).text().replace( /-{1,2}/g, "" )

            if (cats_val[1] == 1) {
                cats_map.set(Number(cats_val[0]),[0,cats_text])
                cats_arr_parent.push(cats_val[0])
            } else if (cats_val[1] == 2) {
                cats_map.set(Number(cats_val[0]),[Number(cats_val[2]),cats_text])
            } else {
                cats_map.set(Number(cats_val[0]),[0,cats_text])
            }
        });
        cats_arr_parent.forEach((value, index, array)=>{
            var arr_sub_cat = [];
            cats_map.forEach((value_map, key, map)=>{
                if (map.get(key)[0] == value) {
                    arr_sub_cat.push(key)
                }
            })
            cats_map_parents.set(value,arr_sub_cat)
        })
        return [cats_map_parents,cats_map];
    }
    /*
    Навешивание события При Изменении на SelectHTML
        jQuery
    взятие:
    id_categories,
    id_parents_gategory,
    name_categories
     */
    function changeSelect(name) {
        $("select[name = '"+name+"']").change(function(){
            var string_out_ids = $("textarea[name = '"+name+"_ids']").val();
            var category_name = $('select[name="'+name+'"]  option:selected').text();
            var category_id = $('select[name="'+name+'"]  option:selected').val().split('_')[0];
            var select_key_val_categories_product = select_key_val(name);
            var parents_cats_id = select_key_val_categories_product[0];
            var cats_map = select_key_val_categories_product[1];
            var category_parent = cats_map.get(Number(category_id))[0];

            category_name = category_name.replace( /-{1,2}/g, "" )
            if (string_out_ids === '') string_out_ids = category_name + "("+category_id+")" ;
            else string_out_ids = string_out_ids + ';' + category_name + "("+category_id+")";
            var result = string_out_ids.split(';');

            if (parents_cats_id.get(category_id)) {
                // если выбераешь Категорию то Подкатегории  удаляються
                result = delete_sub_cats(result, parents_cats_id.get(category_id))
                // если выбераешь Категорию то все Подкатегории добавляються
                //result = add_sub_categories(result, parents_cats_id.get(category_id), cats_map)
            }
            if (category_parent > 0) {
                // если выбераешь Подкатегорию то Категория удаляеться
                result = delete_sub_cats(result, [category_parent])
            }
            result = unique(result);

            //console.log(result);
            $("textarea[name = '"+name+"_ids']").val(result.join(';'));
        });
    }
    changeSelect('categories_product');
    changeSelect('torgovie_marks_product');
    changeSelect('not_categories_product');
    changeSelect('not_torgovie_marks_product');

    function select_textarea(name) {

    }

    /*if (params.switch_on == 'Y') {

        var style  = 'width: ' + params.width + 'px;';
        style += 'height: ' + params.height + 'px;';
        style += 'border-radius: ' + params.radius + 'px;';
        style += 'background-color: ' + params.color + ';';
        style += 'bottom: ' + params.indent_bottom + 'px;';
        style += params.side + ': ' + params.indent_side + 'px;';

        var speed = 600;
        if (params.speed == 'slow') {
            speed = 300;
        } else if (params.speed == 'fast'){
            speed = 1000;
        };

        $('body').append('<div id="scrollup-button" style="' + style +'"></div>');

        var button = $('#scrollup-button');
        $(window).on('load', function() {
            if ($(this).scrollTop() > 300) {
                button.fadeIn(600);
            }
        });

        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 300) {
                button.fadeIn(600);
            } else {
                button.fadeOut(600);
            };
        });

        button.on('click', function() {
            $('html, body').animate({
                scrollTop: 0
            }, speed);
        });

    };*/

});